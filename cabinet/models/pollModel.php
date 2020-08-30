<?php
class PollModel {
    protected $db;

    public function __construct() {
        $this->db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    /**
     * Метод добавит опрос в БД
     * @param 	int 	$channel_id 	- Ид канала 
     * @param 	int 	$user_id 	- Ид пользователя
     * @param 	string 	$question 	- Вопрос (тайтл опроса)
     * @param 	string 	$answers 	- Json с ответами
     * @param 	string 	$notification 	- Отправлять ли уведомления в чат
     * @return 	int 	lastInsertId 	- Ид добавленной записи
    */
    public function addPoll($channel_id, $user_id, $question, $answers, $notification) {
            $query = 'INSERT INTO polls
                            SET channel_id  = :channel_id,
                            user_id         = :user_id,
                           `question`       = :question,
                            answers         = :answers,
                            notification    = :notification,
                            created         = NOW(),
                            status          = "ACTIVE"';
            $stmt  = $this->db->prepare($query);
            $stmt->execute([
                    ':channel_id'   => (int)$channel_id,
                    ':user_id'      => (int)$user_id,
                    ':question'     => $question,
                    ':answers'      => $answers,
                    ':notification' => $notification
            ]);
            return $this->db->lastInsertId();				
    }

    /**
     * Метод  получит список всех добавленных в сервис опросов пользователя
     * @param 	int 	$user_id    - Ид пользователя
     * @return 	obj                 - Объек со списком опросов пользователя в БД
    */
    public function getUserPollsList($user_id) {
        $query = 'SELECT polls.id,
                        polls.user_id, 
                        polls.channel_id,
                        polls.status,
                        polls.question,
                        polls.answers,
                        polls.notification,
                        polls.created,
                        polls.published,
                        telegram_chanels.id AS channel_id,
                        telegram_chanels.url AS channel_url,
                        telegram_chanels.channel_title AS channel_title,
                        (SELECT scheduler.item_id 
                            FROM scheduler 
                            WHERE scheduler.item_id = polls.id 
                                AND scheduler.item_type = "POLL"
                                AND scheduler.status = "ACTIVE"
                       ) AS is_schedule
               FROM polls
                       JOIN telegram_chanels ON telegram_chanels.id = polls.channel_id
               WHERE polls.user_id = :user_id
               ORDER BY Id DESC';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
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
     * Метод  получит запись публикации по ид
     * @param 	int 	$user_id    - Ид пользователя
     * @return 	obj                 - Объек со списком каналов пользователя в БД
    */
    public function getUserPollById($post_id) {
        $query = 'SELECT polls.id,
                        polls.user_id, 
                        polls.channel_id,
                        polls.chat_id,
                        polls.message_id,
                        polls.status,
                        polls.question,
                        polls.answers,
                        polls.notification,
                        polls.created,
                        polls.published,
                        (SELECT scheduler.item_id 
                            FROM scheduler 
                            WHERE scheduler.item_id = polls.id 
                                AND scheduler.item_type = "POLL"
                                AND scheduler.status = "ACTIVE"
                       ) AS is_schedule
               FROM polls
               WHERE id = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':id' => $post_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Метод  получит канал пользователя по урл канала
     * @param 	string 	$url 	- ссылка на канал
     * @return 	obj             - Объек
    */
    public function getChannelByUrl($url) {
        $query = 'SELECT id,
                        user_id, 
                        url,
                        status,
                        comment
                    FROM telegram_chanels
                    WHERE url = :url';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':url' => $url]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Удалит канал пользователя в БД сервиса
     * @param 	int 	$chanel_id 		- Ид канала
    */
    public function deletePoll($post_id, $user_id) {
        $query = 'DELETE FROM polls WHERE id = :post_id AND user_id = :user_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':post_id' => $post_id, ':user_id' => $user_id]);
    }

}