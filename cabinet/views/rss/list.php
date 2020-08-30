<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/rssController.php';
//-------------------------------------------------------//
$rss                = new RSSController();
$rss_list           = $rss->getRSSList();
$rss_add_url        = "/cabinet/home.php?template=rss&view=add";
if (true == $rss->isUserRSSLimitExceeded($user->rss_count)) {
    $rss_add_url = "/cabinet/home.php?template=rss&view=list&message=LimitError";
}
?>

<div class="clearfix"></div>
<?php if (!empty($rss_list)): ?>
    <div class="col-md-8">
        <h2>Список RSS:</h2>
    </div>

    <div class="col-md-4 buttons_header_group text-right">
        <a href="<?=$rss_add_url;?>" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить фаил
        </a>		
    </div>
    <div class="clearfix"></div>
    <hr>
    <?php foreach ($rss_list as $item) : ?>
        <div class="vk_import_item_wrap <?= $rss->getRuleCSSClassByStatus($item->available); ?>">
            <div class="item_header">
                <div class="col-sm-9">
                    <div class="col-sm-12 clear_padding grey_header">
                            <?= $item->url; ?>
                        <div class="import_rule_params">
                            <div class="col-sm-6 clear_padding">
                                <?php if (null != $item->comment && '' != trim($item->comment)): ?>
                                    <p>Примечание: <b> <?= $item->comment; ?></b></p>
                                <?php endif; ?>
                                <span>Добавлен: <b> <?= $item->created; ?></b> </span>
                                <span> Проверен: <b><?= $item->checked; ?></b></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item_header col-sm-3">
                    <div class="item_header_action text-right">
                        <a  href="#" 
                            item-id="<?= $item->id; ?>" 
                            itm="rss"
                            class="btn btn-sm btn-danger delete_channel delete_item">
                            <i class="glyphicon glyphicon-remove"></i> Удалить
                        </a>
                    </div>
                </div>
            </div> 
        </div>
    <?php endforeach; ?>
    <?php $rss->getPaginations(); ?>
<?php else: ?>
    <div class="col-md-8">
        <h2>Список RSS:</h2>
    </div>
    <div class="clearfix"></div>
    <div class="alert alert-warning text-center">
        <b>Нет данных для отображения</b>
    </div>
    <div class="col-md-12 text-center">
        <a href="/cabinet/home.php?template=rss&view=add" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить фаил
        </a>
    </div>
<?php endif; ?>
<?php include __DIR__.'/../../helpers/ajax/ajaxModal.php'; ?>