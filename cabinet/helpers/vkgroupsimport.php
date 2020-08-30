<?php
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../controllers/vkgroupsImportController.php';
//-------------------------------------------------------//

$vk_import_controller  	= new VKGroupsImportController();
$rule_id                = null;
$action                 = (isset($_GET['action']))          ? $_GET['action']               : null;
$rule_name              = (isset($_POST['rule_name']))      ? $_POST['rule_name']           : null;
$vk_user_id             = (isset($_POST['vk_user_id']))     ? $_POST['vk_user_id']          : null;
$user_chanel_id         = (isset($_POST['user_chanel_id'])) ? $_POST['user_chanel_id']      : null;
$vk_group_id            = (isset($_POST['vk_group_id']))    ? $_POST['vk_group_id']         : null;
$mode                   = (isset($_POST['mode']))           ? $_POST['mode']                : null;
$text_mode              = (isset($_POST['text_mode']))      ? $_POST['text_mode']           : null;
$state                  = (isset($_POST['state']))          ? $_POST['state']               : 'on';
$order                  = (isset($_POST['order']))          ? $_POST['order']               : 'ASC';
$url_mode               = (isset($_POST['url_mode']))       ? $_POST['url_mode']            : null;
$stop_words             = (isset($_POST['stop_words']))     ? $_POST['stop_words']          : null;
$sheduler               = (isset($_POST['dt']))             ? json_encode($_POST['dt'])     : '';
$limit                  = (isset($_POST['limit']))          ? (int)$_POST['limit']          : 1;

if ('edit' == $action) {
    $rule_id = $_POST['rule_id'];
} elseif (in_array($action, ['edit', 'delete', 'off', 'on'])) {
    $rule_id = $_GET['rule_id'];
}
//-------------------------------------------------------//

if ('add' == $action || 'edit' == $action) {
    $rule_set = [
        'rule_name' 			=> $rule_name,
        'user_chanel_id' 		=> $user_chanel_id,
        'vk_user_id' 			=> $vk_user_id,
        'vk_group_id' 			=> $vk_group_id,
        'mode' 				=> $mode,
        'text_mode' 			=> $text_mode,
        'order'				=> $order,
        'state'				=> $state,
        'url_mode'			=> $url_mode,
        'stop_words'			=> $stop_words,
        'rule_id'			=> $rule_id,
        'sheduler'			=> $sheduler,
        'limit'                         => $limit
    ];
}
//-------------------------------------------------------//

if (isset($action) && 'add' == $action) {
    $vk_import_controller->addVKGroupImportRule($rule_set);
} elseif (isset($action) && 'edit' == $action) {
    $vk_import_controller->editVKGroupImportRule($rule_set);
} elseif (isset($action) && 'delete' == $action) {
    $vk_import_controller->deleteVKImportRule($rule_id);
} elseif (in_array($action,['off', 'on'])) {
    $vk_import_controller->changeStateVKImportRule($action, $rule_id);
} else {
    die('some error');
}
?>