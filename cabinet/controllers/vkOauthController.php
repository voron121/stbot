<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/libs/logger.php';
require_once __DIR__ . '/../../core/coreTools.php';
require_once __DIR__ . '/../models/vkaccountModel.php';

class VKOauthController {

    public function __construct($action = null) {
        $this->user_id = (int) $_SESSION['uid'];
        $this->vkmodel = new VKAccount();
    }

    /**
     * Реализует запрос к центру авторизации VK 
     */
    public function setRequestToGetCode() {
        $reqused_uri = $this->getVKAuthUrl();
        header('location:' . $reqused_uri);
    }
    
    /**
     * Реализует запрос к API Vk для получения access_token
     * @param 	string 	$code 	- Код доступа  
     * @return 	array 	$token 	- Массив с токеном пользователя и ид пользователя в VK
     */
    public function getAccessToken($code) {
        $token = [];
        $reqused_uri = 'https://oauth.vk.com/access_token?client_id=' . VK_APP_ID . '&client_secret=' . VK_SECRET_TOKEN . '&redirect_uri=' . VK_REDIRECT_URI . '&scope=offline&code=' . $code;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type:multipart/form-data"
        ]);
        curl_setopt($ch, CURLOPT_URL, $reqused_uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $response = json_decode($output, true);
        if (isset($response['access_token']) && '' != $response['access_token'] && isset($response['user_id'])) {
            $token['access_token'] = $response['access_token'];
            $token['user_id'] = $response['user_id'];
            Logger::getInstance()->userActionWriteToLog('getVkAccessTokenSuccess', 'Успешно получен токкен');
        } elseif (isset($response['error'])) {
            Logger::getInstance()->userActionWriteToLog('getVkAccessTokenError', $response['error'] . ' ' . $response['error_description']);
        }
        return $token;
    }

    /**
     * Реализует запрос к API Vk для получения данных пользователя
     * @param 	string 	$token 		- Access token 
     * @return 	array 	$user_info 	- Массив с данными пользователя в VK
     */
    public function getVKUserInfo($token) {
        $user_info = null;
        $reqused_uri = 'https://api.vk.com/method/users.get?user_ids=' . $token['user_id'] . '&fields=photo_400&access_token=' . $token['access_token'] . '&v=5.102';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type:multipart/form-data"
        ]);
        curl_setopt($ch, CURLOPT_URL, $reqused_uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $response = json_decode($output, true);
        if (isset($response['response'][0])) {
            $user_info = $response['response'][0];
        }
        return $user_info;
    }
    
    /**
     * Метод проверит подключен ли аккаунт вк у другого пользователя
     * @param type $vk_user_id
     * @return boolean
     */
    protected function isVKAccountBusy($vk_user_id) {
        $is_busy = false;
        $count = $this->vkmodel->getVKAccountCountByVkUserId($vk_user_id, $this->user_id);
        if ($count > 0) {
            $is_busy = true;
        }
        return $is_busy;
    }
    
    /**
     * Сохранит аватар пользователя на сервере
     * @param string    $photo          - ссылка на аватар
     * @param int       $vk_user_id     - Ид пользователя вк
     */
    public function saveVKAccountPhoto($photo, $vk_user_id) {
        $user_dirs      = coreTools::getUserDirs($this->user_id);
        $user_photo_dir = __DIR__."/../..".$user_dirs["vk_accounts_path"];
        file_put_contents($user_photo_dir.$vk_user_id.".jpg", file_get_contents($photo));
    }
    
    /**
     * Реализует сохранение данных пользовательского аккаунта VK в БД
     * @param 	array 	$vkaccount 		- Массив с данными пользователького аккаунта
     */
    public function saveVKAccount($vkaccount) {
        if (true == $this->isVKAccountBusy($vkaccount['user_id'], $this->user_id)) {
            header('location: /cabinet/home.php?template=vkaccount&view=list&message=addVKAccountBusyError');
            exit;
        }
         
        $is_success = $this->vkmodel->saveVKAccount($vkaccount, $this->user_id);
        
        if (true == $is_success) {
            $this->saveVKAccountPhoto($vkaccount['photo_400'], $vkaccount['id']);
            Logger::getInstance()->userActionWriteToLog('saveVkAccessTokenSuccess', 'Успешно сохранен токкен для пользователя ' . $this->user_id);
            header('location: /cabinet/home.php?template=vkaccount&view=list&message=addVKAccountSuccess');
        } else {
            Logger::getInstance()->userActionWriteToLog('saveVkAccessTokenError', 'Не удалось сохранить токкен для пользователя ' . $this->user_id);
            header('location: /cabinet/home.php?template=vkaccount&view=list&message=addVKAccountError');
        }
    }

    /**
     * Конструирует ссылку для авторизации в VK
     * @return 	string - Ссылка для дальнейших запросов к центру авторизации VK
     */
    public function getVKAuthUrl() {
        return 'https://oauth.vk.com/authorize?client_id=' . VK_APP_ID . '&display=page&redirect_uri=' . VK_REDIRECT_URI . '&scope=offline&response_type=code&v=5.102';
    }

}

?>