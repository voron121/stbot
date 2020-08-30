<?php
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../controllers/schedulerController.php';
//-------------------------------------------------------//

$scheduler  = new SchedulerController();
$type       = (isset($_POST['type']))           ? $_POST['type']        : null;
$date       = (isset($_POST['date']))           ? $_POST['date']        : null;
$time       = (isset($_POST['time']))           ? $_POST['time']        : null;
$channel_id = (isset($_POST['channel_id']))     ? $_POST['channel_id']  : null;
$action     = (isset($_POST['action']))         ? $_POST['action']      : null;
$id         = (isset($_POST['id']))             ? $_POST['id']          : null; 
//-------------------------------------------------------//

if (in_array($_POST['action'], ['PUBLISH', 'UNPUBLISH', 'CLOSE'])) {
    echo $scheduler->addTaskAjax($id, $type, $date, $time, $action, $channel_id);
} else {
    die('Access denied!');
}
?>