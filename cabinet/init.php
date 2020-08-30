<?php
require_once __DIR__.'/../config.php';
//-----------------------------------------------//

session_start();
define('TEMPLATE_CHECK', 1);
if (!isset($_SESSION['uid'])) {
    header('location: ../cabinet/');
    die();
}
//-----------------------------------------------//

require_once __DIR__.'/controllers/userController.php';
$user               = new UserController();
$acive_menu_item    = (isset($_GET['template'])) ? $_GET['template'] : "";

?>