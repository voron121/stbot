<?php

class SchedulerModel {

    protected $db;

    public function __construct() {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    /**
     * Метод расписание в БД
     * @return 	int 	lastInsertId 	- Ид добавленной записи
     */
    public function addTask($user_id, $channel_id, $item_id, $type, $date, $time, $action, $telegram_chat_id) {
        $query = 'INSERT INTO scheduler
                        SET user_id             = :user_id,
                            item_id             = :item_id,
                            channel_id          = :channel_id,
                            item_type           = :item_type,
                            item_action         = :item_action,
                            telegram_chat_id    = :telegram_chat_id,
                            date                = :date,
                            time                = :time';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':user_id'          => (int) $user_id,
            ':item_id'          => (int) $item_id,
            ':channel_id'       => (int) $channel_id,
            ':item_type'        => $type,
            ':item_action'      => $action,
            ':telegram_chat_id' => $telegram_chat_id,
            ':date'             => $date,
            ':time'             => $time
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getSchedulerTaskCount($user_id) {
        $query = 'SELECT COUNT(*)
               FROM scheduler
               WHERE user_id = :user_id
                AND date = DATE_FORMAT(NOW(), "%Y-%m-%d")';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getSchedulerCount($user_id) {
        $query = 'SELECT COUNT(*)
                        FROM scheduler
                                JOIN telegram_chanels ON telegram_chanels.id = scheduler.channel_id
                        WHERE scheduler.user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    /**
     * Вернет список расписаний задач для пользователя
     *
     * @param 	int $user_id 	- Ид пользователя
     * @return  obj 			- Объекст со списком заданий пользователя
     */
    public function getUserSchedulerList($user_id, $options) {
        $query = 'SELECT scheduler.id,
                                scheduler.user_id, 
                                scheduler.item_id,
                                scheduler.channel_id,
                                scheduler.item_type,
                                scheduler.item_action,
                                scheduler.date,
                                scheduler.time,
                                scheduler.status,
                                telegram_chanels.url as chanel_url,
                                telegram_chanels.channel_title as channel_title
                       FROM scheduler
                            JOIN telegram_chanels ON telegram_chanels.id = scheduler.channel_id
                       WHERE scheduler.user_id = :user_id';
        $query .= ' ORDER BY Id DESC ';
        if (isset($options['page'])) {
            $query .= ' LIMIT ' . $options['offset'] . ', ' . ITEMS_ON_PAGE_LIMIT . ' ';
        } else {
            $query .= ' LIMIT 0, ' . ITEMS_ON_PAGE_LIMIT . ' ';
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Метод  обновит статус задачи
     * @param 	int 	$id 			- Ид записи
     * @param 	string 	$status 		- Желаемый статус
     * @return 	int 	lastInsertId 	- Ид обновленной записи
     */
    public function updateTaskStatus($post_id, $status) {
        $query = 'UPDATE scheduler
                        SET status 	= :status
                        WHERE id 	= :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':id' => $post_id,
            ':status' => $status
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Удалит задачу из БД сервиса
     * @param 	int 	$id 		- Ид задачи
     */
    public function deleteTask($id) {
        $query = 'DELETE FROM scheduler WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
    }

}
