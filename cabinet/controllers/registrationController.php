<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/alertMessageController.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../models/registrationModel.php';
require_once __DIR__.'/../models/registrationModel.php';
require_once __DIR__.'/../models/authModel.php';
 
//-------------------------------------//

class RegistrationController {
    const FIELDS 		= ['u_login', 'u_password', 'u_password_retry']; // Массив с допустими полями из формы
    const RESERVED_LOGINS 	= ['admin'];
    const MIN_LENGHT		= 6;
    const MAX_LENGHT		= 120;

    protected $model;

    public function __construct() {
        $this->model        = new Registration();
        $this->auth_model   = new Auth();
    }

    //-----------------------------------------------------//

    public function post($fields) {
        // Если пришло меньше или больше полей в масссиве - кикнем юзера (может быть взлом или тип того)
        if (count($fields) != count(self::FIELDS) || false == $this->checkMatchingFields($fields) || (isset($fields['u_login']) &&  in_array($fields['u_login'], SELF::RESERVED_LOGINS) )) {
            header('location: /cabinet/index.php?template=registration&message=registrationUndefineError');
            exit;
        }
        // Если первичная проверка пройдена - пробуем создать юзера
        $fields         = $this->prepareFields($fields);	// Вырежем теги HTML
        $check_fields 	= $this->validateFields($fields);	// Валидируем поля

        if ( 'error' == $check_fields['status'] ) {
            header('location: /cabinet/index.php?template=registration&message='.$check_fields['code']);
        } else {
            $user_id = $this->createNewUser($fields);
            $this->sendUserEmail($fields, $user_id);
            Logger::getInstance()->userActionWriteToLog('registrationNewUser', 'Зарегистрирован новый пользователь. Логин пользователя: '.$fields['u_login'] .". Ид пользователя: ". $user_id);
            header('location: /cabinet/index.php?template=registration&message=registrationSuccess');
        }
    }
    
    /**
     * Обработчик аякс регистрации
     * @param type $fields
     * @return type
     */
    public function ajaxRegistration($fields) {
        $message = ["status" => "success", "message" => ""];
        //print_r($fields);
        // Если пришло меньше или больше полей в масссиве - кикнем юзера (может быть взлом или тип того)
        if (count($fields) != count(self::FIELDS) 
            || false == $this->checkMatchingFields($fields) 
            || (isset($fields['u_login']) &&  in_array($fields['u_login'], SELF::RESERVED_LOGINS) )
        ) {
            $message = ["status" => "error", "message" => "Произошла ошибка"]; 
        } else {
            // Если первичная проверка пройдена - пробуем создать юзера
            $fields         = $this->prepareFields($fields);	// Вырежем теги HTML
            $check_fields   = $this->validateFields($fields);	// Валидируем поля

            if ( 'error' == $check_fields['status'] ) {
                $messageController  = new alertMessageController();
                $messagesArray      = $messageController->messages;
                $message = [
                    "status"    => "error", 
                    "message"   => $messagesArray[$check_fields['code']]['message']
                ];  
            } else {
                $user_id = $this->createNewUser($fields);
                $this->sendUserEmail($fields, $user_id);
                Logger::getInstance()->userActionWriteToLog('registrationNewUser', 'Зарегистрирован новый пользователь. Логин пользователя: '.$fields['u_login'] .". Ид пользователя: ". $user_id, $user_id);
                $message = [
                    "status"    => "success", 
                    "message"   => "Пожалуйста, пройдите по ссылке в письме, для активации учетной записи. Письмо мы вам выслали на email ".$fields['u_login']
                ]; 
            }
        } 
        return json_encode($message);
    }

    /**
     * Вырежет теги в данных их форм
     * и на предмет того что урл соответсвует  урл телеграм канала
     * @param 	array 	$fields 	- Массив с данными, введенных пользователем 
     * @return 	array 	$fields 	- Массив с подготовленными данными, введенных пользователем 
    */
    protected function prepareFields($fields) {
        foreach ($fields as $key => &$field) {
            strip_tags($field);
        }
        return $fields;
    }

    /**
     * Проверит есть ли все дозволеные поля в передданном массиве
     * @param 	array 	$fields 	- Массив с данными, введенных пользователем 
     * @return 	bool 	$status 	- TRUE | FALSE в зависимости есть все поля или нет
    */
    protected function checkMatchingFields($fields) {
        $status = true;
        foreach ($fields as $field_name => $field_value) {
            if ( !in_array($field_name, self::FIELDS ) ) {
                $status = false;
                break;
            }
        }
        return $status;
    }

    /**
     * Проверит переданный массив с полями пользователя из формы
     * на соответсвия требованиям.  
     * и на предмет того что урл соответсвует  урл телеграм канала
     * @param 	array 	$fields 	- Массив с данными, введенных пользователем 
     * @return 	array 	$message 	- Массив с данными валидации		 	- 
    */
    protected function validateFields($fields) {
        $message = ['status' => 'ok', 'code' => 'success'];

        if ($fields['u_password'] != $fields['u_password_retry']) {
            $message['status'] 	= 'error'; 
            $message['code'] 	= 'passwordsNotMatchingError';
        } elseif (true == $this->isIssetLogin($fields['u_login'])) {
            $message['status'] 	= 'error'; 
            $message['code'] 	= 'loginIsBusyError';
        } elseif ( strlen($fields['u_password']) > self::MAX_LENGHT) {
            $message['status'] 	= 'error'; 
            $message['code'] 	= 'passwordLongError';
        } elseif (strlen($fields['u_password']) < self::MIN_LENGHT) {
            $message['status'] 	= 'error'; 
            $message['code'] 	= 'passwordShortError';
        } elseif ( false == preg_match('/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}[0-9А-Яа-я]{1}))@([-A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u', $fields['u_login'])) {
            $message['status'] 	= 'error'; 
            $message['code'] 	= 'invalidLoginError';
        }

        return $message;
    }

    /**
     * Активирует пользователя по ссылке с хешом  
     * @param 	int 	$user_id 	- Ид пользователя
     * @param 	string 	$hash 		- Хеш
    */
    public function activateUserByHash($user_id, $hash) {
        if ($hash != $this->model->getUserActivateHashById($user_id) ) {
            header('location: /cabinet/index.php?message=registrationCompleteError');
            exit;
        } else {
            $is_activated = $this->model->activateUser($user_id);
            if (1 == $is_activated) {
                $user_info = $this->auth_model->getUserInfoById($user_id);
                session_start();
                $_SESSION['uid'] = $user_info->id;
                Logger::getInstance()->userActionWriteToLog('authUser', 'Пользователь с логином '.$user_info->login. ' активирован в сервисе');
                Logger::getInstance()->userActionWriteToLog('authUser', 'Пользователь с логином '.$user_info->login. ' авторизировался в сервисе');
                header('location: /cabinet/home.php?template=dashboard&view=list'); 
            } else {
                header('location: /cabinet/index.php?message=registrationCompleteError');
                exit;
            }
        }
    }

    /**
     * Проверит свободен ли логин  
     * @param 	string 	$login 		- Логин из формы 
     * @return 	bool 	is_isset	- True | False в зависимости от того свободен логин или нет
    */
    protected function isIssetLogin($login) {
        $is_isset = false;
        if (false != $this->model->checkLogin(strip_tags($login))) {
            $is_isset = true;
        }
        return $is_isset;
    }

    /**
     * Создаст структуру папок для хранения данных пользователя
    */
    protected function createUserDir($user_id) { 
        if (true == mkdir(__DIR__."/../../users_data/".$user_id, 0755)) {
            file_put_contents(__DIR__."/../../users_data/".$user_id."/index.html", " ");
        } else {
            throw new Exception("fail create user dir");
        }
        if (true == mkdir(__DIR__."/../../users_data/".$user_id."/user_xml/", 0755)) {
            file_put_contents(__DIR__."/../../users_data/".$user_id."/user_xml/index.html", " ");
        } else {
            throw new Exception("fail create user_xml dir");
        }
        if (true == mkdir(__DIR__."/../../users_data/".$user_id."/user_images/", 0755)) {
            file_put_contents(__DIR__."/../../users_data/".$user_id."/user_images/index.html", " ");
        } else {
            throw new Exception("fail create user_images dir");
        }
        if (true == mkdir(__DIR__."/../../users_data/".$user_id."/user_images/vk_accounts", 0755)) {
            file_put_contents(__DIR__."/../../users_data/".$user_id."/user_images/vk_accounts/index.html", " ");
        } else {
            throw new Exception("fail create user_images dir");
        }
        if (true == mkdir(__DIR__."/../../users_data/".$user_id."/user_images/vk_public_images/", 0755)) {
            file_put_contents(__DIR__."/../../users_data/".$user_id."/user_images/vk_public_images/index.html", " ");
        } else {
            throw new Exception("fail create user_images/vk_public_images dir");
        }
        if (true == mkdir(__DIR__."/../../users_data/".$user_id."/user_images/telegram_channels_images/", 0755)) {
            file_put_contents(__DIR__."/../../users_data/".$user_id."/user_images/telegram_channels_images/index.html", " ");
        } else {
            throw new Exception("fail create user_images/telegram_channels_images dir");
        }
        if (true == mkdir(__DIR__."/../../users_data/".$user_id."/user_attachments/", 0755)) {
            file_put_contents(__DIR__."/../../users_data/".$user_id."/user_attachments/index.html", " ");
        } else {
            throw new Exception("fail create user_attachments dir");
        }
        if (true == mkdir(__DIR__."/../../users_data/".$user_id."/user_attachments/post/", 0755)) {
            file_put_contents(__DIR__."/../../users_data/".$user_id."/user_attachments/post/index.html", " ");
        } else {
            throw new Exception("fail create user_attachments dir");
        }
        if (true == mkdir(__DIR__."/../../users_data/".$user_id."/user_attachments/rss_import_rules/", 0755)) {
            file_put_contents(__DIR__."/../../users_data/".$user_id."/user_attachments/rss_import_rules/index.html", " ");
        } else {
            throw new Exception("fail create user_attachments/rss_import_rules/ dir");
        }
        if (true == mkdir(__DIR__."/../../users_data/".$user_id."/user_attachments/vk_import_rules/", 0755)) {
            file_put_contents(__DIR__."/../../users_data/".$user_id."/user_attachments/vk_import_rules/index.html", " ");
        } else {
            throw new Exception("fail create user_attachments/vk_import_rules dir");
        }
    }

    /**
     * Создаст нового пользователя в БД
     * @param 	array 	$fields 	- Массив с данными, введенных пользователем 
    */
    protected function createNewUser($fields) {
        try {
            $user_id = $this->model->createNewUser( strip_tags($fields['u_login']), md5($fields['u_password']) );
            if (null != $user_id) {
                $this->createUserDir($user_id);
            }
        } catch (Exception $e) {
            // DO something
            //echo $e->getMessage();
        }
        return $user_id;
    }

    //----------------------------------------------------------------------//

    /**
     * Генерирует хеш для активации учетной записи пользователя
     * @param 	array 	$fields 	- Массив с данными, введенных пользователем 
     * @return 	string 	md5()		- md5 хеш-сумма
    */
    protected function getActivateHash($fields) {
        return md5( $fields['u_login'].md5($fields['u_password']).substr($fields['u_login'], 1).substr(md5($fields['u_password']), 3) );
    }

    /**
     * Отправит юзеру email с данными о регистрации
     * @param 	array 	$fields 	- Массив с данными, введенных пользователем 
    */
    protected function sendUserEmail($fields, $user_id) {
        $url 	  = AUTH_HOST.'/cabinet/helpers/registration.php?action=activate_user&uid='.$user_id.'&hash='.$this->getActivateHash($fields);

        $headers  = "From: " . strip_tags($fields['u_login']) . "\r\n";
        $headers .= "Reply-To: ". strip_tags($fields['u_login']) . "\r\n";
        $headers .= "CC: no-reply@example.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $message  = 'Вы зарегистрировались на сервисе! <br>';
        $message .= 'Для подтверждения регистрации пройлите по ссылке ';
        $message .= '<a href="'.$url.'">Подвердить регистрацию </a> ';
        mail($fields['u_login'], 'Регистрация в сервсие', $message, $headers);
    }
}