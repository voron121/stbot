<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/postController.php';
//-------------------------------------------------------//
$post       = new PostController();
$posts_list = $post->getUserPostsList();
?>


<?php if (!empty($posts_list)): ?>
    <div class="col-md-8">
        <h2>Публикации:</h2>
    </div>

    <div class="col-md-4 buttons_header_group text-right">
        <a href="/cabinet/home.php?template=post&view=addPost" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить статью
        </a> 
    </div>
    <div class="clearfix"></div>
    <hr>
    <?php foreach ($posts_list as $item) : ?>
    <div class="item_wrap <?= $post->getPostCSSClassByStatus($item->status); ?>">
        <div class="item_header">
                <div class="col-sm-6">
                    <?= $post->getPostStatusBadge($item->status); ?>
                    <i class="glyphicon glyphicon-calendar"></i> <span><?= $item->created; ?></span>
                    <i class="glyphicon glyphicon-send"></i> 
                    <span>
                        <a href="<?= $item->chanel_url; ?>" target="blanck">
                            <?= (null != $item->channel_title) ? $item->channel_title : $item->channel_url ; ?>
                        </a>
                    </span>
                </div> 
        <?php if ('PUBLISHED' == $item->status): ?>
            <div class="col-sm-6 item_header_action text-right">
                <a href="#" 
                    item-id="<?= $item->id; ?>" 
                    itm="post"
                    class="btn btn-sm btn-danger delete_channel delete_item">
                     <i class="glyphicon glyphicon-remove"></i> Удалить
                 </a>
                <a href="#" class="btn btn-sm btn-default add_schedule" data-toggle="modal" data-target="#scheduler" 
                   item-id="<?= $item->id; ?>"
                   item-status="<?= $item->status; ?>"
                   item-channel_id="<?= $item->channel_id; ?>"
                   item-type="post">
                    <i class="glyphicon glyphicon-time"></i>
                    <?=('Yes' == $item->is_schedule) ? "Запланировано" : "Запланировать действие" ;?>
                </a>
                <a href="#" class="btn btn-sm btn-default">
                    <i class="glyphicon glyphicon-send"></i> Опубликовано
                </a>
            </div>
        <?php else: ?>
            <div class="col-sm-6 item_header_action text-right">
                <a href="#" 
                    item-id="<?= $item->id; ?>" 
                    itm="post"
                    class="btn btn-sm btn-danger delete_channel delete_item">
                     <i class="glyphicon glyphicon-remove"></i> Удалить
                </a>
                <a href="/cabinet/home.php?template=post&view=edit&id=<?= $item->id; ?>" class="btn btn-sm btn-success">
                    <i class="glyphicon glyphicon-pencil"></i> Редактировать
                </a>
                <a href="#" class="btn btn-sm btn-default add_schedule" data-toggle="modal" data-target="#scheduler" 
                   item-id="<?= $item->id; ?>"
                   item-status="<?= $item->status; ?>"
                   item-channel_id="<?= $item->channel_id; ?>"
                   item-type="post">
                    <i class="glyphicon glyphicon-time"></i>
                    <?=('Yes' == $item->is_schedule) ? "Запланировано" : "Запланировать действие" ;?>
                </a>
                <a href="/cabinet/helpers/post.php?action=sendPost&post_id=<?= $item->id; ?>" class="btn btn-sm btn-info">
                    <i class="glyphicon glyphicon-send"></i> Опубликовать
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($item->title): ?>
            <div class="col-sm-12">
                <i class="glyphicon glyphicon-link"></i> <span><?= $item->title; ?></span>
            </div>
        <?php endif; ?>

            <div class="col-sm-12 item_content">
                <div id="prew_text<?= $item->id; ?>">
                    <?= iconv_substr(strip_tags($item->text), 0 , 60 , "UTF-8" ); ?> ...
                </div>
                <div class="collapse" id="collapse<?= $item->id; ?>">
                    <div class="card card-body"><?= $item->text; ?></div>
                </div>
                <br>
                <a class="btn btn-sm btn-default show_all" data-toggle="collapse" data-cid="<?= $item->id; ?>" href="#collapse<?= $item->id; ?>" role="button" aria-expanded="false" aria-controls="collapse">
                    Посмотреть полностью сообщение
                </a>
            </div>
    </div>
    <?php endforeach; ?>
    <?php $post->getPaginations(); ?>
    <?php include __DIR__ . '/../scheduler/modal.php'; ?>
    <?php else: ?>
        <div class="col-md-8">
            <h2>Публикации:</h2>
        </div>
        <div class="clearfix"></div>
        <div class="alert alert-warning text-center">
            <b>Нет данных для отображения</b>
        </div>
        <div class="col-md-12 text-center">
            <a href="/cabinet/home.php?template=post&view=addPost" class="btn btn-success">
                <i class="glyphicon glyphicon-plus-sign"></i> Добавить статью
            </a>
        </div>
    <?php endif; ?>
<script>
    $('.delete_post').click(function () {
        var retVal = confirm("Вы уверены что хотите удалить публикацию ?");
        if (retVal == true) {
            return true;
        } else {
            return false;
        }
    });
    
    $('.show_all').click(function () {
        colapse_id = $(this).attr('data-cid');
        is_colapsed = $(this).attr('aria-expanded');
        if ("true" === is_colapsed) {
            $("#prew_text"+colapse_id).show();
            $(this).text("Посмотреть полностью сообщение");
        } else {
            $("#prew_text"+colapse_id).hide();
            $(this).text("Свернуть");
        }
    });
</script>
<?php include __DIR__.'/../../helpers/ajax/ajaxModal.php'; ?>