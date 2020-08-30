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

if (true == coreTools::checkRobotLock($_SERVER['PHP_SELF'], "")) {
    echo "Blocked";
    Logger::getInstance()->robotActionWriteToLog('sсhedulerWatchdog', 'robotLock', 'Фаил заблокирован другой копией робота', $login);
    exit;
}
//----------------------------------------------------------------------------//

Logger::getInstance()->robotActionWriteToLog('sсhedulerWatchdog', 'sсhedulerWatchdogStart', 'Робот надсмотрщик для шедулера запущен', $login);

$scheduler              = new SchedulerTools();
$updated_items          = $scheduler->setFailStatusSkippedItemsByLogin($login);
Logger::getInstance()->robotActionWriteToLog('sсhedulerWatchdog', 'sсhedulerWatchdogSession', 'Установили статус FAIL для '.$updated_items.' просроченных задач', $login);

Logger::getInstance()->robotActionWriteToLog('sсhedulerWatchdog', 'sсhedulerWatchdogSuccess', 'Робот надсмотрщик для шедулера завершил работу', $login);
?> 