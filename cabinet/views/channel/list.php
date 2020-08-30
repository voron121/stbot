<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/channelController.php';
//-------------------------------------------------------//
$channel            = new ChannelController();
$channel_list       = $channel->getUserTelegramChannelsList();
$channel_add_url    = "/cabinet/home.php?template=channel&view=add";
if(true == $channel->isUserChannelsLimitExceeded($user->channels_count)) {
    $channel_add_url = "/cabinet/home.php?template=channel&view=list&message=LimitError";
}
?>

<div class="clearfix"></div>
<?php if (!empty($channel_list)): ?>
    <div class="col-md-8">
       <h2>Список каналов:</h2>
    </div>
    
    <div class="col-md-4 buttons_header_group text-right">
        <a href="<?=$channel_add_url;?>" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить канал
        </a>
    </div>

    <div class="clearfix"></div>
    <hr>
    <?php foreach ($channel_list as $item) : ?>
        <div class="telegram_item_wrap <?= ('approved' == $item->status) ? 'approved' : 'not_approved'; ?>">
            <div class="item_header">
                <div class="col-sm-9">
                    <div class="col-sm-3 clear_padding_left">
                        <a href="<?= $item->url; ?>" target="blank">
                            <img src="<?=$item->channel_photo;?>" width="200" class="img-thumbnail rounded float-left">	
                        </a>
                    </div>
                    <div class="col-sm-9 clear_padding grey_header">
                        <a href="<?= $item->url; ?>" target="blank"><?=(null != $item->channel_title) ? $item->channel_title :  $item->url; ?></a>
                    </div>
                    <div class="col-sm-9 clear_padding grey_info_wraper">
                        <p>Количество участников: <b><?=$item->channel_users_counter;?></b></p>
                        <p>Количество правил импорта: 
                            <a href="/cabinet/home.php?template=vkgroupsimport&view=list&channel_id=<?= $item->id;?>" target="blank">
                                <b><?=$item->rules_count;?> показать</b>
                            </a>
                        </p>
                    </div>
                </div>
                <div class="col-sm-12 item_header_action text-right" style="margin: -10px 0px 0px 0px;">
                    <a href="#" 
                       item-id="<?= $item->id; ?>" 
                       itm="channel"
                       class="btn btn-sm btn-danger delete_channel delete_item">
                        <i class="glyphicon glyphicon-remove"></i> Удалить
                    </a>
                    <a href="/cabinet/home.php?template=channel_stat&view=list&channel_id=<?= $item->id;?>" class="btn btn-sm btn-default">
                        <i class="glyphicon glyphicon-stats"></i> Статистика канала
                    </a>
                    <a href="/cabinet/helpers/channel.php?action=synchChannel&channel_id=<?= $item->id; ?>" class="btn btn-sm btn-success">
                        <i class="glyphicon glyphicon-refresh"></i> Обновить
                    </a>
                </div>
            </div> 
        </div> 
    <?php endforeach; ?>
    <?php $channel->getPaginations();?>
<?php else: ?>
    <div class="col-md-8">
       <h2>Список каналов:</h2>
    </div>
    <div class="clearfix"></div>
    <div class="alert alert-warning text-center">
        <b>Нет данных для отображения</b>
    </div>
    <div class="col-md-12 text-center">
        <a href="/cabinet/home.php?template=channel&view=add" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить канал
        </a>	
    </div>
<?php endif; ?>
<?php include __DIR__.'/../../helpers/ajax/ajaxModal.php'; ?>