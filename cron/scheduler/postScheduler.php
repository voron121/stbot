<?php
require_once __DIR__.'/tools.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once(__DIR__ . "/../../core/libs/telegram/telegramTools.php");
require_once __DIR__.'/../../core/coreTools.php';
//----------------------------------------------------------------------------//

$db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

// Разбор аргументов командной строки
$cmd_args = $tools->getCommandLineArgs($argv, $argc);
if (empty($cmd_args['login'])) {
    throw new Exception('"login" command line argument is required');
}
$login = $cmd_args['login'];
//----------------------------------------------------------------------------//

if (true == coreTools::checkRobotLock($_SERVER['PHP_SELF'], $login)) {
    echo "Blocked";
    Logger::getInstance()->robotActionWriteToLog('postScheduler', 'robotLock', 'Фаил заблокирован другой копией робота', $login);
    exit;
}
//----------------------------------------------------------------------------//

$scheduler          = new SchedulerTools();
$telegram           = new TelegramTools(TELEGRAM_BOT_TOKEN);
$post_to_processed  = $scheduler->getPostByLogin($login);
Logger::getInstance()->robotActionWriteToLog('postScheduler', 'postSchedulerStart', 'Робот авто публикаций приступил к работе', $login);
//----------------------------------------------------------------------------//

if (empty($post_to_processed)) {
    Logger::getInstance()->robotActionWriteToLog('postScheduler', 'postSchedulerSuccess', 'Нет активных задач. Робот авто публикаций завершил работу.');
    exit("Skip by time");
}
//----------------------------------------------------------------------------//

foreach ($post_to_processed as $task) {
    $tools              = new coreTools($task->user_id);
    $is_publish         = false;
    if ('PUBLISH' == $task->item_action) { // Публикация 
        if ('text' == $task->type) { // Просто текст
            $response   = $telegram->sendMessage(
                $task->text, 
                $task->telegram_chat_id, 
                ["disable_notification" => $task->notification, "reply_markup" => $task->buttons] 
            );
        } elseif ('album' == $task->type) { // Альбом или фото
            $images = $tools->getPostImages($task->user_id, $task->post_id);
            if (empty($images)) {
                Logger::getInstance()->robotActionWriteToLog('postScheduler', 'postSchedulerSkipp', 'Задача #'.$task->id.' пропущена: нет файлов для публикации', $login);
                continue;
            }
            if (count($images) == 1) {
                $options = [
                    "disable_notification"  => $task->notification, 
                    "caption"               => null, 
                    "reply_markup"          => $task->buttons
                ];
                if (isset($task->text)) {
                    $options['caption'] = $task->text;
                }
                $response   = $telegram->sendImage(
                    $images[0], 
                    $task->telegram_chat_id, 
                    $options 
                );
            } else  {
                $media      = $telegram->createMediaObj($images, 'photo', $task->text);
                $response   = $telegram->sendMediaGroup($media, $task->telegram_chat_id, $task->notification);
            }
        }
        //--------------------------------------------------------------------//

        $response   = json_decode($response, true);
        if (false == $response['ok']) {
            $scheduler->updateTaskStatus($task->id, 'FAIL');
            Logger::getInstance()->robotActionWriteToLog('postScheduler', 
                'postSchedulerError', 
                'Задача #'.$task->id.' не выполнена! ' .
                $response['error_code'].": ".$response['description'],
                $login
            );
            $scheduler->updateTaskStatus($task->id, 'FAIL');
        } else {
            $telegram_message_id    = (isset($response['result'][0]['message_id'])) ? $response['result'][0]['message_id'] : $response['result']['message_id'];
            $chat_id                = (isset($response['result'][0]['chat']['id'])) ? $response['result'][0]['chat']['id'] : $response['result']['chat']['id'];
            $is_publish             = true;
            $scheduler->updateTaskStatus($task->id, 'DONE');
            $tools->removePostImagesDir($task->user_id, $task->post_id);
            $scheduler->updatePostStatus($task->post_id, $telegram_message_id, $chat_id, 'PUBLISHED');
            Logger::getInstance()->robotActionWriteToLog('postScheduler', 'postSchedulerSuccess', 'Задача #'.$task->id.' успешно выполнена', $login);
        }
    } elseif ('UNPUBLISH' == $task->item_action) { // Удаление публикации
        $response   = $telegram->deleteMessage($task->telegram_chat_id, $task->message_id);
        $response   = json_decode($response, true);
        if (true == $response['ok']) {
            $scheduler->deletePost($task->post_id);
            $scheduler->updateTaskStatus($task->id, 'DONE');
            Logger::getInstance()->robotActionWriteToLog('postScheduler', 'postSchedulerSuccess', 'Задача #'.$task->id.' успешно выполнена', $login);
        } else {
            $scheduler->updateTaskStatus($task->id, 'FAIL');
            Logger::getInstance()->robotActionWriteToLog(
                'postScheduler', 
                'postSchedulerFail', 
                'Задача #'.$task->id.' не выполненна выполнена'.
                $response['error_code'].": ".$response['description'], 
                $login
            );
        }
        
    }
}
Logger::getInstance()->robotActionWriteToLog('postScheduler', 'postSchedulerSuccess', 'Робот авто публикаций завершил работу', $login);
?> 