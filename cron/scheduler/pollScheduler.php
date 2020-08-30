<?php
require_once __DIR__.'/tools.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__ . "/../../core/coreTools.php";
require_once __DIR__ . "/../../core/libs/telegram/telegramTools.php";
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
    Logger::getInstance()->robotActionWriteToLog('pollScheduler', 'robotLock', 'Фаил заблокирован другой копией робота', $login);
    exit;
}
//----------------------------------------------------------------------------//

$scheduler          = new SchedulerTools();
$telegram           = new TelegramTools(TELEGRAM_BOT_TOKEN);
$poll_to_processed  = $scheduler->getPollByLogin($login);
Logger::getInstance()->robotActionWriteToLog('pollScheduler', 'pollSchedulerStart', 'Робот авто публикаций опросов приступил к работе', $login);
//----------------------------------------------------------------------------//

if (empty($poll_to_processed)) {
    Logger::getInstance()->robotActionWriteToLog('pollScheduler', 'pollSchedulerSuccess', 'Нет активных задач. Робот авто публикаций опросов завершил работу.');
    exit("Skip by time");
}
//----------------------------------------------------------------------------//

foreach ($poll_to_processed as $task) {
    if ('PUBLISH' == $task->item_action) { // Публикация 
        $response   = $telegram->sendPool($task->telegram_chat_id, $task->question, json_decode($task->answers), $task->notification, false, false);
        $response   = json_decode($response, true);
        if (true == $response["ok"]) {
            $scheduler->updatePolltAfterPublish((int)$task->poll_id, $response['result']['message_id'], $response['result']['chat']['id']);
        }
    } elseif ('CLOSE' == $task->item_action) {
        $response   = $telegram->stopPoll($task->telegram_chat_id, $task->message_id);
        $response   = json_decode($response, true);
        if (true == $response["ok"]) {
            $scheduler->updatePollStatus((int)$task->poll_id, 'CLOSE');
        }
    } elseif ('UNPUBLISH' == $task->item_action) {
        $response   = $telegram->deleteMessage($task->telegram_chat_id, $task->message_id);
        $response   = json_decode($response, true);
        if (true == $response["ok"]) {
            $scheduler->deletePoll((int)$task->poll_id);
        }
    }
    
    // Запишем логи
    if (true == $response["ok"]) {
        $scheduler->updateTaskStatus($task->id, 'DONE');
        Logger::getInstance()->robotActionWriteToLog('pollScheduler', 'pollSchedulerSuccess', 'Задача #'.$task->id.' успешно выполнена', $login);
    } else {
        $scheduler->updateTaskStatus($task->id, 'FAIL');
        Logger::getInstance()->robotActionWriteToLog(
            'pollScheduler', 
            'pollSchedulerFail', 
            'Задача #'.$task->id.' не выполненна выполнена'.
            $response['error_code'].": ".$response['description'], 
            $login
        );
    }
}
Logger::getInstance()->robotActionWriteToLog('pollScheduler', 'pollSchedulerSuccess', 'Робот авто публикаций опросов опросов завершил работу', $login);
?> 