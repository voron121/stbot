<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/pollController.php';
//-------------------------------------------------------//
$poll       = new PollController();
$polls_list = $poll->getUserPollsList();
?>

<div class="clearfix"></div>
<?php if (!empty($polls_list)): ?>
    <div class="col-md-8">
        <h2>Опросы:</h2>
    </div>
    <div class="col-md-4 buttons_header_group text-right">
        <a href="/cabinet/home.php?template=poll&view=add" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить опрос
        </a>	
    </div>
    <div class="clearfix"></div>
    <hr> 
    <?php foreach ($polls_list as $item) : ?>
        <div class="poll_wrap <?= $poll->getPostCSSClassByStatus($item->status); ?>">
            <div class="item_header">
                <div class="col-sm-12">
                    <i class="glyphicon glyphicon-calendar"></i> <span><?= $item->created; ?></span>
                    <i class="glyphicon glyphicon-send"></i> 
                    <span>
                        <a href="<?= $item->channel_url; ?>" target="blanck">
                            <?= (null != $item->channel_title) ? $item->channel_title : $item->channel_url; ?>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-sm-12">
                <b>Вопрос:</b> <span><?= $item->question; ?></span>
            </div>
            <div class="col-sm-12 item_content">
                <b>Ответы:</b> <?= implode(', ', $item->answers) ?>
            </div>
            <div class="col-sm-12 item_header_action text-right">
                <hr>
                <a href="#" 
                    item-id="<?= $item->id; ?>" 
                    itm="poll"
                    class="btn btn-sm btn-danger delete_channel delete_item">
                    <i class="glyphicon glyphicon-remove"></i> Удалить
                </a>
                <?php if ('PUBLISHED' == $item->status || 'CLOSE' == $item->status): ?>
                    <a href="#" class="btn btn-sm btn-warning">
                        <i class="glyphicon glyphicon-refresh"></i> Результаты
                    </a>
                <?php if ('PUBLISHED' == $item->status): ?>
                    <a href="/cabinet/helpers/poll.php?action=closePoll&poll_id=<?= $item->id; ?>" class="btn btn-sm btn-info">
                        <i class="glyphicon glyphicon-remove"></i> Закрыть опрос
                    </a>
                <?php endif ?>
        <?php else: ?>
            <a href="/cabinet/helpers/poll.php?action=sendPoll&poll_id=<?= $item->id; ?>" class="btn btn-sm btn-info">
                <i class="glyphicon glyphicon-send"></i> Опубликовать
            </a>
        <?php endif; ?>
                <a href="#" class="btn btn-sm btn-default add_schedule" data-toggle="modal" data-target="#scheduler" 
                   item-id="<?= $item->id; ?>"
                   item-status="<?= $item->status; ?>"
                   item-channel_id="<?= $item->channel_id; ?>"
                   item-type="poll">
                    <i class="glyphicon glyphicon-time"></i>
                    <?=('Yes' == $item->is_schedule) ? "Запланировано" : "Запланировать действие" ;?>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
    <?php include __DIR__ . '/../scheduler/modal.php'; ?>
<?php else: ?>
    <div class="col-md-8">
        <h2>Опросы:</h2>
    </div>
    <div class="clearfix"></div>
    <div class="alert alert-warning text-center">
        <b>Нет данных для отображения</b>
    </div>
    <div class="col-md-12 text-center">
        <a href="/cabinet/home.php?template=poll&view=add" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить опрос
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
</script>
<?php include __DIR__.'/../../helpers/ajax/ajaxModal.php'; ?>