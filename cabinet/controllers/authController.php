<?php
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../models/authModel.php';
//-------------------------------------//
class authController {
    const FIELDS    = ['u_login', 'u_password']; // Массив с допустими полями из формы

    public function __construct() {
        $this->model = new Auth();
    }
    //-----------------------------------------------------//

    public function post($fields) {
        // Если пришло меньше или больше полей в масссиве - кикнем юзера (может быть взлом или тип того)
        if (count($fields) != count(self::FIELDS) || false == $this->checkMatchingFields($fields)) {
            header('location: /cabinet/index.php?message=UndefineError');
            exit;
        }
        // Если первичная проверка пройдена - пробуем создать юзера
        $fields 	= $this->prepareFields($fields);	// Вырежем теги HTML
        $user_info 	= $this->model->getUserInfo($fields['u_login']);	
        
        if (null == $user_info) {
            header('location: /cabinet/index.php?message=userNotFoundError');
            exit;
        } elseif ( md5($fields['u_password']) != $user_info->password ) {
            header('location: /cabinet/index.php?message=userPasswordError');
            exit;
        }
        if ("No" == $user_info->active) {
            header('location: /cabinet/index.php?message=userActivateError');
            exit;
        }
        session_start();
        $_SESSION['uid'] = $user_info->id;
        Logger::getInstance()->userActionWriteToLog('authUser', 'Пользователь с логином '.$user_info->login. ' авторизировался в сервисе');
        header('location: /cabinet/home.php?template=dashboard&view=list');
        exit;
    }

    /**
     * Вырежет теги в данных их форм
     * и на предмет того что урл соответсвует  урл телеграм канала
     * @param 	array 	$fields 	- Массив с данными, введенных пользователем 
     * @return 	array 	$fields 	- Массив с подготовленными данными, введенных пользователем 
    */
    protected function prepareFields($fields) {
        foreach ($fields as $key => &$field) {
            strip_tags( trim($field) );
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

}