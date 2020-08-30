<?php
	// Защита от запуска темплета вне контекста админ панели
	if (TEMPLATE_CHECK != 1) { die('');}
	require_once __DIR__.'/../../controllers/postController.php';
	//-------------------------------------------------------//
	$post  		= new PostController();
	$polls_list = $post->getUserPollsList();
?>

<h2>Опросы:</h2>

<div class="clearfix"></div>
<?php if (!empty($polls_list)): ?>
	<div class="col-md-12 text-right">
		<a href="/cabinet/home.php?template=post&view=addPoll" class="btn btn-primary">
			<i class="glyphicon glyphicon-signal"></i> Добавить опрос
		</a>	
	</div>
	<div class="clearfix"></div>
	<hr>
	<?php foreach ($polls_list as $item) : ?>
		<div class="poll_wrap <?=$post->getPostCSSClassByStatus($item->status);?>">
			<div class="item_header">
				<div class="col-sm-12">
					<i class="glyphicon glyphicon-calendar"></i> <span><?=$item->created;?></span>
					<i class="glyphicon glyphicon-send"></i> 
					<span>
						<a href="<?=$item->chanel_url;?>" target="blanck">
							<?=$post->getTelegramChannelIdByUrl($item->chanel_url);?>
						</a>
					</span>
				</div>
			</div>
			<div class="col-sm-12">
				<b>Вопрос:</b> <span><?=$item->question;?></span>
			</div>
			<div class="col-sm-12 item_content">
				<b>Ответы:</b> <?=implode(', ', $item->answers)?>
			</div>
			<div class="col-sm-12 item_header_action text-right">
				<hr>
				<a href="/cabinet/helpers/post.php?action=deletePoll&post_id=<?=$item->id;?>" class="btn btn-sm btn-danger delete_post">
					<i class="glyphicon glyphicon-remove"></i> Удалить
				</a>
				<a href="#" class="btn btn-sm btn-warning">
					<i class="glyphicon glyphicon-pencil"></i> Результаты
				</a>
				<?php if ('PUBLISHED' == $item->status):?>
					<a href="/cabinet/helpers/post.php?action=closePoll&post_id=<?=$item->id;?>" class="btn btn-sm btn-info">
						<i class="glyphicon glyphicon-time"></i> Закрыть опрос
					</a>
					<a href="#" class="btn btn-sm btn-default">
						<i class="glyphicon glyphicon-send"></i> Опубликовано
					</a>
				<?php else: ?>
					<a href="#" class="btn btn-sm btn-primary add_schedule" data-toggle="modal" data-target="#scheduler" item-id="<?=$item->id;?>"  item-channel_url="<?=$item->chanel_url;?>" item-type="poll">
						<i class="glyphicon glyphicon-time"></i> Задать дату публикации
					</a>
					<a href="/cabinet/helpers/post.php?action=sendPoll&post_id=<?=$item->id;?>" class="btn btn-sm btn-info">
						<i class="glyphicon glyphicon-send"></i> Опубликовать
					</a>
				<?php endif;?>
			</div>
		</div>
	<?php endforeach;?>
	<?php include __DIR__.'/../scheduler/modal.php';?>
<?php else:?>
	<div class="alert alert-warning text-center">
		<b>Нет данных для отображения</b>
	</div>
	<div class="col-md-12 text-center">
		<a href="/cabinet/home.php?template=post&view=addPoll" class="btn btn-success">
			<i class="glyphicon glyphicon-plus-sign"></i> Добавить опрос
		</a>	
	</div>
<?php endif;?>
<script>
	$('.delete_post').click(function() {
		var retVal = confirm("Вы уверены что хотите удалить публикацию ?");
		if( retVal == true ) {
		  return true;
		} else {
		  return false;
		}
	});
</script>