<?php
	// Защита от запуска темплета вне контекста админ панели
	if (TEMPLATE_CHECK != 1) { die('');}
	require_once __DIR__.'/../../controllers/channelController.php';
	$channel  			= new ChannelController();
	$channel_list 		= $channel->getUserTelegramChannelsList();
	$user_channel_list 	=  array_filter($channel_list, function($item) {
		return $item->status == 'approved';
	});
	//-------------------------------------------------------//
?>

<div class="col-md-12">
	<h2>Добавить статью:</h2>
	<hr>
	<div class="col-sm-12">
		<form class="register-form" method="post" action="/cabinet/helpers/post.php?action=addPost">
			<label for="basic-url">Выберите канал:</label>
			<div class="input-group col-md-12">
				<select name="user_chanel" class="form-control">
			  		<?php foreach($user_channel_list as $user_channel):?>	
			  			<option value="<?=$user_channel->id;?>"><?=$user_channel->url;?></option>
					<?php endforeach;?>
			  	</select>
			</div>
			<div class="clearfix"></div>
			

			<label for="basic-url">Заголовок:</label>
			<div class="input-group col-md-12">
				<input type="text" name="title" data-validation="TelegramMessageTitle" class="form-control validation_input" placeholder="Заголовок:" value="">
				<div class="clearfix"></div>
				<div class="alert-input alert alert-danger" data-validation-message="title"></div>
			</div>

			<label for="basic-url">Текст публикации:</label>
			<div class="input-group col-md-12">
				<textarea 
					name="text" id="" cols="30" rows="10"
					data-validation="TelegramMessageText" 
					class="form-control validation_input" 
					placeholder="Текст публикации:"
					required
				></textarea>
				<div class="clearfix"></div>
				<div class="alert-input alert alert-danger" data-validation-message="text"></div>
			</div>



			<div>
				<div class="pull-right">
					<input class="btn btn-success btn-send" type="submit" value="Создать публикацию">
				</div>
				<div class="clearfix"></div>
			</div>
		</form>
	</div>	
</div>
 