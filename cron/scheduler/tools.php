<?php

include(__DIR__ . '/../../config.php');
require_once __DIR__.'/../../cabinet/models/schedulerModel.php';
require_once __DIR__.'/../../core/libs/logger.php';

// VK parser  bot helper
class SchedulerTools {
 
    public function __construct($param = null) {
        $this->db               = $this->getDBConnection();
        $this->schedulerModel 	= new SchedulerModel();
    }

    /**
     * Коннектор к БД
     */
    protected function getDBConnection() {
        $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $db;
    }

    /**
     * Получает информацию для импорта
     *
     * @param 	string 	$login 	- Логин пользователя в сервисе
     * @return 	obj             - Объект с параметрами импорта
     */
    public function getPostByLogin($login) {
        $query = 'SELECT scheduler.id,
                         scheduler.item_action,
                         users.id AS user_id,                 
                         telegram_chanels.telegram_chat_id,
                         posts.id as post_id,
                         posts.notification,
                         posts.type,
                         posts.text,
                         posts.buttons,
                         posts.message_id,
                         scheduler.date,
                         scheduler.time,
                         scheduler.id
                    FROM scheduler
                           JOIN users               ON users.id = scheduler.user_id
                           JOIN telegram_chanels    ON scheduler.channel_id = telegram_chanels.id
                           JOIN posts               ON posts.id = scheduler.item_id	
                    WHERE users.login                   = :login
                            AND scheduler.status 	= "ACTIVE"
                            AND scheduler.item_type 	= "POST"
                            AND scheduler.date          = CURDATE()
                            AND scheduler.time BETWEEN (DATE_SUB(CURTIME(), INTERVAL 1 MINUTE)) AND (DATE_SUB(CURTIME(), INTERVAL 0 MINUTE))
                    GROUP BY posts.id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':login' => $login]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Получает информацию для импорта опросов 
     *
     * @param 	string 	$login 	- Логин пользователя в сервисе
     * @return 	obj             - Объект с параметрами импорта
     */
    public function getPollByLogin($login) {
        $query = 'SELECT scheduler.id,
                         scheduler.item_action,
                         users.id AS user_id,                 
                         telegram_chanels.telegram_chat_id,
                         polls.id AS poll_id,
                         polls.notification,
                         polls.message_id,
                         polls.question,
                         polls.answers,
                         scheduler.date,
                         scheduler.time,
                         scheduler.id
                    FROM scheduler
                           JOIN users               ON users.id = scheduler.user_id
                           JOIN telegram_chanels    ON scheduler.channel_id = telegram_chanels.id
                           JOIN polls               ON polls.id = scheduler.item_id	
                    WHERE users.login                   = :login
                            AND scheduler.status 	= "ACTIVE"
                            AND scheduler.item_type 	= "POLL"
                            AND scheduler.date          = CURDATE()
                            AND scheduler.time BETWEEN (DATE_SUB(CURTIME(), INTERVAL 1 MINUTE)) AND (DATE_SUB(CURTIME(), INTERVAL 0 MINUTE))
                    GROUP BY polls.id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':login' => $login]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Метод  обновит опроса после публикации в телеграм
     * @param 	int 	$post_id        - Ид публикации
     * @param 	int 	$message_id 	- Ид публикации в телеграм
     * @param 	int 	$chat_id 	- Ид чата с публикацией в телеграм
     * @return 	int 	lastInsertId 	- Ид обновленной записи
    */
    public function updatePolltAfterPublish($post_id, $message_id, $chat_id) {
        $query = 'UPDATE polls
                        SET message_id          = :message_id,
                                chat_id 	= :chat_id,
                                status          = "PUBLISHED",
                                published	= NOW()
                        WHERE id    = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
                ':id'           => $post_id, 
                ':message_id'   => $message_id,
                ':chat_id'      => $chat_id
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Метод  обновит статус опроса по ид
     * @param 	int 	$post_id 	- Ид записи
     * @param 	string 	$status 	- Желаемый статус
     * @return 	int 	lastInsertId 	- Ид обновленной записи
    */
    public  function updatePollStatus($post_id, $status) {
        $query = 'UPDATE polls SET status = :status WHERE id = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
                ':id' 		=> $post_id, 
                ':status' 	=> $status
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Удалит опрос в БД
     * @param 	int 	$poll_id 		- Ид канала
    */
    public function deletePoll($poll_id) {
        $query = 'DELETE FROM polls WHERE id = :poll_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':poll_id' => $poll_id]);
    }
    
    /**
     * 
     * @param type $post_id
     * @param type $telegram_message_id
     * @param type $status
     * @return type
     */
    public function updatePostStatus($post_id, $telegram_message_id, $chat_id, $status) {
        $query = 'UPDATE posts
                    SET status      = :status,
                        message_id  = :message_id,
                        chat_id     = :chat_id
                    WHERE id        = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
                ':id' 		=> $post_id, 
                ':message_id' 	=> $telegram_message_id,
                ':chat_id' 	=> $chat_id,
                ':status' 	=> $status
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Удалит публикацию из БД
     * @param int   $post_id - Ид публикации
     */
    public function deletePost($post_id) {
        $query = 'DELETE FROM posts  WHERE id = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':id'=> $post_id]);
    }
    
    /**
     * Обновит статус задачи
     * @param 	int 	$id 	- Ид задачи
     * @return 	string 	$status - Желаемый статус
    */
   public function updateTaskStatus($id, $status) {
        return $this->schedulerModel->updateTaskStatus($id, $status);
   }
   
   /**
    * Установит статус FAIL для пропущенных заданий
    * @param type $login
    */
   public function setFailStatusSkippedItemsByLogin($login) {
       $query = 'UPDATE scheduler
                    JOIN users ON users.id = scheduler.user_id
                    SET scheduler.`status` = "FAIL"
                WHERE users.login = :login
                    AND scheduler.status 	NOT IN("DONE", "FAIL")
                    AND ((scheduler.date = CURDATE() AND CURTIME() > scheduler.time) OR  (scheduler.date < CURDATE()))';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':login' => $login]);
        return $stmt->rowCount();
   }
   
   public function deletePostFromChanel() {
       
   }

}

?>