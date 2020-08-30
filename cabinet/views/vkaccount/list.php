<?php
    // Защита от запуска темплета вне контекста админ панели
    if (TEMPLATE_CHECK != 1) { die('');}
    require_once __DIR__.'/../../controllers/vkaccountController.php';
    //-------------------------------------------------------//
    $vkcontroller   = new VKAccountController();
    $accounts       = $vkcontroller->getUserVKAccountsList();
    $add_vk_url = "/cabinet/helpers/vkaccount.php?action=add";
    if (true == $vkcontroller->isUserVKAccountLimitExceeded($user->vkaccounts_count)) {
        $add_vk_url = "/cabinet/home.php?template=vkaccount&view=list&message=LimitError";
    }
?>

<div class="clearfix"></div>
<?php if (!empty($accounts)): ?>
    <div class="col-md-8">
        <h2>Список аккаунтов ВК:</h2>
    </div>

    <div class="col-md-4 buttons_header_group text-right">
        <a href="<?=$add_vk_url;?>" class="btn btn-success">
            <i class="glyphicon glyphicon-plus-sign"></i> Добавить аккаунт ВК
        </a>	
    </div>
    <div class="clearfix"></div>
    <hr>
	<?php foreach ($accounts as $item) : ?>
		<div class="telegram_item_wrap approved">
			<div class="item_header">
				<div class="col-sm-8">
                    <div class="col-sm-3 clear_padding_left">
                        <img src="<?=$item->user_photo;?>" alt="<?=$item->first_name;?> <?=$item->last_name;?>" width="100" class="img-thumbnail rounded float-left">
                    </div>

                    <div class="col-sm-9 clear_padding grey_header">
                        <a href="https://vk.com/id<?=$item->vk_user_id;?>" target="blank"><?=$item->first_name;?> <?=$item->last_name;?></a>
                    </div>
                    <div class="col-sm-9 clear_padding grey_info_wraper">
                        <p>Подключено сообществ: <b><?=$item->group_count;?></b></p>
                        <p>Правил импорта сообществ: <b><?=$item->import_rules_count;?></b></p>
                    </div>
				</div> 
			</div> 
		</div>
	<?php endforeach;?>
<?php else:?>
    <div class="col-md-8">
        <h2>Список аккаунтов ВК:</h2>
    </div>
    <div class="clearfix"></div>
	<div class="alert alert-warning text-center">
            <b>Нет данных для отображения</b>
	</div>
        <div class="col-md-12 text-center">
            <a href="/cabinet/helpers/vkaccount.php?action=add" class="btn btn-success">
                <i class="glyphicon glyphicon-plus-sign"></i> Добавить аккаунт ВК
            </a>
            <div class="clearfix"></div>
	</div>
<?php endif;?>
<script>
	$('.delete_channel').click(function() {
		var retVal = confirm("Вы уверены что хотите удалить канал из списка ?");
		if( retVal == true ) {
		  return true;
		} else {
		  return false;
		}
	});
</script>