<?php
require_once __DIR__.'/../controllers/registrationController.php';
//-------------------------------------------------------//

$registration = new registrationController();
//-------------------------------------------------------//

if ('registration' == $_GET['action']) {
    $registration->post($_POST);
} elseif ('activate_user' == $_GET['action']) {
    $registration->activateUserByHash((int)$_GET['uid'], strip_tags($_GET['hash']) );
} elseif ('aregistration' == $_GET['action']) {
    echo $registration->ajaxRegistration($_POST);
} else {
    die('Access denied!');
} 
?>