<?php
class User {
    protected $db;
    protected $user = [];

    public function __construct($param = null) {
        $this->db = $this->getDBConnection();
        if (null != $param) {
            if (is_string($param)) {
                $this->user = $this->getUserByLogin($param);
            } else {
                $this->user = $this->getUserById($param);
            }
            foreach ($this->user as $key => $value) {
                $this->user[$key] = $value;
            }
        }
    }

    /**
     * Определение магического метода __set()
     * @param 	string 	$name               - Имя свойства объекта
     * @param 	string 	$value              - Значение свойства объекта
     * @return 	string 	$this->user[$name]  - Новое значение свойства объекта
    */
    public function __set($name, $value) {
        $this->user[$name] = $value;
    }

    /**
     * Определение магического метода __get()
     * @param 	string 	$name                   - Имя свойства объекта
     * @return 	string 	$this->user[$name] 	- Значение свойства объекта
    */
    public function __get($name) {	
        if (array_key_exists($name, $this->user)) {
            return $this->user[$name];
        }
        return null;
    }

    /**
     * Метод расписание в БД
     * @return 	int 	lastInsertId 	- Ид добавленной записи
    */
    protected function getDBConnection() {
        $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $db;
    }

    /**
     * Метод расписание в БД
     * @return 	int 	lastInsertId 	- Ид добавленной записи
    */
    protected function getUserById($id) {
        $query = 'SELECT users.id,
                        users.login,
                        users.password,
                        users.balance,
                        users.active,
                        users.registration_date,
                        users.last_interaction,
                        subscription_plans.subscription_id,
                        subscription_plans.name,
                        subscription_plans.description,
                        subscription_plans.cost,
                        subscription_plans.vkaccounts_count,
                        subscription_plans.channels_count,
                        subscription_plans.vk_publics_count,
                        subscription_plans.vk_rule_count,
                        subscription_plans.rss_count,
                        subscription_plans.rss_rule_count,
                        subscription_plans.sheduler_task_count,
                        subscription_plans.disc_space
                    FROM users
                        JOIN subscription_plans USING(subscription_id)
                   WHERE id = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    /**
     * Метод расписание в БД
     * @return 	int 	lastInsertId 	- Ид добавленной записи
    */
    protected function getUserByLogin($login) {
        $query = 'SELECT users.id,
                        users.login,
                        users.password,
                        users.balance,
                        users.active,
                        users.registration_date,
                        users.last_interaction,
                        subscription_plans.id AS subscription_id,
                        subscription_plans.subscription_name,
                        subscription_plans.subscription_description,
                        subscription_plans.cost,
                        subscription_plans.vkaccounts_count,
                        subscription_plans.channels_count,
                        subscription_plans.vk_publics_count,
                        subscription_plans.vk_rule_count,
                        subscription_plans.rss_count,
                        subscription_plans.rss_rule_count,
                        subscription_plans.sheduler_task_count,
                        subscription_plans.disc_space 
                    FROM users
                        JOIN subscription_plans ON subscription_plans.id = users.subscription_id
                   WHERE login = :login';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch();
        return $user;
    }

    /**
     * Метод сохраняет изменения в таблице пользователя
     */
    public function save() {
        $db = $this->getDBConnection();
        $fields_sql = 	'`login`                = ' . $db->quote($this->login) .	
                        ',`password`            = ' . $db->quote($this->password) .
                        ',`balance`             = ' . $db->quote($this->balance) .
                        ',`active`              = ' . $db->quote($this->active) .
                        ',`registration_date` 	= ' . $db->quote($this->registration_date) .
                        ',`last_interaction` 	= ' . $db->quote($this->last_interaction);
        if ($this->id) {
            $query = 'UPDATE users SET ' . $fields_sql . ' WHERE id = :id';
            echo $query;
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $this->id]);
        }

    }

}
?>