<?php
/*
 * Робот обновляет мета информацию каналов пользователя
 */
//--------------------------------------------------------//

include(__DIR__."/tools.php");
include(__DIR__."/../../core/coreTools.php");
include(__DIR__."/../../core/libs/logger.php");
//--------------------------------------------------------//

$core_tools 	        = new coreTools();
$telegram_parser_tools 	= new TelegramParserTools(); 
//----------------------------------------------------------------------------//

if (true == coreTools::checkRobotLock($_SERVER['PHP_SELF'], "")) {
    echo "Blocked";
    Logger::getInstance()->robotActionWriteToLog('telegramGroupMetaCacher', 'robotLock', 'Фаил заблокирован другой копией робота');
    exit;
}
//----------------------------------------------------------------------------//

$core_tools::printColorMessage('Start vk group cacher');
Logger::getInstance()->robotActionWriteToLog('telegramGroupMetaCacher', 'cacheStart', 'Робот сбора мета информации о каналах телеграмм приступил к работе');

$users      = $telegram_parser_tools->getUsers();
$core_tools::printColorMessage('Get '.count($users), ' users', 'success');
foreach ($users as $user) {
    try {
        $core_tools::printColorMessage('Processed user '.$user->login, 'success');
        $channels    = $telegram_parser_tools->getUserChannels($user->id);
        $core_tools::printColorMessage('Get '.count($channels), ' users channels', 'success');
        //--------------------------------------------------------//
        foreach ($channels as $channel) {
            try {
                $core_tools::printColorMessage('Processed channel '.$channel->telegram_channel_id, 'success');
                if (true == $telegram_parser_tools->updateChannelMeta($user->id, $channel->telegram_chat_id, $channel->id)) {
                    $core_tools::printColorMessage('Updated meta success!', 'success');
                } else {
                    $core_tools::printColorMessage("Error", 'warning');
                }   
            } catch (Exception $ex) {
                Logger::getInstance()->robotActionWriteToLog('telegramGroupMetaCacher', 'cacheSession', $ex->getMessage());
                $core_tools::printColorMessage($ex->getMessage(), 'warning');
                continue;
            }
        }
    } catch (Exception $ex) {
        Logger::getInstance()->robotActionWriteToLog('telegramGroupMetaCacher', 'cacheSession', $ex->getMessage());
        $core_tools::printColorMessage($ex->getMessage(), 'warning');
        continue;
    }
}
//--------------------------------------------------------//

Logger::getInstance()->robotActionWriteToLog('telegramGroupMetaCacher', 'cacheSuccess', 'Робот сбора мета информации о каналах телеграмм завершил работу');
$core_tools::printColorMessage('Finish vk group cacher');
?>