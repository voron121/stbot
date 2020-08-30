<?php

class DashboardModel {

    protected $db;

    public function __construct() {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }
 
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getVKAccountCount($user_id) {
        $query = 'SELECT COUNT(*)
               FROM vkaccounts
               WHERE user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    
    public function getVKImportRulesCount($user_id) {
        $query = 'SELECT COUNT(*)
               FROM vkgroup_import_rules
               WHERE user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    
    public function getVKImportGroupsCount($user_id) {
        $query = 'SELECT COUNT(*)
               FROM vkgroups
               WHERE user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    
    public function getRSSImportRulesCount($user_id) {
        $query = 'SELECT COUNT(*)
               FROM rss_import_rules
               WHERE user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    
    public function getRSSImportCount($user_id) {
        $query = 'SELECT COUNT(*)
               FROM rss
               WHERE user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    
    public function getRSSMessagesPOstedCount($user_id) {
        $query = 'SELECT COUNT(*)
               FROM rss_import_meta
               WHERE user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getTelegramChannelsCount($user_id) {
        $query = 'SELECT COUNT(*)
               FROM telegram_chanels
               WHERE user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getTotalShedulerStat($user_id) {
        $query = 'SELECT scheduler.`status` as `status`
                FROM scheduler
                    JOIN telegram_chanels ON telegram_chanels.id = scheduler.channel_id
                WHERE scheduler.user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getTodayShedulerStat($user_id) {
        $query = 'SELECT scheduler.`status` as `status`
                FROM scheduler
                    JOIN telegram_chanels ON telegram_chanels.id = scheduler.channel_id
                WHERE scheduler.user_id = :user_id 
                    AND scheduler.`date` = DATE_FORMAT(NOW(), "%Y-%m-%d")';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * 
     * @param type $user_login
     * @return type
     */
    public function getTodayRobotsData($user_login) {
        $query = 'SELECT `date`, 
                         `event`, 
                         `bot`
                FROM action_log
                WHERE user_login = :user_login
                    AND DATE_FORMAT(`date`, "%Y-%m-%d") = DATE_FORMAT(NOW(), "%Y-%m-%d") AND `event` LIKE"%Success"';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_login' => $user_login]);
        return $stmt->fetchAll();
    }
     
    
    
}
