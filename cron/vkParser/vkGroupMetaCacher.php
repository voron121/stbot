<?php
/*
 * Робот обновляет мета информацию для групп ВК всех пользователей
 */
//--------------------------------------------------------//

include(__DIR__."/tools.php");
include(__DIR__."/../../core/libs/VK/VK.php");
include(__DIR__."/../../core/libs/logger.php");
//--------------------------------------------------------//

$core_tools 	    = new coreTools();
$vk_parser_tools    = new VKParserTools();
$groups             = $vk_parser_tools->getGroupsToUpdateMeta();
//--------------------------------------------------------//

if (true == coreTools::checkRobotLock($_SERVER['PHP_SELF'], "")) {
    echo "Blocked";
    Logger::getInstance()->robotActionWriteToLog('vkGroupMetaCacher', 'robotLock', 'Фаил заблокирован другой копией робота');
    exit;
}
//----------------------------------------------------------------------------//

$core_tools::printColorMessage('Start vk group cacher');
Logger::getInstance()->robotActionWriteToLog('vkGroupMetaCacher', 'cacheStart', 'Робот сбора мета информации о группах ВК приступил к работе');
foreach ($groups as $group) {
    try {
        $core_tools::printColorMessage('Getting info for  '.$group->screen_name);
        $vk         = new VK($group->vk_user_id, $group->access_token);
        $vk_group   = $vk->getGroupInfo($group->screen_name);
        $wall_meta  = $vk->getWall($group->screen_name, 0, 1);
        $wall_count = $wall_meta["response"]["count"];
        
        $vk_parser_tools->saveGroupsMeta($group->group_id, $wall_count);
        $vk_parser_tools->saveVKAGroupPhoto($vk_group["photo_100"], $vk_group["id"], $group->user_id);
        $core_tools::printColorMessage('Update group count set '.$wall_count.' count', 'success');
        echo "---------------- \r\n";
    } catch(Exception $e) {
        Logger::getInstance()->robotActionWriteToLog('vkGroupMetaCacher', 'cacheError', $e->getMessage());
        $core_tools::printColorMessage($e->getMessage(), 'error');
        continue;
    }
    sleep(1);
}
Logger::getInstance()->robotActionWriteToLog('vkGroupMetaCacher', 'cacheSuccess', 'Робот сбора мета информации о группах ВК завершил работу');
$core_tools::printColorMessage('Finish vk group cacher');
?>