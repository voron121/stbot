<?php
    // Защита от запуска темплета вне контекста админ панели
    if (TEMPLATE_CHECK != 1) { die('');}
    require_once __DIR__.'/../../controllers/vkgroupsController.php';
    $vkgroups = new VKGroupsController();
    //-------------------------------------------------------//
?>
<?php if(true == $vkgroups->isUserVKGroupsLimitExceeded($user->vk_publics_count)):?>
    <script>
        window.location.href = "/cabinet/home.php?template=vkgroupsimport&view=list&message=LimitError";
    </script>
<?php endif;?>

<script src="/cabinet/views/vkgroups/js/vkgroups.js"></script>

<div class="col-md-12">
	<h2>Добавить паблик:</h2>
	<hr>
	<div class="col-sm-6">
		<form class="register-form" method="post" action="/cabinet/helpers/vkgroups.php?action=add">
			
			<div class="input-group col-md-12">
				<label for="basic-url">Выберите пользователя Вконтакте:</label>
				<select name="vk_user_id" class="form-control">
			  		<?php foreach($vkgroups->getUserVKAccountsList() as $vk_user):?>	
			  			<option value="<?=$vk_user->vk_user_id;?>"><?=$vk_user->first_name;?> <?=$vk_user->last_name;?></option>
					<?php endforeach;?>
			  	</select>
			</div>   

			<div class="input-group  col-md-12">
				<label for="basic-url">Ссылка на паблик Вконтакте: <span class="answer_number"></span>:</label>
				<div class="input-group col-md-12">
					<input type="text" name="vk_group_url" data-validation="TelegramPollAnswer" class="form-control validation_input" placeholder="Ссылка на паблик:" value="">
					<div class="clearfix"></div>
					<div class="alert-input alert alert-danger" data-validation-message="answer"></div>
				</div>
			</div>
			<div class="clearfix"></div>
	 		<br>
	 		<div class="pull-right">
				<input class="btn btn-success btn-send" type="submit" value="Добавить паблик">
			</div>
		</form>
	</div>

	<div class="col-sm-6">
		<div class="alert alert-warning">
			<h4>Информация:</h4>
			    <p>Вы можете подключить паблик Вконтакте.</p>
                <p>Укажите аккаунт Вконтакте, который прикреплен к паблику (пользователь ВК вступил в паблик)</p>
                <p>Укажите ссылку на импортируемый паблик. Паблик должен быть публичным и иметь псевдоним ссылки.</p>
                <p>Паблики с ссылками вида https://vk.com/public_00001 не поддерживаются</p>
		</div>
	</div>
</div>
 