<?php

include(__DIR__ . "/tools.php");
include(__DIR__ . "/../../core/libs/telegram/telegramTools.php");
include(__DIR__ . "/../../core/libs/VK/VK.php");
include(__DIR__ . "/../../core/libs/logger.php");
//--------------------------------------------------------//

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
//--------------------------------------------------------//

$tools              = new coreTools();
$vk_parser_tools    = new VKParserTools();
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
    Logger::getInstance()->robotActionWriteToLog('vkParser', 'robotLock', 'Фаил заблокирован другой копией робота', $login);
    exit;
}
//----------------------------------------------------------------------------//

$import_rules_info = $vk_parser_tools->getUserRulesInfoByLogin($login);
Logger::getInstance()->robotActionWriteToLog('vkParser', 'importStart', 'Робот начал импорт', $login);
//--------------------------------------------------------//

foreach ($import_rules_info as $rule) {
    try {
        $import_rule_message = "Rule name: {$rule->name} Id: {$rule->rule_id} Mode: {$rule->mode}";
        $tools::printColorMessage("------------\r\nProcessed rule " . $import_rule_message, "success");
        Logger::getInstance()->robotActionWriteToLog('vkParser', 'ImportSession', 'Импорт для правила ' . $import_rule_message, $login);
        //--------------------------------------------------------//
        // Проверим можем ли сейчас запустить робот по расписанию
        if (false == $vk_parser_tools->isMustRobotStart($rule->sheduler)) {
            $tools::printColorMessage("Rule skiped by sheduler", "warning");
            Logger::getInstance()->robotActionWriteToLog('vkParser', 'ImportSession', 'Правило пропущенно по расписанию', $login);
            continue;
        } 
        //--------------------------------------------------------//
        
        $imported_messages_counter = 0;
        $telegram_messages  = [];
        $channel_id         = $rule->telegram_chat_id;
        $vk                 = new VK($rule->vk_user_id, $rule->access_token);
        $wall_count         = $vk->getWall($rule->screen_name, 0, 1);
        $count              = $wall_count["response"]["count"];
        $upload_dir         = $vk_parser_tools->createUploadDir($rule->user_id, $rule->rule_id);

        //--------------------------------------------------------//
        // Посчитаем пагинацию
        if (0 == $rule->offset) { // Первый импорт
            $offset = $count - $rule->limit;
        } else { // Любой последующий импорт
            $offset = $count - ($rule->offset + $rule->limit);
            if ($offset < 0) { // Если дошли до конца
                $offset = 0;
                $importMessagesMeta = $vk_parser_tools->getImportedMessagesIds((int)$rule->rule_id, 1000);
            }
        }
        //-------------------------- Отладка ---------------------------------//
        // echo $offset." ".$rule->limit." --- \r\n"; // die("stop");
        // die();
        //--------------------------------------------------------------------//

        $wall = $vk->getWall($rule->screen_name, $offset, $rule->limit);
        $wall_records = array_reverse($wall["response"]["items"]);
        
        /*
         * Если завершили импорт исторических данных, отфильтруем из массива с сообщениями 
         * записи, которые уже были ранее импортированны. 
         */
        if (isset($importMessagesMeta)) {
            $wall_records = array_filter($wall_records, function($item) use ($importMessagesMeta) {
                return !isset($importMessagesMeta[$item['id']]);
            });
        }
        if (empty($wall_records)) {
            $tools::printColorMessage("Not isset new records for import", "success");
            Logger::getInstance()->robotActionWriteToLog('vkParser', 'ImportSession', 'Нет новых записей для импорта', $login);
            continue;
        }

        $tools::printColorMessage("Get " . count($wall_records) . " wall records", "success");
        Logger::getInstance()->robotActionWriteToLog('vkParser', 'ImportSession', 'Робот получил ' . count($wall_records) . ' записей', $login);
        //--------------------------------------------------------//

        foreach ($wall_records as $record) {
            $images_to_delete   = [];
            $images             = [];
            $animations         = [];
            $telegram_message   = [];
            //--------------------------------------------------------//
            // Обработаем ссылки в зависимости от режима
            if ('skipp' == $rule->url_mode) {
                if (true == $vk_parser_tools->checkUrlInText($record['text'])) {
                    $tools::printColorMessage('Record has hreff. Skip', 'warning');
                    continue;
                }
            } elseif ('cut' == $rule->url_mode) {
                $record['text'] = $vk_parser_tools->cutUrlInText($record['text']);
            }
            //--------------------------------------------------------//
            // Обрабатываем стоп-слова
            if (null != $rule->stop_words || '' != trim($rule->stop_words)) {
                if (true === $vk_parser_tools->checkStopWords($rule->stop_words, $record['text'])) {
                    Logger::getInstance()->robotActionWriteToLog('vkParser', 'ImportSession', 'Сообщение пропущенно. В нем присутсвует слово из списка стоп-слов. Сообщение: ' . $record["text"], $login);
                    $tools::printColorMessage('Record has stop word. Skip', 'warning');
                    continue;
                }
            }
            //--------------------------------------------------------//
            // Получим массив картинок для записи
            if (isset($record['attachments'])) {
                foreach ($record['attachments'] as $attachment) {
                    // Разбираем картинки в сообщении
                    if ('photo' == $attachment['type']) {
                        $images[] = $vk->getBigestImageByKey($attachment['photo']);
                    }
                    // Разбираем анимации
                    if ('doc' == $attachment['type'] && isset($attachment['doc']['preview']['video']['src'])) {
                        $animations[] = $attachment['doc']['preview']['video']['src'];
                    }
                }
            }
            //--------------------------------------------------------//

            if ('text_only' == $rule->mode && strlen($record['text']) > 0) {
                // Только текст
                if ('all' == $rule->text_mode) {
                    $telegram_message['message'] = $vk_parser_tools->splitMessage($record['text']);
                } else {
                    $telegram_message['message'][] = iconv_substr($record['text'], 0, 4096, "UTF-8");
                }
            } elseif ('image_only' == $rule->mode) {
                // Только изображение
                if (!empty($images)) {
                    foreach ($images as $image) {
                        $download_image = $vk_parser_tools->saveImage($image, $upload_dir);
                        if (null != $download_image) {
                            $telegram_message['alboms'][] = [
                                'image' => $download_image
                            ];
                        }
                    }
                }
            } elseif ('text_and_image' == $rule->mode) {
                // Изображение и текст
                if (!empty($images)) {
                    foreach ($images as $image) {
                        $download_image = $vk_parser_tools->saveImage($image, $upload_dir);
                        if (null != $download_image) {
                            $telegram_message['alboms'][] = [
                                'caption' => iconv_substr($record['text'], 0, 4096, "UTF-8"),
                                'image' => $download_image
                            ];
                        }
                    }
                }
            } elseif ('albom_with_caption' == $rule->mode) {
                // Альбом с текстом
                if (!empty($images)) {
                    $chunks = array_chunk($images, 10);
                    foreach ($chunks as $chunk_images) {
                        $telegram_message['alboms'][] = [
                            'caption' => iconv_substr($record['text'], 0, 4096, "UTF-8"),
                            'image' => $chunk_images
                                ];
                    }
                }
            } elseif ('albom' == $rule->mode) {
                // Альбом без текста
                if (!empty($images)) {
                    $chunks = array_chunk($images, 10);
                    foreach ($chunks as $chunk_images) {
                        $telegram_message['alboms'][] = [
                            'image' => $chunk_images
                        ];
                    }
                }
            } elseif ('animation_with_caption' == $rule->mode) {
                // Анимация с текстом
                if (!empty($animations)) {
                    foreach ($animations as $animation) {
                        $telegram_message['animations'][] = [
                            'animation' => $animation,
                            'caption' => iconv_substr($record['text'], 0, 4096, "UTF-8")
                        ];
                    }
                }
            } elseif ('animation' == $rule->mode) {
                // Анимация без текста
                if (!empty($animations)) {
                    foreach ($animations as $animation) {
                        $telegram_message['animations'][] = [
                            'animation' => $animation
                        ];
                    }
                }
            }

            // Если есть сформированное сообщение, добавим информацию об импорте и сообщение в общий массив
            if (isset($telegram_message['alboms']) || isset($telegram_message['message']) || isset($telegram_message['animations'])) {
                $telegram_message['channel_id']     = $channel_id;
                $telegram_message['rule_id']        = $rule->rule_id;
                $telegram_message['user_id']        = $rule->user_id;
                $telegram_message['vk_message_id']  = $record['id'];
                $telegram_message['mode']           = $rule->mode;
                $telegram_messages[]                = $telegram_message;
            }
        }
        //-----------------------------------------------------------//

        Logger::getInstance()->robotActionWriteToLog('vkParser', 'ImportSession', 'Сформировали ' . count($telegram_messages) . ' сообщений', $login);
        $tools::printColorMessage("Construct " . count($telegram_messages) . " messages. Start API request", "success");
        $import_meta = [];
        
        if (empty($telegram_messages)) {
            $tools::printColorMessage("No isset messages for import", "success");
            Logger::getInstance()->robotActionWriteToLog('vkParser', 'ImportSession', 'Не удалось сформировать сообщения по правилу', $login);
            $vk_parser_tools->updateGroupOffset($rule->limit, 0, $rule->rule_id); // Запишем offset
            continue;
        }
        
        print_r($telegram_messages); die();
        
        foreach ($telegram_messages as $telegram_message) {
            if ('text_only' == $telegram_message['mode']) {
                if (isset($telegram_message['message']) && count($telegram_message['message']) == 1) {
                    $response = $telegram->sendMessage(
                        $telegram_message['message'][0], 
                        $telegram_message['channel_id'], 
                        ["disable_notification" => true, "disable_web_page_preview" => true]
                    );
                } elseif (isset($telegram_message['message']) && count($telegram_message['message']) > 1) {
                    foreach ($telegram_message['message'] as $message) {
                        $response = $telegram->sendMessage(
                            $message, 
                            $telegram_message['channel_id'], 
                            ["disable_notification" => true, "disable_web_page_preview" => true]
                        );
                    }
                }
            } elseif ('image_only' == $telegram_message['mode'] || 'text_and_image' == $telegram_message['mode'] && isset($telegram_message['alboms'])) {
                foreach ($telegram_message['alboms'] as $albom) {
                    $options = ["disable_notification" => true, "caption" => null];
                    if (isset($albom['caption'])) {
                        $options['caption'] = $albom['caption'];
                    }
                    $response = $telegram->sendImage(
                        $albom['image'], 
                        $telegram_message['channel_id'], 
                        $options
                    );
                    $images_to_delete[] = $albom['image'];
                }
            } elseif (isset($telegram_message['alboms']) && 'albom_with_caption' == $telegram_message['mode'] || 'albom' == $telegram_message['mode'] && is_array($telegram_message['alboms'])) {
                foreach ($telegram_message['alboms'] as $albom) {
                    $media = $telegram->createMediaObj($albom['image'], false, isset($albom['caption']) ? $albom['caption'] : null);
                    $response = $telegram->sendMediaGroup($media, $telegram_message['channel_id'], false);
                }
            } elseif (isset($telegram_message['animations']) && 'animation' == $telegram_message['mode'] || 'animation_with_caption' == $telegram_message['mode']) {
                foreach ($telegram_message['animations'] as $animation) {
                    $response = $telegram->sendAnimation($animation['animation'], $telegram_message['channel_id'], false, isset($animation['caption']) ? $animation['caption'] : null);
                }
            }

            // Разберем ответ от API Telegram и сформируем данные об импорте
            $import_response = $vk_parser_tools->getResponse($response, $telegram_message);
            $imported_messages_counter += count(array_column($import_response, 'vk_message_id', 'vk_message_id'));
            $import_meta = array_merge($import_meta, $import_response);
        }
        //-----------------------------------------------------------//
        // Запишем мету
        $vk_parser_tools->updateGroupOffset($rule->limit, $imported_messages_counter, $rule->rule_id);
        $vk_parser_tools->saveMessagesImportMeta($import_meta);
        Logger::getInstance()->robotActionWriteToLog('vkParser', 'ImportSession', 'Импортировали ' . $imported_messages_counter . ' сообщений', $login);
        //-----------------------------------------------------------//

        $tools::printColorMessage("Finish API requests.\r\nImported " . $imported_messages_counter . " messages.\r\nStart record import info to DB", "success");
        $vk_parser_tools->clearUploadDir($images_to_delete);
        $tools::printColorMessage("Delete uploaded images " . count($images_to_delete), "success");
        //-----------------------------------------------------------//
    } catch (Exception $e) {
        Logger::getInstance()->robotActionWriteToLog('vkParser', 'ImportError', $e->getMessage(), $login);
        $tools::printColorMessage("ERROR: " . $e->getMessage(), "error");
        continue;
    }
    $tools::printColorMessage("Wait 1 second", "success");
    sleep(1);
}
Logger::getInstance()->robotActionWriteToLog('vkParser', 'importSuccess', 'Робот завершил импорт', $login);
?>