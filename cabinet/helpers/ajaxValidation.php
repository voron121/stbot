<?php
	/**
	 * Реализует взаимодействие с классом валидатора для ajax проверки данных, введнные в формы
	*/
	require_once __DIR__.'/../init.php';
	require_once __DIR__.'/../../core/libs/validator.php';
	//-------------------------------------------------------//
	$data = (isset($_POST['data'])) ?  $_POST['data'] : null;
	if (null == $data || !isset($data['validation_type'])) {
		die('Access denied!');
	}
	echo Validator::getInstance()->init($data);
?>