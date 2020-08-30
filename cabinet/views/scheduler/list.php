<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/schedulerController.php';
//-------------------------------------------------------//
$scheduler = new SchedulerController();
$schedulers_list = $scheduler->getUserSchedulerList();
?>

<h2>Расписание заданий:</h2>

<div class="clearfix"></div>
<?php if (!empty($schedulers_list)): ?>
    <?php foreach ($schedulers_list as $item) : ?>
 
        <div class="sheduler_item_wrap <?= $scheduler->getSchedulerTaskCSSClassByStatus($item->status); ?>">
            <div class="item_header">
                <div class="col-sm-10">
                    <div class="col-sm-12">
                        <b>Задача # <?= $item->id; ?></b>
                        <i class="glyphicon glyphicon-link"></i>
                        <?php if("POST" == $item->item_type):?>
                        <span>
                            <b>Публикация с ид <?= $item->item_id; ?> </b>
                        </span>
                        <?php else:?>
                            <b>Опрос с ид <?= $item->item_id; ?></b>
                        <?php endif;?>
                        

                        <i class="glyphicon glyphicon-send"></i>
                        <span>
                            <a href="https://t.me/<?= str_replace('@', '', $item->chanel_url); ?>" target="blank">
                                <b> <?=(null != $item->channel_title ) ? $item->channel_title : $item->chanel_url ;?></b>
                            </a>
                        </span>
                    </div>

                    <div class="col-sm-3">
                        <i class="glyphicon glyphicon-tasks"></i> <span>
                            Задание: <b><?= $scheduler->getHumanityItemAction($item->item_action); ?></b>
                        </span>
                    </div>

                    <div class="col-sm-3">
                        <i class="glyphicon glyphicon-calendar"></i> <span>
                            Дата: <?= $item->date; ?>
                        </span>
                        <i class="glyphicon glyphicon-time"></i> <span>
                            Время: <?= $item->time; ?>
                        </span>
                    </div>

                    <div class="col-sm-3">
                        <i class="glyphicon glyphicon-info-sign"></i> <span>
                            Статус выполнения: <b><?= $scheduler->getHumanityItemStatus($item->status); ?></b>
                        </span>
                    </div>

                </div>
                <div class="col-sm-2 item_header_action text-right">
                    <?php if(!in_array($item->status, ['DONE', 'FAIL'])):?>
                        <a href="#" 
                            item-id="<?= $item->id; ?>" 
                            itm="scheduler"
                            class="btn btn-sm btn-danger delete_channel delete_item">
                            <i class="glyphicon glyphicon-remove"></i> Удалить
                         </a>
                    <?php endif;?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php $scheduler->getPaginations();?>
<?php else: ?>
    <div class="alert alert-warning text-center">
        <b>Нет данных для отображения</b>
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