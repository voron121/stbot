<?php
	require_once __DIR__.'/../init.php';
	require_once __DIR__.'/../controllers/pollController.php';
	//-------------------------------------------------------//
	$poll  			= new PollController();
	$user_chanel 	= (isset($_POST['user_chanel'])) ? (int)$_POST['user_chanel'] : null;
	$poll_id 		= (isset($_GET['poll_id'])) ?  (int)($_GET['poll_id']) : null;
	$question 		= (isset($_POST['question'])) ? $_POST['question'] : null;
	$answers 		= (isset($_POST['answer'])) ? $_POST['answer'] : null;
	$notification 	= (isset($_POST['notification'])) ? $_POST['notification'] : "No";

	//-------------------------------------------------------//
	if ('addPoll' == $_GET['action']) {
		$poll->addPoll($user_chanel, $question, $answers, $notification);
	} elseif ('deletePoll' == $_GET['action']) {
		$poll->deletePoll($poll_id);
	} elseif ('sendPoll' == $_GET['action']) {
		$poll->publishPoll($poll_id);
	} elseif ('closePoll' == $_GET['action']) {
		$poll->stopPoll($poll_id);
	} else {
		die('Access denied!');
	}
?>