<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/subscriptionsController.php';
//-------------------------------------------------------//
$subscriptions          = new SubscriptionsController();
$subscriptions_list     = $subscriptions->getSubscriptionsList(); 
?>

<div class="clearfix"></div> 
<div class="col-md-8">
    <h2>Тарифы:</h2>
</div>
<div class="clearfix"></div>
<hr>
<div class="msg"></div>
<?php foreach ($subscriptions_list as $subscription) : ?>
<div class="col-sm-3 tarif_wraper <?=$user->subscription_id == $subscription->subscription_id ? "tarif_wraper_active" : ""?>">
    <div class="tarif_header">
        <h3><?=$subscription->name;?></h3>
    </div>
    <hr>
    <div class="tarif_options">
        <table>
            <tr>
                <td>Задач:</td>
                <td class="tarif_option_item"><?=$subscription->sheduler_task_count;?> в день</td>
            </tr>
            <tr>
                <td>Каналов Телеграм:</td>
                <td class="tarif_option_item"><?=$subscription->channels_count;?> шт.</td>
            </tr>
            <tr>
                <td>Аккаунтов VK:</td>
                <td class="tarif_option_item"><?=$subscription->vkaccounts_count;?> шт.</td>
            </tr>
            <tr>
                <td>Пабликов VK:</td>
                <td class="tarif_option_item"><?=$subscription->vk_publics_count;?> шт.</td>
            </tr>
            <tr>
                <td>Правил для VK:</td>
                <td class="tarif_option_item"><?=$subscription->vk_rule_count;?> шт.</td>
            </tr> 
            <tr>
                <td>RSS каналов:</td>
                <td class="tarif_option_item"><?=$subscription->rss_count;?> шт.</td>
            </tr>
            <tr>
                <td>Правил для RSS:</td>
                <td class="tarif_option_item"><?=$subscription->rss_rule_count;?> шт.</td>
            </tr>  
            <tr>
                <td>Дисковая квота:</td>
                <td class="tarif_option_item"><?=$subscription->disc_space;?> мб.</td>
            </tr>
        </table> 
    </div>
    <div class="tarifs_actions">
        <?php if ($user->subscription_id == $subscription->subscription_id):?>
            <div>
                <div class="btn btn-default">Выбран</div>
            </div>
        <?php else:?>
            <div>
                <div class="btn btn-default tarif_action_btn" data-tarif="<?=$subscription->subscription_id; ?>">Выбрать</div>
            </div>
        <?php endif;?>
    </div>
</div>
<?php endforeach; ?>
<script src="/cabinet/views/tarifs/js/tarifs.js"></script>