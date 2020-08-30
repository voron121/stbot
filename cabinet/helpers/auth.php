<?php
require_once __DIR__.'/../controllers/authController.php';
//-------------------------------------------------------//
$auth = new authController();
//-------------------------------------------------------//
if ('login' == $_GET['action']) {
    $auth->post($_POST);
} elseif('logout' == $_GET['action']) {
    session_start();
    session_destroy();
    header('Location: /cabinet/index.php');
} else {
    die('Access denied!');
}
?>