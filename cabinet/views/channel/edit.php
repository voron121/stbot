<?php
	// Защита от запуска темплета вне контекста админ панели
	if (TEMPLATE_CHECK != 1) { die('');}
	require_once __DIR__.'/../../controllers/templates.php';
	//-------------------------------------------------------//
	$templates  = new TemplatesController();
	$item   	= $templates->getTemplateById($_GET['id']);
	$categories = $templates->getTemlatesSelectOptions();	
?>

<div class="col-md-12">
	<h2>Изменить шаблон</h2>
	<form class="register-form" method="post" action="/admin/helpers/templates.php?action=edit">
		<input name="id" type="hidden" value="<?=$item->id;?>">
		<div class="col-md-7">
			<p>Название:</p>
			<input name="name" type="text" class="form-control" value="<?=$item->name;?>" aria-describedby="basic-addon1">
		</div>

		<div class="col-md-3">
			<p>Статус:</p>
			<select name="status" class="form-control" >
				<option value="UNPUBLISH">Не опубликован</option>
				<option value="PUBLISH" <?php echo ($item->status =='PUBLISH')? 'selected' :'' ; ?>>Опубликован</option>
			</select>
		</div>
		<!--**********************************************************************-->
		<div id="templatesContainer">
		   <?php foreach (unserialize($item->template) as $template_key => $template_item): ?>
				<div id="templateGroup<?=$template_key;?>" class="templateGroup col-md-10">
					<div class="col-md-8">
						<select name="template[<?=$template_key;?>][category]" class="form-control" >
							<?=$templates->getTemlatesSelectOptions( $template_item['category'] );?>
						</select>
					</div>
					<div class="col-md-3">
						<input name="template[<?=$template_key;?>][value]" class="form-control" value="<?=$template_item['value'];?>" />
					</div>
					<div class="col-md-1">
						<input value="x" type="button" class="btn btn-danger"  onClick="deleteThisItem(<?=$template_key;?>)">
					</div>
				</div>
				<div class="clearfix"></div>
			<?php endforeach; ?>
		</div>
		<div class="clearfix"></div>
		<div class="col-md-3 col-md-push-7 text-right">
			<br>
			<input type="button" class="btn btn-primary" id="addTemplateItem" value="Добавить категорию">
		</div>
 		<!--**********************************************************************-->
		<div class="clearfix"></div>
		<hr>
		<div class="col-md-12">
			<input type="submit" class="btn btn-success" value="Сохранить">
		</div>
	</form>	
</div>

<script>
	var i = <?=count(unserialize($item->template))?>;
	$('#addTemplateItem').click(function(event) {
	    addDynamicExtraField(i);
	    i++;
	    return false;
	});

	function addDynamicExtraField(i) {
		var div = $('<div/>', {
	        'class' : 'templateGroup col-md-10'}
	    ).appendTo($('#templatesContainer'));

	    input = $('<div class="col-md-8"><select name="template['+i+'][category]" class="form-control"><?php echo $categories;?></select></div>').appendTo(div);
	    input = $('<div class="col-md-3"><input  name="template['+i+'][value]"    class="form-control" value="1" /></div>').appendTo(div);
	    input = $('<div class="col-md-1"><input value="x" type="button" class="btn btn-danger"></div><div class="clearfix"></div>').appendTo(div);
	    
	    input.click(function() {
	        $(this).parent().remove();
	    });
	}

	function deleteThisItem(id) {
		$('#templateGroup'+id+'').remove();
		return false;
	}
</script>

<link rel="stylesheet" href="../admin/views/templates/css/templates.css" >