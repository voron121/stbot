<?php
require_once __DIR__.'/../init.php';
require_once __DIR__.'/../controllers/paymentController.php';
//-------------------------------------------------------//
$cost               = (isset($_POST['cost'])) 		? (int)$_POST['cost'] 		: 0 ;
$payment_id			= (isset($_POST['payment_id'])) ? (int)$_POST['payment_id'] : null ;
$payment_controller = new PaymentController($cost);
//-------------------------------------------------------//

if ("payment" == $_GET['action']) { // Оплата 
    $payment_controller->createPaymentRequest();
} else if ("paymentOk" == $_GET['action']) { // В случае успха
    $payment_controller->paymentSuccess();
} else if ("paymentContinue"== $_GET['action']) {
	$payment_controller->paymentContinue($payment_id);
} else if ("paymentError" == $_GET['action']) { // В случае неудачи
    $payment_controller->paymentError();
} else {
    die("Access denied");
}
//-------------------------------------------------------//

 
?>