<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../controllers/paymentController.php';
require_once __DIR__.'/../../core/libs/logger.php';
//-------------------------------------------------------//

$payment_id 			= (int)$_POST["MERCHANT_ORDER_ID"];
$merchant_payment_id	= (int)$_POST["intid"];
$payment_sum 			= $_POST["AMOUNT"];
$sign 					= $_POST["SIGN"];
//-------------------------------------------------------//

if (md5(FREE_CASSA_MERCHANT_ID.':'.$payment_sum.':'.FREE_CASSA_SECRET2.':'.$payment_id) != $sign) {
	Logger::getInstance()->userActionWriteToLog('paymentError', 'Ошибка платежа с ид '.$payment_id.': не совпадают контрольные суммы платежа');
	die("Access denied!");
}
//-------------------------------------------------------//

$payment_controller = new PaymentController();
$payment_controller->paymentSuccess($payment_id, $merchant_payment_id);

?>