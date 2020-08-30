<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/channelController.php';
$channel            = new ChannelController();
$user_channel_list  = $channel->getApprovedUserTelegramChannelsList();
//-------------------------------------------------------//
?>

<div class="col-md-12">
    <h2>Добавить статью:</h2>
    <hr>
    <div class="col-sm-6">
        <form class="register-form" method="post" enctype="multipart/form-data" action="/cabinet/helpers/post.php?action=addPost">
            <div class="input-group input-group_floatLeft col-md-6">
                <label for="basic-url">Выберите канал:</label>
                <select name="user_chanel" class="form-control">
                    <?php foreach ($user_channel_list as $user_channel): ?>	
                        <option value="<?= $user_channel->id; ?>"><?= (null != $user_channel->channel_title) ? $user_channel->channel_title : $user_channel->url; ?></option>
                    <?php endforeach; ?>
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

            <!--
            <label for="basic-url">Заголовок:</label>
            <div class="input-group col-md-12">
                    <input type="text" name="title" data-validation="TelegramMessageTitle" class="form-control validation_input" placeholder="Заголовок:" value="">
                    <div class="clearfix"></div>
                    <div class="alert-input alert alert-danger" data-validation-message="title"></div>
            </div>
            -->
            <label for="basic-url">Текст публикации:</label>
            <div class="input-group col-md-12">
                <textarea 
                    name="text" id="TelegramMessageText" cols="30" rows="10"
                    data-validation="TelegramMessageText" 
                    class="form-control validation_input" 
                    placeholder="Текст публикации:"
                    required
                    ></textarea>
                <div class="clearfix"></div>
                <div class="alert-input alert alert-danger" data-validation-message="text"></div>
            </div>
            
            <div class="input-group col-md-12 buttons_list_wraper">
                <input type="file"  name="files[]" multiple="true" />
            </div> 
            
            <?php include __DIR__.'/../inlinebuttons/modal.php'; ?>
            
            <div>
                <div class="pull-right">
                    <input class="btn btn-success btn-send" type="submit" value="Создать публикацию">
                </div>
                <div class="clearfix"></div>
            </div>
        </form>
    </div>

    <div class="col-sm-6">
        <div class="alert alert-warning">
            <h4>Информация:</h4>
            При написание текста можно использовать HTML теги для оформления текста.<br>
            Так же в сообщении могут присутствовать emoji<br>
            В данный момент поддерживаются сл. теги:<br><br>		 
            &lt;b&gt;bold&lt;/b&gt;, &lt;strong&gt;bold&lt;/strong&gt; <br>
            &lt;i&gt;italic&lt;/i&gt;, &lt;em&gt;italic&lt;/em&gt; <br>
            &lt;a href="http://www.example.com/"&gt;inline URL&lt;/a&gt; <br>
            &lt;a href="tg://user?id=123456789"&gt;inline mention of a user&lt;/a&gt; <br>
            &lt;code&gt;inline fixed-width code&lt;/code&gt; <br>
            &lt;pre&gt;pre-formatted fixed-width code block&lt;/pre&gt; <br>
            <hr>
            <h4>Требования к тексту:</h4>
            - Теги не должны быть вложенными.<br>
            - Все символы <,> и &, которые не являются частью тега или объекта HTML, должны быть заменены соответствующими объектами HTML (<с & lt ;,> с & gt; и & с & amp;).<br>
            - Все числовые объекты HTML поддерживаются.<br>
            - В настоящее время API поддерживает только следующие именованные сущности HTML: & lt ;, & gt ;, & amp; и & quot ;.<br> 
            <hr>
            <h4>Кнопки:</h4>
            Вы можете добавить к вашему сообщению несколько кнопок.<br>
            В данный момент поддерживаются только кнопки с ссылками.<br>
            Если вам нужно добавить несколько кнопок в несколько hzzljd - вы можете добавить<br>
            ряд кнопок нажав на кнопку "Добавить ряд кнопок".<br>
            Если вам нужно удалить ряд кнопок - вы можете нажать на иконку корзины <i class="glyphicon glyphicon-trash"></i><br>
            в ряде с кннопками.<br>
            Если вам нужно добавить кнопку в том или ином ряде - вы можете нажать на <br>
            символ <i class="glyphicon glyphicon-plus"></i> в нужном вам ряде кнопок.<br>
        </div>
    </div>

</div>


<script>
    $('#TelegramMessageText').trumbowyg({
        btns: [
            ['viewHTML'],
            ['undo', 'redo'],
            //['formatting'],
            ['strong', 'em'],
            ['link'],
            ['removeformat'],
            ['emoji']
        ]
    });

    $('#TelegramMessageText').on('tbwchange ', function () {
        var validation;
        data = {
            'input_name': $(this).attr('name'),
            'input_value': $(this).val(),
            'validation_type': $(this).attr('data-validation')
        }
        clearTimeout(validation);
        validation = setTimeout(function () {
            validationInput(data);
        }, 1000);
    });
</script>

