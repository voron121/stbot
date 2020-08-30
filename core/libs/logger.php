<?php
// Вспомогательный класс для логирования событий
class Logger {
	// Singleton
    
    private static 	$instance;
    private 		$db;
    private 		$user_id;

    public static function getInstance(): Logger {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function __construct() {
    	$this->db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->user_id = (isset($_SESSION['uid'])) ? (int)$_SESSION['uid'] : null;
    }
    private function __clone() {}
    private function __wakeup() {}

    //-----------------------------------------------------------------------------------------------------------------//

    /**
     * Запишет событие в лог. Метод логирует автоматически события
     *
     * @param 	string 	$bot 						- Название робота
     * @param 	string 	$event 						- Название события
     * @param 	string 	$message 					- Описание события
     * @param 	string 	$user_login 				- Логин пользователя в сервисе для которого запущен робот
     * @return 	int 	$this->db->lastInsertId() 	- Ид созданной записи
    */
	public function robotActionWriteToLog($bot, $event, $message, $user_login = null) {
		$query = 'INSERT INTO action_log
						  SET bot		    = :bot,
				  			  event		    = :event,
					          message	    = :message,
					          user_login    = :user_login,
					    	  date 		    = NOW()';
		$stmt  = $this->db->prepare($query);
		$stmt->execute([
			':bot'		    => $bot,
			':event'	    => $event,
            ':message' 	    => $message,
            ':user_login' 	=> $user_login
		]);
		return $this->db->lastInsertId();
	}

	/**
	 * Запишет событие пользователя в лог. Метод логирует события пользователя
	 *
	 * @param 	string 	$event 						- Название события
	 * @param 	string 	$message 					- Описание события
	 * @return 	int 	$this->db->lastInsertId() 	- Ид созданной записи
	*/
	public function userActionWriteToLog($event, $message, $user_id = null) {
		$query = 'INSERT INTO user_action_log
						  SET user_id   = :user_id,
				  			  event		= :event,
					          message	= :message,
					    	  date 		= NOW()';
		$stmt  = $this->db->prepare($query);
		$stmt->execute([
			':user_id'	=> (null != $this->user_id) ? $this->user_id : $user_id,
			':event'	=> $event,
			':message' 	=> $message
		]);
		return $this->db->lastInsertId();
	}
}
?>