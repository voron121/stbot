<?php
    // Защита от запуска темплета вне контекста админ панели
    if (TEMPLATE_CHECK != 1) { die('');}
    require_once __DIR__.'/../../controllers/vkgroupsImportController.php';
    //-------------------------------------------------------//
    $options = [];
    if (isset($_GET['group_id'])) {
        $options['group_id'] = strip_tags($_GET['group_id']);
    }
    if (isset($_GET['channel_id'])) {
        $options['channel_id'] = strip_tags($_GET['channel_id']);
    }
    $vk_import_rules  	= new VKGroupsImportController();
    $rules              = $vk_import_rules->getVKImportRulesList($options);
    $add_rule_link      = "/cabinet/home.php?template=vkgroupsimport&view=add";
    if (true == $vk_import_rules->isUserVKRulesLimitExceeded($user->vk_rule_count)) {
        $add_rule_link = "/cabinet/home.php?template=vkgroupsimport&view=list&message=LimitError";
    }
?>

<div class="clearfix"></div>
<?php if (!empty($rules)): ?>
    <div class="col-md-8">
        <h2>Список правил  импорта ВК:</h2>
    </div>

    <div class="col-md-4 buttons_header_group text-right">
        <a href="<?=$add_rule_link;?>" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить правило
        </a>		
    </div>
    <div class="clearfix"></div>
    <hr>
    <?php foreach ($rules as $item) : ?>
        <div class="vk_import_item_wrap <?= $vk_import_rules->getRuleCSSClassByStatus($item->state); ?>">
            <div class="item_header">
                <div class="col-sm-9">
                    <div class="col-sm-12 clear_padding grey_header">
                        <?= $item->name; ?>
                        <div class="import_rule_params">
                            <div class="col-sm-6 clear_padding">
                                <p>Импортируем сообщество <b> <?= $item->vk_group; ?> </b> в <b> <?= $item->telegram_chanel; ?></b></p>
                                <p>Режим импорта: <b><?= $item->humanized_mode; ?></b></p>
                                <p>Режим импорта текста: <b><?= $item->humanized_text_mode; ?></b></p>
                            </div>
                            <div class="col-sm-6 clear_padding">
                                <p>Порядок импорта: <b><?= $item->humanized_order; ?></b></p>
                                <p>Режим обработки ссылок: <b><?= $item->humanized_url_mode; ?></b></p>
                                <p>Импортированно сообщений: <b><?= $item->count; ?></b></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item_header col-sm-3">
                    <div class="item_header_action text-right">
                        <?php if ('off' == $item->state): ?>
                            <a href="/cabinet/helpers/vkgroupsimport.php?action=on&rule_id=<?= $item->id; ?>" class="btn btn-success btn-sm delete_channel">
                                <i class="glyphicon glyphicon-play"></i> Включить
                            </a>
                        <?php else: ?>
                            <a href="/cabinet/helpers/vkgroupsimport.php?action=off&rule_id=<?= $item->id; ?>" class="btn btn-warning btn-sm delete_channel">
                                <i class="glyphicon glyphicon-pause"></i> Выключить
                            </a>
                        <?php endif; ?>
                        <a href="/cabinet/home.php?template=vkgroupsimport&view=edit&rule_id=<?= $item->id; ?>" class="btn btn-sm btn-warning delete_channel">
                            <i class="glyphicon glyphicon-pencil"></i> Изменить
                        </a>
                        <a href="#"
                            item-id="<?= $item->id; ?>" 
                            itm="vkimportrule"
                            class="btn btn-sm btn-danger delete_channel delete_item">
                            <i class="glyphicon glyphicon-remove"></i> Удалить
                        </a>
                    </div>
                </div>
            </div> 
        </div>
    <?php endforeach; ?>
    <?php $vk_import_rules->getPaginations(); ?>
<?php else: ?>
    <div class="col-md-8">
        <h2>Список правил импорта ВК:</h2>
    </div>
    <div class="clearfix"></div>
    <div class="alert alert-warning text-center">
        <b>Нет данных для отображения</b>
    </div>
    <div class="col-md-12 text-center">
        <a href="/cabinet/home.php?template=vkgroupsimport&view=add" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить правило
        </a>	
    </div>
<?php endif; ?>
<?php include __DIR__ . '/../../helpers/ajax/ajaxModal.php'; ?>