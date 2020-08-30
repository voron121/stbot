<?php
	// Защита от запуска темплета вне контекста админ панели
	if (TEMPLATE_CHECK != 1) { die('');}
	require_once __DIR__.'/../../controllers/channelController.php';
	$channel  		= new ChannelController();
	$user_channel_list      = $channel->getApprovedUserTelegramChannelsList();
	//-------------------------------------------------------//
?>
<script src="/cabinet/views/poll/js/poll.js"></script>

<div class="col-md-12">
	<h2>Добавить опрос:</h2>
	<hr>
	<div class="col-sm-6">
		<form class="register-form" method="post" action="/cabinet/helpers/poll.php?action=addPoll">
			
			<div class="input-group input-group_floatLeft col-md-6">
				<label for="basic-url">Выберите канал:</label>
				<select name="user_chanel" class="form-control">
			  		<?php foreach($user_channel_list as $user_channel):?>	
			  			<option value="<?=$user_channel->id;?>"><?=$user_channel->channel_name;?></option>
					<?php endforeach;?>
			  	</select>
			</div>
			<div class="col-md-1"></div>
			<div class="input-group input-group_floatLeft col-md-5">
				<label for="basic-url">Уведомлять о публикации:</label>
				<select name="notification" class="form-control">
		  			<option value="No">Не уведомлять</option>
		  			<option value="Yes">Уведомлять</option>
				</select>
			</div>

			<div class="clearfix"></div>

			<label for="basic-url">Вопрос:</label>
			<div class="input-group col-md-12">
				<input type="text" name="question" required data-validation="TelegramPollQuestion" class="form-control validation_input" placeholder="Вопрос:" value="">
				<div class="clearfix"></div>
				<div class="alert-input alert alert-danger" data-validation-message="question"></div>
			</div>
			<div class="answers">
				<div class="answer">
					<label for="basic-url">Ответ <span class="answer_number"></span>:</label>
					<div class="input-group col-md-12">
						<input type="text" name="answer[]" data-validation="TelegramPollAnswer" class="form-control validation_input" placeholder="Ответ:" value="">
						<span class="input-group-btn">
				    	    <button class="btn btn-default remove_answer" type="button"><i class="glyphicon glyphicon-minus"></i></button>
				      	</span> 
						<div class="clearfix"></div>
						<div class="alert-input alert alert-danger" data-validation-message="answer"></div>
					</div>
				</div>
		 	</div>
		
			<div>
				<div class="pull-left">
					<button class="btn btn-default add_answer" type="button"><i class="glyphicon glyphicon-plus"></i> Добавить вариант ответа</button>
				</div>
				<div class="pull-right">
				 	<input class="btn btn-success btn-send" type="submit" value="Создать опрос">
				</div>
				<div class="clearfix"></div>
			</div>
		</form>
	</div>

	<div class="col-sm-6">
		<div class="alert alert-warning">
			<h4>Информация:</h4>
			Опросы в телеграм позволяют реализовать голосования и опросы среди аудитории вашего канала.<br>
			Для того чтобы добавить опрос, укажите телеграм канал, для которого будет создан опрос, <br>
			выберите режим уведомления пользователей, добавьте текст опроса и варианты ответов.<br>
			<hr>
			<h4>Требования к опросу:</h4>
			- Максимальная длина текста опроса  не должна привышать 255 символов<br>
			- Минимум должно быть 2 варианта ответов<br>
			- Максимум  возможно добавить 10 вариантов ответов<br>
		</div>
	</div>
</div>
 