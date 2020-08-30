<?php
    // Защита от запуска темплета вне контекста админ панели
    if (TEMPLATE_CHECK != 1) { die('');}
    require_once __DIR__.'/../../controllers/channelController.php';
    $channel = new ChannelController();
    //-------------------------------------------------------//
?>
<?php if(true == $channel->isUserChannelsLimitExceeded($user->channels_count)):?>
    <script>
        window.location.href = "/cabinet/home.php?template=channel&view=list&message=LimitError";
    </script>
<?php endif;?>
    
<div class="col-md-12">
    <h2>Добавить канал:</h2>
    <hr>
    <div class="col-sm-6">
        <form class="register-form" method="post" action="/cabinet/helpers/channel.php?action=addChannel">
            <label for="basic-url">Ссылка на канал:</label>
            <div class="input-group col-md-12">
              <input type="text" name="url" required data-validation="TelegramUrl" class="form-control validation_input" placeholder="Ссылка на канал:" value="">
              <div class="clearfix"></div>
              <div class="alert-input alert alert-danger" data-validation-message="url"></div>
            </div>
            <div class="clearfix"></div> 
            <hr>
            <div>
                <div class="pull-right">
                    <input class="btn btn-success btn-send" type="submit" value="Добавить">
                </div>
                <div class="clearfix"></div>
            </div>
        </form>
    </div>

    <div class="col-sm-6">
        <div class="alert alert-warning">
            <h4>Порядок добавления канала:</h4>
            <br>
            <p>Для начала работы с сервисом добавьте наш бот <b> @<?=TELEGRAM_BOT_USER_NAME?> </b> в администраторы вашего канала. Бот должен иметь права на публикацию материалов на канале.</p>
            <br>
            <h4>Если ваш канала публичный:</h4>
            <p>Вставьте ссылку на канал вида @channel_name или https://t.me/channel_name  в поле "Ссылка на канал"</p>
            <p>Нажмите кнопку "Добавить"</p>
            <br>
            <h4>Если ваш канала закрытый (частный):</h4>
            <p>Авторизируйтесь в веб версии сервиса телеграм <a href="https://web.telegram.org/#/im" target="blank">https://web.telegram.org/</a></p>
            <p>Скопируйте ссылку на ваш частный канал вида https://web.telegram.org/#/im?p=c1312537902_13717464787299681417</p>
            <p>Вставьте ссылку на канал в поле "Ссылка на канал"</p>
            <p>Нажмите кнопку "Добавить"</p>
            <b>@<?=TELEGRAM_BOT_USER_NAME?> - это наш робот, который реализует взаимодействие сервиса с вашим телеграм-каналом.</b>
        </div>	
    </div>
</div>
 