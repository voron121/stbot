<?php
    // Защита от запуска темплета вне контекста админ панели
    if (TEMPLATE_CHECK != 1) { die('');}
    require_once __DIR__.'/../../controllers/logsController.php';
    //-------------------------------------------------------//
    $logs       = new logsController();
    $logs_list 	= $logs->getLogsList();

?>

<h2>Лог работы роботов:</h2>
<hr>
<div class="clearfix"></div>
<?php if (empty($logs_list)):?>
    <b>Записей нет</b>
<?php else:?>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Дата</th>
            <th>Робот</th>
            <th>Событие</th>
            <th>Сообщение</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($logs_list as $item) : ?>
            <tr>
                <td><b><?=$item->date;?></b></td>
                <td style="word-break: break-all;"><?=$item->bot;?></td>
                <td style="word-break: break-all;"><?=$item->event;?></td>
                <td style="word-break: break-all;"><?=$item->message;?></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    <?php $logs->getPaginations();?>
<?php endif;?>