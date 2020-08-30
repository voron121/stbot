<?php
	require_once __DIR__.'/../init.php';
	require_once __DIR__.'/../controllers/vkgroupsController.php';
	//-------------------------------------------------------//
	
	$action 		= (isset($_GET['action'])) ? $_GET['action'] : null;
	$vk_user_id 	= (isset($_POST['vk_user_id'])) ? $_POST['vk_user_id'] : null;
	$url 			= (isset($_POST['vk_group_url'])) ? $_POST['vk_group_url'] : null;
	$group_id 		= (isset($_GET['group_id'])) ? $_GET['group_id'] : null;
	$vkcontroller  	= new VKGroupsController($vk_user_id);

 	if (isset($action) && 'add' == $action) {
		$vkcontroller->addVKGroup($vk_user_id, $url);
 	} elseif (isset($action) && 'delete' == $action) {
 		$vkcontroller->deleteVKGroup($group_id);
 	} else {
 		die('some error');
 	}
?>