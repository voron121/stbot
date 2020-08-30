<?php

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../controllers/vkOauthController.php';
//-------------------------------------------------------//
$vkcontroller = new VKOauthController();
$action = (isset($_GET['action'])) ? $_GET['action'] : null;

if (isset($action) && 'add' == $action) {
    $vkcontroller->setRequestToGetCode();
} else {
    $code = $_GET['code'];
    if (null != $code && '' != $code) {
        $access_token = $vkcontroller->getAccessToken($code);
        if (!empty($access_token)) {
            $account_info = $vkcontroller->getVKUserInfo($access_token);
            if (null != $account_info) {
                $account_info = $account_info + $access_token;
                $vkcontroller->saveVKAccount($account_info);
            }
        } else {
            die('TOKEN IS NOT EXIST');
        }
    } else {
        die('CODE IS NOT EXIST');
    }
}
?>