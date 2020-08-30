<?php
/**
 * Класс - контроллер для реализации взаимодействия пользовательского ввода
 * с БД и обратно. 
*/
require_once __DIR__.'/../models/channelModel.php';
require_once __DIR__.'/../models/dashboardModel.php';
require_once __DIR__.'/../../core/libs/telegram/telegramTools.php';
require_once __DIR__.'/../../core/libs/validator.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../../core/libs/paginator.php';
require_once __DIR__.'/../../core/coreTools.php';

class DashboardController {
    public $robots_dict = [
        "rssCacher"             => "Обновление RSS",
        "rssParser"             => "Публикатор данных из RSS",
        "vkParser"              => "Публикатор данных из ВК",
        "postScheduler"         => "Публикатор постов",
        "pollScheduler"         => "Публикатор опросов",
        "sсhedulerWatchdog"     => "WatchDog для планировщика"
    ];
    
    public function __construct($action = null) {
        $this->dashboardModel   = new DashboardModel();
        $this->channelModel     = new TelegramChannel();
        $this->user_id 		= (int)$_SESSION['uid'];
        $this->page             = (isset($_GET['page'])) ? (int)$_GET['page'] : 0;
        $this->telegram 	= new TelegramTools(TELEGRAM_BOT_TOKEN);
        $this->tools            = new coreTools($this->user_id);
    }
 
    /**
     * 
     * @return type
     */ 
    public function getVKAccountCount() {
        return $this->dashboardModel->getVKAccountCount($this->user_id);
    }
    
    /**
     * 
     * @return int
     */
    public function getVKImportRulesCount() {
        return $this->dashboardModel->getVKImportRulesCount($this->user_id);
    }
    
    /**
     * 
     * @return int
     */
    public function getVKImportGroupsCount() {
        return $this->dashboardModel->getVKImportGroupsCount($this->user_id);
    }
    
    /**
     * 
     * @return int
     */
    public function getRSSImportRulesCount() {
        return $this->dashboardModel->getRSSImportRulesCount($this->user_id);
    }
    
    /**
     * 
     * @return int
     */
    public function getRSSImportCount() {
        return $this->dashboardModel->getRSSImportCount($this->user_id);
    }
    
    /**
     * 
     * @return int
     */
    public function getRSSMessagesPOstedCount() {
        return $this->dashboardModel->getRSSMessagesPOstedCount($this->user_id);
    }
    
    public function getTelegramChannelsCount() {
        return $this->dashboardModel->getTelegramChannelsCount($this->user_id);
    }
    
    /**
     * 
     * @return array
     */
    public function getTotalShedulerStat() {
        $stat       = $this->dashboardModel->getTotalShedulerStat($this->user_id);
        $total_stat = ["DONE" => 0,"ACTIVE" => 0,"FAIL" => 0,];
        foreach ($stat as $item) {
            $total_stat[$item['status']]++;
        }
        return $total_stat;
    }
    
    /**
     * 
     * @return array
     */
    public function getTodayShedulerStat() {
        $stat       = $this->dashboardModel->getTodayShedulerStat($this->user_id);
        $total_stat = ["DONE" => 0,"ACTIVE" => 0,"FAIL" => 0,];
        foreach ($stat as $item) {
            $total_stat[$item['status']]++;
        }
        return $total_stat;
    }
    
    
    /**
     * 
     * @return array
     */
    public function getTodayRobotsData($user_login) {
        $stat       = $this->dashboardModel->getTodayRobotsData($user_login);
        
        foreach ($stat as $item) {
            $h_robot = $this->robots_dict[$item['bot']];
            if (!isset($total_stat[$h_robot])) {
                $total_stat[$h_robot] = [
                    'date' => $item['date'] 
                ]; 
            } else {
                if ($item['date'] > $total_stat[$h_robot]['date']) {
                    $total_stat[$h_robot]['date'] = $item['date'];
                }
            } 
        }
        //echo "<pre>"; print_r($total_stat); die(); 
        return $total_stat;
    }
     
    

}