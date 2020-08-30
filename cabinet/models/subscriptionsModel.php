<?php
class Subscriptions {
    protected $db; 

    public function __construct($param = null) {
        $this->db = $this->getDBConnection();
    }

    /**
     * Коннектор к БД
     * @return  obj     - Объект PDO
     */
    protected function getDBConnection() {
        $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $db;
    }
    
    /**
     * 
     * @return type
     */
    public function getSubscriptionsList() {
        $query = 'SELECT subscription_id, 
                     name, 
                     description, 
                     cost, 
                     sheduler_task_count, 
                     vkaccounts_count,
                     channels_count, 
                     vk_publics_count, 
                     vk_rule_count, 
                     rss_count, 
                     rss_rule_count, 
                     disc_space
                FROM subscription_plans 
                ORDER BY subscription_id ASC';
        $stmt  = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * 
     * @param type $subscription_id
     * @return type
     */
    public function getSubscriptionPlanById($subscription_id) {
        $query = 'SELECT subscription_id, 
                         name, 
                         description, 
                         cost, 
                         sheduler_task_count, 
                         vkaccounts_count,
                         channels_count, 
                         vk_publics_count, 
                         vk_rule_count, 
                         rss_count, 
                         rss_rule_count, 
                         disc_space
                FROM subscription_plans
                WHERE subscription_id = :subscription_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute(["subscription_id" => $subscription_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * 
     * @param type $subscription_plan_id
     * @param type $user_id
     */
    public function changeUserSubscriptionPlan($subscription_plan_id, $user_id) {
        $query = 'UPDATE users 
                    SET subscription_id = :subscription_id 
                    WHERE id            = :user_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ":subscription_id"  => $subscription_plan_id,
            ":user_id"          => $user_id
        ]);
    }
     
}
?>