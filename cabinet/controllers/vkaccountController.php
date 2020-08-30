<?php 
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../../core/coreTools.php';
require_once __DIR__.'/../models/vkaccountModel.php';

class VKAccountController { 
    public function __construct($action = null) {
        $this->user_id 		= (int)$_SESSION['uid'];
        $this->vkmodel 		= new VKAccount();
    }

    /**
     * Возвращает Access token пользователя VK
     * @param 	int 	- Ид пользователя VK
     * @return 	array 	- Access token пользователя VK
    */
    public function getVKAccountAccessToken($vk_account_id) {
        return $this->vkmodel->getVKAccountAccessToken($this->user_id);
    }

    /**
     * Возвращает список подключенных пользователем аккаунтов VK
     * @return 	array 	- Массив с данными пользователького аккаунта из БД
    */
    public function getUserVKAccountsList() {
        $user_dirs      = coreTools::getUserDirs($this->user_id);
        $vk_accounts    = $this->vkmodel->getUserVKAccountsList($this->user_id);
        array_walk($vk_accounts, function (&$item) use ($user_dirs) {
            $item->user_photo = $user_dirs["vk_accounts_path"].$item->vk_user_id.".jpg";
        });
        return $vk_accounts;
    }
    
    /**
     * 
     * @param type $user_limit
     * @return boolean
     */
    public function isUserVKAccountLimitExceeded($user_limit) {
        $is_limit_exceeded = false; 
        $account_count = $this->vkmodel->getVKAccountCount($this->user_id);
        if ($account_count >= $user_limit) {
            $is_limit_exceeded = true;
        }
        return $is_limit_exceeded;
    }
 
}

?>