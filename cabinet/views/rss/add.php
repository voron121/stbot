<?php
    // Защита от запуска темплета вне контекста админ панели
    if (TEMPLATE_CHECK != 1) { die('');}
    require_once __DIR__.'/../../models/vkaccountModel.php';
    require_once __DIR__ . '/../../controllers/rssController.php';
    //-------------------------------------------------------//
    $rss            = new RSSController();
    $vkModel        = new VKAccount();
    $vk_accounts    = $vkModel->getUserVKAccountsList((int)$_SESSION['uid']);
    //-------------------------------------------------------//
?>
<?php if(true == $rss->isUserRSSLimitExceeded($user->rss_count)):?>
    <script>
        window.location.href = "/cabinet/home.php?template=rss&view=list&message=LimitError";
    </script>
<?php endif;?>
<div class="col-md-12">
	<h2>Добавить фаил RSS:</h2>
	<hr>
	<div class="col-sm-6">
		<form class="register-form" method="post" action="/cabinet/helpers/rss.php?action=add">
            <div class="input-group col-md-12">
                <label for="basic-url">Ссылка на фаил:</label>
                <input type="text" name="url" required class="form-control" placeholder="Ссылка на фаил:" value="">
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Комментарий:</label>
                <input type="text" name="comment" class="form-control" placeholder="Комментарий:" value="">
            </div>

			<div class="clearfix"></div>
	 		<br>
	 		<div class="pull-right">
				<input class="btn btn-success btn-send" type="submit" value="Добавить">
			</div>
		</form>
	</div>

	<div class="col-sm-6">
		<div class="alert alert-warning">
			<h4>Информация:</h4>
            <p></p>
        </div>
	</div>
</div>
