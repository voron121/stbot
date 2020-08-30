<?php 
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../models/subscriptionsModel.php'; 
require_once __DIR__.'/../models/userModel.php'; 

class SubscriptionsController {

    public function __construct() {
        $this->user_id              = (int)$_SESSION['uid'];
        $this->subscriptionsmodel   = new Subscriptions(); 
        $this->user                 = new User($this->user_id); 
    }
    
    /**
     * 
     * @return type
     */
    public function getSubscriptionsList() {
        $tarifs = $this->subscriptionsmodel->getSubscriptionsList();
        $tarifs = array_filter($tarifs, function($item) {
            return !in_array($item->name, ['Безлимит']);
        });
        return $tarifs;
    }
    
    /**
     * 
     * @param type $subscription_plan_id
     * @return type
     */
    public function changeSubscriptionPlan($subscription_plan_id) {
        $response = ["status" => "ok", "message" => ""]; 
        $new_subscription_plan  = $this->subscriptionsmodel->getSubscriptionPlanById($subscription_plan_id);
        if ($this->user->balance < round(($new_subscription_plan->cost / 30), 2)) {
            $response["status"] = "error";
            $response["message"] = "Для перехода на тарифный план ".$new_subscription_plan->name." не достаточно средств на балансе";
        } else {
            try {
                $this->subscriptionsmodel->changeUserSubscriptionPlan($subscription_plan_id, $this->user_id);
                $response["message"] = "тариф успешно изменен на ".$new_subscription_plan->name;
                Logger::getInstance()->userActionWriteToLog('changeTarifSuccess', 'Пользователь успешно изменил тарифный план на '.$new_subscription_plan->name);
            } catch(\Exception $e) {
                $response["status"] = "error";
                $response["message"] = $e->getMessage();
                Logger::getInstance()->userActionWriteToLog('changeTarifError', $e->getMessage());
            }
        }
        return json_encode($response);
    }
    
    public function getSubscriptionsListForMain() {
        $tarifs = $this->subscriptionsmodel->getSubscriptionsList();
        $tarifs = array_filter($tarifs, function($item) {
            return !in_array($item->name, ['Безлимит']);
        });
        return $tarifs;
    }
 
}

?>