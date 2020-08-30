<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/rssImportController.php';
//-------------------------------------------------------//
$rss            = new RSSImportController();
$rss_rules_list = $rss->getRSSImportRulesList();

$rss_import_rule_add_url = "/cabinet/home.php?template=rssimport&view=add";
if (true == $rss->isUserRSSImportRulesLimitExceeded($user->rss_rule_count)) {
    $rss_import_rule_add_url = "/cabinet/home.php?template=rssimport&view=list&message=LimitError";
}

?>

<div class="clearfix"></div>
<?php if (!empty($rss_rules_list)): ?>
    <div class="col-md-8">
        <h2>Список правил импорта RSS:</h2>
    </div>

    <div class="col-md-4 buttons_header_group text-right">
        <a href="<?=$rss_import_rule_add_url;?>" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить правило
        </a>		
    </div>
    <div class="clearfix"></div>
    <hr>
    <?php foreach ($rss_rules_list as $item) : ?>
        <div class="vk_import_item_wrap <?= $rss->getImportRuleCSSClassByStatus($item->state); ?>">
            <div class="item_header">
                <div class="col-sm-9">
                    <div class="col-sm-12 clear_padding grey_header">
                        <?= $item->name; ?>
                        <div class="import_rule_params">
                            <div class="col-sm-6 clear_padding">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item_header col-sm-3">
                    <div class="item_header_action text-right">
                        <!--
                        <?php if ('off' == $item->state): ?>
                            <a href="/cabinet/helpers/vkgroupsimport.php?action=on&rule_id=<?= $item->id; ?>" class="btn btn-success btn-sm delete_channel">
                                <i class="glyphicon glyphicon-play"></i> Включить
                            </a>
                        <?php else: ?>
                            <a href="/cabinet/helpers/vkgroupsimport.php?action=off&rule_id=<?= $item->id; ?>" class="btn btn-warning btn-sm delete_channel">
                                <i class="glyphicon glyphicon-pause"></i> Выключить
                            </a>
                        <?php endif; ?>
                        -->
                        <a href="/cabinet/home.php?template=rssimport&view=edit&rule_id=<?= $item->id; ?>" class="btn btn-sm btn-warning delete_channel">
                            <i class="glyphicon glyphicon-pencil"></i> Изменить
                        </a>
                        <a  href="#" 
                            item-id="<?= $item->id; ?>" 
                            itm="rssimportrule"
                            class="btn btn-sm btn-danger delete_channel delete_item">
                            <i class="glyphicon glyphicon-remove"></i> Удалить
                        </a>
                    </div>
                </div>
            </div> 
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="col-md-8">
        <h2>Список правил импорта RSS:</h2>
    </div>
    <div class="clearfix"></div>
    <div class="alert alert-warning text-center">
        <b>Нет данных для отображения</b>
    </div>
    <div class="col-md-12 text-center">
        <a href="/cabinet/home.php?template=rssimport&view=add" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить правило
        </a>
    </div>
<?php endif; ?>
<?php include __DIR__.'/../../helpers/ajax/ajaxModal.php'; ?>