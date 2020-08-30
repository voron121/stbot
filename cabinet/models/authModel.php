<?php
class Auth {
    protected $db;

    public function __construct() {
        $this->db = $this->getDBConnection();
    }

    protected function getDBConnection() {
        $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $db;	
    }

    /**
     * Проверить данные пользователя в БД 
     * @param 	string 	$login 		- Логин из формы 
     * @return 	Obj 				- Ид и логин пользователя или null
    */
    public function getUserInfo($login) {
        $query = 'SELECT id,
                        login,
                        password,
                        active
                FROM users 
               WHERE login = :login';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':login' => $login]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Проверить данные пользователя в БД 
     * @param 	string 	$login 		- Логин из формы 
     * @return 	Obj 				- Ид и логин пользователя или null
    */
    public function getUserInfoById($id) {
        $query = 'SELECT id,
                        login,
                        password,
                        active
                FROM users 
               WHERE id = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Обновит дату последней авторизации пользователя в сервисе 
     * @param 	int 	$user_id 		- Ид пользователя 
    */
    public function updateInteractionTime($user_id) {
        $query = 'UPDATE users SET last_interaction = NOW() WHERE id = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':id' => $user_id]);
    }
}