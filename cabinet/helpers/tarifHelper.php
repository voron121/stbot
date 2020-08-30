<?php
    require_once __DIR__.'/../init.php';
    require_once __DIR__.'/../controllers/subscriptionsController.php';
    //-------------------------------------------------------//
    $rsscontroller      = new SubscriptionsController();
    $subscription_id    = (isset($_POST['subscription_id'])) ? (int)$_POST['subscription_id'] : null;
    echo $rsscontroller->changeSubscriptionPlan($subscription_id);
?>