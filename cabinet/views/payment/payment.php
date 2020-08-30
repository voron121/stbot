<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) { die('');}
require_once __DIR__.'/../../controllers/paymentController.php';
$payment_controller = new PaymentController();
//-------------------------------------------------------//
?>

<h2>Пополнить баланс:</h2>
<hr>
<div class="clearfix"></div>
<div class="col-md-3"></div>
<div class="col-md-5">
    <form method="post" action="/cabinet/helpers/payment.php?action=payment">
        <div class="input-group col-md-12">
            <label>Сумма платежа:</label>
            <input type="number" min="1" name="cost" class="form-control" placeholder="Сумма платежа:" value="">
            <div class="clearfix"></div>
        </div>
        <br>
        <div class="pull-right">
            <input class="btn btn-success btn-send" type="submit" value="Пополнить">
        </div>
    </form>
</div>
<div class="col-md-3"></div>
<div class="clearfix"></div>
<hr>
<div class="col-md-12">
    <h4>История пополнения баланса:</h4>
</div>
<div class="col-md-12">
    <?php if(empty($payment_controller->getPaymentsList())):?>
        Платежей нет
    <?php else:?>
        <?php foreach($payment_controller->getPaymentsList() as $payment):?>
            <div class="sheduler_item_wrap <?= $payment_controller->getPaymentCSSClassByStatus($payment->status); ?>">
            <div class="item_header">
                <div class="col-sm-12">
                    <div class="col-sm-3">
                        <b>Платеж# <?=$payment->id;?></b> 
                        <b>Дата: <?=$payment->date;?></b> 
                    </div>
                    <div class="col-sm-3">
                        <b>Сумма: <?=$payment->sum;?> USD</b>
                    </div>
                    
                    <div class="col-sm-2">
                        <b>Статус: <?=$payment->status;?></b>
                    </div>
                    
                    <?php if('new' == $payment->status):?>
                    <div class="col-sm-4">
                        <form method="post" action="/cabinet/helpers/payment.php?action=paymentContinue">
                            <input type="hidden" min="1" name="payment_id" style="display:none" value="<?=$payment->id?>">
                            <input type="number" min="1" name="cost" style="display:none" value="<?=$payment->sum?>">
                            <div class="pull-right">
                                <input class="btn btn-sm btn-warning btn-send" type="submit" value="Завершить платеж">
                            </div>
                        </form>
                    </div> 
                    <?php endif;?>
                </div> 
            </div>
        </div>
        <?php endforeach;?>
        <?php $payment_controller->getPaginations(); ?>
    <?php endif;?>
</div>
