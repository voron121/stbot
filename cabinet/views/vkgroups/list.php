<?php
    // Защита от запуска темплета вне контекста админ панели
    if (TEMPLATE_CHECK != 1) { die('');}
    require_once __DIR__.'/../../controllers/vkgroupsController.php';
    //-------------------------------------------------------//
    $vkgroups  		= new VKGroupsController();
    $groups 		= $vkgroups->getGroupsList();
    $group_add_link = "/cabinet/home.php?template=vkgroups&view=add";
    if (true == $vkgroups->isUserVKGroupsLimitExceeded($user->vk_publics_count)) {
        $group_add_link = "/cabinet/home.php?template=vkgroupsimport&view=list&message=LimitError";
    }
?>

<div class="clearfix"></div>
<?php if (!empty($groups)): ?>
    <div class="col-md-8">
        <h2>Список пабликов ВК:</h2>
    </div>

    <div class="col-md-4 buttons_header_group text-right">
        <a href="<?=$group_add_link;?>" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить паблик
        </a>	
    </div>
    <div class="clearfix"></div>
    <hr>
	<?php foreach ($groups as $item) : ?>
		<div class="telegram_item_wrap active_item">
			<div class="item_header">
				<div class="col-sm-9">
					<div class="col-sm-3 clear_padding_left">
						<img src="<?=$item->group_photo;?>" alt="<?=$item->group_name;?>" width="200" class="img-thumbnail rounded float-left">	
					</div>
                    <div class="col-sm-9 clear_padding grey_header">
                        <a href="https://vk.com/<?=$item->screen_name;?>" target="blank"><?=$item->group_name;?></a>
                    </div>
                    <div class="col-sm-9 clear_padding grey_info_wraper">
                        <p>Количество записей в группе: <b><?=$item->record_count;?></b></p>
                        <p>Количество правил импорта: 
                                <a href="/cabinet/home.php?template=vkgroupsimport&view=list&group_id=<?=$item->group_id;?>" target="blank">
                                    <b><?=$item->rules_count;?> показать</b>
                                </a>
                        </p>
                        <p>Последнее обновление: <b><?=$item->updated;?></b></p>
                    </div>
				</div>
				<div class="item_header col-sm-3">
					<div class="item_header_action text-right"> 
						<a href="#" item-id="<?=$item->group_id;?>" itm="vkgroup" class="btn btn-sm btn-danger delete_channel delete_item">
							<i class="glyphicon glyphicon-remove"></i> Удалить
						</a>
					</div>
				</div>
			</div> 
		</div>
	<?php endforeach;?>
        <?php $vkgroups->getPaginations();?>
<?php else:?>
    <div class="col-md-8">
        <h2>Список пабликов ВК:</h2>
    </div>
    <div class="clearfix"></div>
    <div class="alert alert-warning text-center">
            <b>Нет данных для отображения</b>
    </div>
    <div class="col-md-12 text-center">
            <a href="/cabinet/home.php?template=vkgroups&view=add" class="btn btn-success">
        <i class="glyphicon glyphicon-plus-sign"></i> Добавить паблик
            </a>	
    </div>
<?php endif;?>
<?php include __DIR__.'/../../helpers/ajax/ajaxModal.php'; ?>