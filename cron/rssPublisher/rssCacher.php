<?php

include(__DIR__."/../../core/libs/telegram/telegramTools.php");
include(__DIR__."/tools.php");
include(__DIR__."/../../core/libs/logger.php");
//--------------------------------------------------------//

$db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
//--------------------------------------------------------//

$tools 				= new coreTools();
$rss_parser_tools               = new RSSParserTools();
$telegram 			= new TelegramTools(TELEGRAM_BOT_TOKEN);

// Разбор аргументов командной строки
$cmd_args = $tools->getCommandLineArgs($argv, $argc);
if (empty($cmd_args['login'])) {
    throw new Exception('"login" command line argument is required');
}
$login = $cmd_args['login'];

//--------------------------------------------------------//

if (true == coreTools::checkRobotLock($_SERVER['PHP_SELF'], $login)) {
    echo "Blocked";
    Logger::getInstance()->robotActionWriteToLog('rssCacher', 'robotLock', 'Фаил заблокирован другой копией робота', $login);
    exit;
}
 
//----------------------------------------------------------------------------//

$rss_list 	= $rss_parser_tools->getUserRSSByLogin($login);
Logger::getInstance()->robotActionWriteToLog('rssCacher', 'cacherStart', 'Робот начал работу', $login);
//--------------------------------------------------------//

foreach ($rss_list as $rss) {
    $tools::printColorMessage("------------\r\nProcessed ".$rss->url, "success");
    if (false == $rss_parser_tools->checkUrlAvailable($rss->url)) {
        Logger::getInstance()->robotActionWriteToLog('rssCacher', 'cacherError', "RSS не доступна", $login);
        $tools::printColorMessage("ERROR: RSS не доступна", "error");
        $rss_parser_tools->updateRSSAvailableState($rss->id, "no");
    }
    if (null == $rss_parser_tools->downloadRSS($rss->url, $rss->user_id, $rss->id)) {
        Logger::getInstance()->robotActionWriteToLog('rssCacher', 'downloadRSSError', "Не удалось сохранить RSS", $login);
        $tools::printColorMessage("ERROR: Не удалось сохранить RSS", "error");
        $rss_parser_tools->updateRSSAvailableState($rss->id, "no");
    } else {
        Logger::getInstance()->robotActionWriteToLog('rssCacher', 'cacherSession', "RSS успешно обновлена!", $login);
        $tools::printColorMessage("RSS успешно обновлена!", "success");
        $rss_parser_tools->updateRSSAvailableState($rss->id, "yes");
    }
}
Logger::getInstance()->robotActionWriteToLog('rssCacher', 'cacherSuccess', 'Робот завершил работу', $login);
?>