<?php
	require_once __DIR__.'/../init.php';
	require_once __DIR__.'/../controllers/rssController.php';
    //-------------------------------------------------------//

    $rsscontroller = new RSSController();
    //-------------------------------------------------------//

	$action 		= (isset($_GET['action'])) ? $_GET['action'] : null;
	$url 	        = (isset($_POST['url'])) ? $_POST['url'] : null;
    $comment 		= (isset($_POST['comment'])) ? $_POST['comment'] : null;
    $rss_id 		= (isset($_GET['rss_id'])) ? $_GET['rss_id'] : null;
    //-------------------------------------------------------//

    if (isset($action) && 'add' == $action) {
		$rsscontroller->addRSS($url, $comment);
 	} elseif (isset($action) && 'delete' == $action) {
        $rsscontroller->deleteRSS($rss_id);
 	} else {
 		die('some error');
 	}
?>