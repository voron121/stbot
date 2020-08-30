<?php
class Logs {
    protected $db; 

    public function __construct($param = null) {
        $this->db = $this->getDBConnection();
    }

    protected function getDBConnection() {
        $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $db;
    }
    
    public function getLogsCount($user_login) {
        $query = 'SELECT COUNT(*)
               FROM action_log
               WHERE user_login = :user_login';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_login' => $user_login]);
        return $stmt->fetchColumn();
    }
    
    /**
     * @param   string  $user_login     - Логин пользователя в сервисе
     * @return obj                      - Логи запуска роботов для данного пользователя
     */
    public function getLogsList($user_login, $options) {
        $query = 'SELECT id,
                        bot,
                        event,
                        message,
                        `date`,
                        user_login
               FROM action_log
               WHERE user_login = :user_login
               ORDER BY `id` DESC';
        if (isset($options['page'])) {
            $query .= ' LIMIT '.$options['offset'].', '.ITEMS_ON_PAGE_LIMIT.' ';
        } else {
            $query .= ' LIMIT 0, '.ITEMS_ON_PAGE_LIMIT.' ';
        }
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':user_login' => $user_login]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
?>