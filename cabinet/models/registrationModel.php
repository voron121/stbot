<?php
class Registration {
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
     * Проверит логин в БД  
     * @param 	string 	$login 		- Логин из формы 
     * @return 	Obj 				- Логин пользователя или null
    */
    public function checkLogin($login) {
        $query = 'SELECT login FROM users  WHERE login = :login';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':login' => $login]);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Создаст нового пользователя в БД
     * @param 	string 	$login 						- Логин пользователя 
     * @param 	string 	$password 					- Пароль пользователя
     * @return 	int 	$this->db->lastInsertId()                       - Ид созданного пользователя
    */
    public function createNewUser($login, $password) {
        $query = 'INSERT INTO users
                        SET login               = :login,
                            password            = :password,
                            balance             = 0,
                            registration_date   = NOW(),
                            active              = "No",
                            subscription_id     = :subscription_id,
                        last_interaction	= NOW()';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':login'		=> $login,
            ':password'		=> $password,
            ':subscription_id'  => DEFAULT_SUBSCRIPTION
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Генерирует хеш для активации учетной записи пользователя на основе данных в БД
     * @param 	int 	$user_id 	- Ид пользователя
     * @return 	string 	$hash		- md5 хеш-сумма
    */
    public function getUserActivateHashById($user_id) {
        $hash = '';
        $query = 'SELECT login, password FROM users  WHERE id = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':id' => $user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (null != $user_data) {
            $hash = md5( $user_data['login'].$user_data['password'].substr($user_data['login'], 1).substr($user_data['password'], 3) );
        }
        return $hash;
    }

    /**
     * Активирует учетную запись пользователя
     * @param 	int 	$user_id 	- Ид пользователя
    */
    public function activateUser($user_id) {
        $query = 'UPDATE users SET active = "Yes" WHERE id = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':id' => $user_id]);
        return $stmt->rowCount();
    }
}