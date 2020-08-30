<?php
include(__DIR__."/tools.php");
include(__DIR__."/../../core/libs/telegram/telegramTools.php");
include(__DIR__."/../../core/libs/logger.php");
//--------------------------------------------------------//

$db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
//--------------------------------------------------------//

$tools              = new coreTools();
$rss_parser_tools   = new RSSParserTools();
$telegram           = new TelegramTools(TELEGRAM_BOT_TOKEN);

// Разбор аргументов командной строки
$cmd_args = $tools->getCommandLineArgs($argv, $argc);
if (empty($cmd_args['login'])) {
    throw new Exception('"login" command line argument is required');
}
$login = $cmd_args['login'];

//--------------------------------------------------------//

if (true == coreTools::checkRobotLock($_SERVER['PHP_SELF'], $login)) {
    echo "Blocked";
    Logger::getInstance()->robotActionWriteToLog('rssParser', 'robotLock', 'Фаил заблокирован другой копией робота', $login);
    exit;
}
//----------------------------------------------------------------------------//

$rss_rules 	= $rss_parser_tools->getUserRulesInfoByLogin($login);
Logger::getInstance()->robotActionWriteToLog('rssParser', 'importStart', 'Робот начал импорт', $login);
//--------------------------------------------------------//

foreach ($rss_rules as $rule) {
    try {
        $import_rule_message = "Rule name: {$rule->name} Id: {$rule->rule_id}";
        $tools::printColorMessage("------------\r\nProcessed rule ".$import_rule_message, "success");
        Logger::getInstance()->robotActionWriteToLog('rssParser', 'ImportSession', 'Импорт для правила '.$import_rule_message, $login);
        //--------------------------------------------------------//
        // Проверим можем ли сейчас запустить робот по расписанию
        if (false == $rss_parser_tools->isMustRobotStart($rule->sheduler)) {
            $tools::printColorMessage("Rule skiped by sheduler", "warning");
            Logger::getInstance()->robotActionWriteToLog('vkParser', 'ImportSession', 'Правило пропущенно по расписанию', $login);
            continue;
        } 
        //--------------------------------------------------------//
        
        $user_dirs          = coreTools::getUserDirs($rule->user_id);
        $rss_file           = __DIR__."/../..".$user_dirs["xml_path"].$rule->download_file;
        $upload_dir         = $rss_parser_tools->createUploadDir($rule->user_id, $rule->rule_id);
        $items              = $rss_parser_tools->getRSSItems($rss_file);
        $cachedItems        = $rss_parser_tools->getCachedRSSItems($rule->rule_id);
        $messages           = [];
        $images_to_delete   = [];
        $rss_parser_tools->setChanelId($telegram->getTelegramChannelIdByUrl($rule->url));
        //--------------------------------------------------------//
        // Сформируем массив с сообщениями для постинга на канал согластно правилам
        $message_count = 1;
        foreach ($items as $item) {
            $message        = [];
            $message_hash   = md5($rss_parser_tools->getNodeValueByTagName($item, 'title').$rss_parser_tools->getNodeValueByTagName($item, 'description'));
            // Если сообщение уже было импортированно ранее
            if (isset($cachedItems[$message_hash])) {
                $tools::printColorMessage("Message with hash ".$message_hash." isset in cache. ", "warning");
                Logger::getInstance()->robotActionWriteToLog('rssParser', 'ImportSession', "Запись с хешом ".$message_hash." есть в кеше. ", $login);
                continue;
            }
            // Если достигли лимита сообщений за сессию импорта
            if ($message_count > $rule->limit) {
                $tools::printColorMessage("Message with hash ".$message_hash." skipped by limit. ", "warning");
                Logger::getInstance()->robotActionWriteToLog('rssParser', 'ImportSession', 
                        "Запись с хешом ".$message_hash." пропущена. Достигнут лимит сообщений за один импорта, указанный в правиле. Лимит = ".$rule->limit, 
                        $login);
                continue;
            }
            //--------------------------------------------------------//
            // Массив с сообщениями будет индексирован по хеш сумме для сообщения на основе RSS фида
            $message[$message_hash]['image']            = null;
            $message[$message_hash]['user_id']          = $rule->user_id;
            $message[$message_hash]['rss_id']           = $rule->rss_id;
            $message[$message_hash]['rule_id']          = $rule->rule_id;
            $message[$message_hash]['rss_item_hash']    = $message_hash;
            $message[$message_hash]['title']            = $rss_parser_tools->getNodeValueByTagName($item, 'title');
            $message[$message_hash]['channel_id']       = $rule->telegram_chat_id;
            // Сформируем текст сообщения с учетом обрезки текста согласно лимитов Телеграм и наличия или отсутсвия ссылки
            if ('yes' == $rule->publish_url) {
                $message[$message_hash]['message'] = $rss_parser_tools->cutText(
                    $rule,
                    $rss_parser_tools->getNodeValueByTagName($item, 'description'),
                    $rss_parser_tools->getReadMoreUrl($rss_parser_tools->getNodeValueByTagName($item, 'link'), $rule->read_more_text, 'HTML')
                );
            } else {
                $message[$message_hash]['message'] = $rss_parser_tools->cutText(
                    $rule,
                    $rss_parser_tools->getNodeValueByTagName($item, 'description'),
                    ''
                );
            }
            //--------------------------------------------------------//
            // Обработаем картинку
            if ('yes' == $rule->publish_image) {
                $image_url      = $rss_parser_tools->getItemImageFromRSS($item, $rule->image_tag, $rule->image_tag_mode);
                $saved_image    = $rss_parser_tools->saveImage($image_url, $upload_dir);
                if (null == $saved_image) {
                    $tools::printColorMessage("Fail saved image!", "warning");
                    Logger::getInstance()->robotActionWriteToLog('rssParser', 'ImportSession',
                        'Не удалось сохранить изображение для правила '.$rule->rule_id. ' Хеш сообщения '.$message_hash,
                        $login);
                } else {
                    // Если удалось сохранить картинку - добавим ее в сообщения к отправке
                    $images_to_delete[]                 = $saved_image;
                    $message[$message_hash]['image']    = $saved_image;
                }
            }

            // Сформируем сообщение в зависмости от наличия в ней ссылки
            if ('yes' == $rule->publish_url) {
                $message[$message_hash]['message'] .= $rss_parser_tools->getReadMoreUrl(
                    $rss_parser_tools->getNodeValueByTagName($item, 'link'), $rule->read_more_text, 'HTML'
                );
            }
            $messages = $messages + $message;
            $message_count++;
        }
        //--------------------------------------------------------//
        // Опубликуем сообщения
        $import_meta                = [];
        $imported_messages_counter  = 0;
        
        if (!empty($messages)) {
            $tools::printColorMessage("Get ".count($messages) . " messages. \r\nStart  requests", "success");
            foreach ($messages as $message_hash => $message) {
                if (isset($cachedItems[$message_hash])) {
                    $tools::printColorMessage("Item is isset in cache!", "warning");
                    continue;
                }
                //-------------------------------------------------//
                if (isset($message['image']) && null != $message['image']) {
                    $options = ["disable_notification" => true, "caption" => null];
                    if (isset($message['message'])) {
                        $options['caption'] = $message['message'];
                    }
                    $response 	= $telegram->sendImage(
                        $message['image'], 
                        $message['channel_id'], 
                        $options
                    );
                } elseif(!isset($message['image']) && null != $message['message']) {
                    $response = $telegram->sendMessage(
                        $message['message'], 
                        $message['channel_id'], 
                        ["disable_notification" => true]
                    );
                }
                // Соберем мету и статистику
                $import_response            = $rss_parser_tools->getResponse($response, $message);
                $imported_messages_counter += count(array_column($import_response, 'rss_item_hash', 'rss_item_hash'));
                $import_meta                = array_merge($import_meta, $import_response);
            }
        }
        //-----------------------------------------------------------//

        $rss_parser_tools->saveImportMeta($import_meta);
        Logger::getInstance()->robotActionWriteToLog('rssParser', 'ImportSession', 'Импортировали '.$imported_messages_counter.' сообщений', $login);
        //-----------------------------------------------------------//

        $tools::printColorMessage("Finish API requests.\r\nImported ".$imported_messages_counter." messages.\r\nStart record import info to DB", "success");
        $rss_parser_tools->clearUploadDir($images_to_delete);
        $tools::printColorMessage("Delete uploaded images ".count($images_to_delete), "success");
        //-----------------------------------------------------------//
	} catch(Exception $e) {
            Logger::getInstance()->robotActionWriteToLog('rssParser', 'ImportError', $e->getMessage(), $login);
            $tools::printColorMessage("ERROR: ".$e->getMessage(), "error");
            continue;
	}
}
Logger::getInstance()->robotActionWriteToLog('rssParser', 'importSuccess', 'Робот завершил импорт', $login);
?>