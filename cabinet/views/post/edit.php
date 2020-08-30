<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/channelController.php';
require_once __DIR__ . '/../../controllers/postController.php';
$postController     = new PostController();
$post               = $postController->getUserPostById((int)$_GET['id']);
$channel            = new ChannelController();
$user_channel_list  = $channel->getApprovedUserTelegramChannelsList();
//-------------------------------------------------------//
?>

<div class="col-md-12">
    <h2>Редактировать статью:</h2>
    <hr>
    <div class="col-sm-6">
        <form class="register-form" method="post" enctype="multipart/form-data" action="/cabinet/helpers/post.php?action=editPost">
            <input type="hidden"  name="post_id" value="<?=$post->id?>"/>
            <div class="input-group input-group_floatLeft col-md-6">
                <label for="basic-url">Выберите канал:</label>
                <select name="user_chanel" class="form-control">
                    <?php foreach ($user_channel_list as $user_channel): ?>	
                        <option value="<?=$user_channel->id; ?>" <?=($user_channel->id == $post->channel_id) ? "selected=selected" : "" ?>>
                            <?= (null != $user_channel->channel_title) ? $user_channel->channel_title : $user_channel->url; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1"></div>
            <div class="input-group input-group_floatLeft col-md-5">
                <label for="basic-url">Уведомлять о публикации:</label>
                <select name="notification" class="form-control">
                    <option value="No" <?=("No" == $post->notification) ? "selected=selected" : "" ?>>Не уведомлять</option>
                    <option value="Yes" <?=("Yes" == $post->notification) ? "selected=selected" : "" ?>>Уведомлять</option>
                </select>
            </div>

            <div class="clearfix"></div>
            <label for="basic-url">Текст публикации:</label>
            <div class="input-group col-md-12">
                <textarea 
                    name="text" id="TelegramMessageText" cols="30" rows="10"
                    data-validation="TelegramMessageText" 
                    class="form-control validation_input" 
                    placeholder="Текст публикации:"
                    required
                    ><?=$post->text?></textarea>
                <div class="clearfix"></div>
                <div class="alert-input alert alert-danger" data-validation-message="text"></div>
            </div>
            
            <div class="attachmentMessage alert alert-block"></div>
            
            <div class="input-group col-md-12">
                <?php if(isset($post->attachments)):?>
                    <?php foreach($post->attachments as $attachments):?>
                    <div class="attachments_wraper"> 
                        <div class = "btn btn-sm btn-default"><?=$attachments["file_name"];?> </div>
                        <div class = "btn btn-sm btn-danger delete_item"
                             item-id     = "<?= $post->id; ?>" 
                             itm         = "post_attachment"
                             file        = "<?=$attachments["file_path"];?>"
                             <?=$attachments["file_name"];?>>
                            <i class="glyphicon glyphicon-trash"></i>
                        </div>
                    </div> 
                    <?php endforeach;?>
                <?php endif;?>
            <div class="clearfix"></div>
            </div> 
            <br>
            
            
            <div class="input-group col-md-12 buttons_list_wraper">
                <input type="file"  name="files[]" multiple="true" />
            </div> 
            
            <?php include __DIR__.'/../inlinebuttons/modal.php'; ?>
            
            <div>
                <div class="pull-right">
                    <input class="btn btn-success btn-send pull-right" type="submit" value="Сохранить">
                </div>
                <div class="clearfix"></div>
            </div>
             
            <div class="clearfix"></div>
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
<?php include __DIR__ . '/../../helpers/ajax/ajaxModal.php'; ?>
<script src="/cabinet/views/post/js/post.js"></script>
 
