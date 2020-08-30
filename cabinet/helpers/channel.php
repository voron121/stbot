<?php
	require_once __DIR__.'/../init.php';
	require_once __DIR__.'/../controllers/channelController.php';
	//-------------------------------------------------------//
	$channel  		= new ChannelController();
	$channel_id             = (isset($_GET['channel_id'])) ? (int)$_GET['channel_id'] : null;
	$url 			= (isset($_POST['url'])) ? strip_tags(htmlspecialchars($_POST['url'])) : null;
	$comment		= (isset($_POST['comment'])) ? strip_tags(htmlspecialchars($_POST['comment'])) : null;
	// TODO: подумать на счет организации дальнейших обращений внутри системмы через JSON и соответсвенно перейти на внутренний API 
	$data			= (isset($_POST['data'])) ?  $_POST['data'] : null;

	//-------------------------------------------------------//
	if ('addChannel' == $_GET['action']) {
		$channel->addChannel($url);
	} elseif ('synchChannel' == $_GET['action']) {
		$channel->synchChannel($channel_id);
	} elseif ('deleteChannel' == $_GET['action']) {
		$channel->deleteChannel($channel_id);
	} else {
		die('Access denied!');
	}
?>