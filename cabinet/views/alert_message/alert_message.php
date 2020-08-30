<?php
	// Защита от запуска темплета вне контекста админ панели
	if (TEMPLATE_CHECK != 1) { die('');}
	require_once __DIR__.'/../../controllers/alertMessageController.php';
	//-------------------------------------------------------//
	if (isset($_GET['message'])) {
		$messageClass  	= new alertMessageController();
		$message_code 	= (isset($_GET['message'])) ? $_GET['message'] : 'UNKNOW';
		$message_css 	= $messageClass->getMessageCSSClass($message_code);
		$message 		= $messageClass->getMessage($message_code);
	}
?>

<?php if(isset($_GET['message'])):?>
	<div class="alert <?=$message_css;?> alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
	    <span aria-hidden="true">&times;</span>
	  </button>
	  <h4 class="alert-heading"><?=$message['heading'];?></h4>
	  <p><?=$message['message'];?></p>
	</div>
<?php endif;?>




