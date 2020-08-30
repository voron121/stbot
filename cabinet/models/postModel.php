<?php

class PostModel {

    protected $db;

    public function __construct() {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    /**
     * Метод добавит канал в БД
     * @param 	string 	$url 			- Ссылка на канал с https://t.me/
     * @param 	int 	$user_id 		- Ид пользователя
     * @param 	string 	$comment 		- Примечание к каналу
     * @param 	string 	$notification           - Отправлять ли уведомления в чат
     * @return 	int 	lastInsertId            - Ид добавленной записи
     */
    public function addPost($channel_id, $user_id, $text, $buttons, $title, $type, $notification) {
        $query = 'INSERT INTO posts
                        SET channel_id          = :channel_id,
                            user_id		= :user_id,
                            `text` 		= :text,
                            buttons 		= :buttons,
                            title 		= :title,
                            status		= "ACTIVE",
                            type                = :type,
                            notification 	= :notification,
                            created		= NOW()';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':channel_id'   => (int) $channel_id,
            ':user_id'      => (int) $user_id,
            ':text'         => $text,
            ':buttons'      => $buttons,
            ':title'        => $title,
            ':type'         => $type,
            ':notification' => $notification
                
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Обновит запись в БД
     * @param int       $post_id        - Ид публикации
     * @param int       $channel_id     - Ид канала
     * @param string    $title          - Заголовок
     * @param string    $text           - Текст публикации
     * @param array     $files          - Вложения
     * @param string    $notification   - Уведомлять ли пользователя (Yes|No)
     */
    public function updatePost($post_id, $channel_id, $text, $buttons, $title, $type, $notification) {
        $query = 'UPDATE posts
                    SET channel_id 	= :channel_id,
                        `text`          = :text,
                        buttons         = :buttons,
                        title           = :title,
                        type            = :type,
                        notification 	= :notification
                    WHERE id            = :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':channel_id'   => $channel_id,
            ':text'         => $text,
            ':title'        => $title,
            ':buttons'      => $buttons,
            ':type'         => $type,
            ':notification' => $notification,
            ':id'           => $post_id,
        ]);
        return $stmt->rowCount();
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getPostCount($user_id) {
        $query = 'SELECT COUNT(*)
                    FROM posts
                    JOIN telegram_chanels ON telegram_chanels.id = posts.channel_id
                    WHERE posts.user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Метод  получит список всех добавленных в сервис каналов пользователя
     * @param 	int 	$user_id    - Ид пользователя
     * @return 	obj                 - Объек со списком каналов пользователя в БД
     */
    public function getUserPostsList($user_id, $options = []) {
        $query = 'SELECT posts.id,
                         posts.user_id, 
                         posts.status,
                         posts.type,
                         posts.title,
                         posts.text,
                         posts.file,
                         posts.created,
                         posts.published,
                         telegram_chanels.id as channel_id,
                         telegram_chanels.url AS channel_url,
                         telegram_chanels.channel_title AS channel_title,
                         (SELECT scheduler.item_id 
                               FROM scheduler 
                               WHERE scheduler.item_id = posts.id 
                                       AND scheduler.item_type = "POST"
                                       LIMIT 0, 1
                        ) AS is_schedule					
                        FROM posts
                        JOIN telegram_chanels ON telegram_chanels.id = posts.channel_id
                WHERE posts.user_id = :user_id';
        $query .= ' ORDER BY Id DESC ';
        if (isset($options['page'])) {
            $query .= ' LIMIT '.$options['offset'].', '.ITEMS_ON_PAGE_LIMIT.' ';
        } else {
            $query .= ' LIMIT 0, '.ITEMS_ON_PAGE_LIMIT.' ';
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Метод  обновит статус публикации по ид
     * @param 	int 	$post_id 		- Ид записи
     * @param 	string 	$status 		- Желаемый статус
     * @return 	int 	lastInsertId            - Ид обновленной записи
     */
    public function updatePostStatus($post_id, $status) {
        $query = 'UPDATE posts
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
     * Метод  обновит запись после публикации в телеграм
     * @param 	int 	$post_id 		- Ид публикации
     * @param 	int 	$message_id             - Ид публикации в телеграм
     * @param 	int 	$chat_id 		- Ид чата с публикацией в телеграм
     * @return 	int 	lastInsertId            - Ид обновленной записи
     */
    public function updatePostAfterPublish($post_id, $message_id, $chat_id) {
        $query = 'UPDATE posts
                    SET message_id 	= :message_id,
                            chat_id 	= :chat_id,
                            status 	= "PUBLISHED",
                            published	= NOW()
                    WHERE id 		= :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':id' => $post_id,
            ':message_id' => $message_id,
            ':chat_id' => $chat_id
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Метод  получит запись публикации по ид
     * @param 	int 	$user_id - Ид пользователя
     * @return 	obj 		 - Объек со списком каналов пользователя в БД
     */
    public function getUserPostById($post_id) {
        $query = 'SELECT posts.id,
                        posts.user_id,
                        posts.channel_id,
                        posts.chat_id,
                        posts.message_id,
                        posts.status,
                        posts.notification,
                        posts.type,
                        posts.title,
                        posts.buttons,
                        posts.text,
                        posts.file,
                        posts.created,
                        posts.published,
                        telegram_chanels.url AS channel_url,
                        telegram_chanels.channel_title AS channel_title,
                        (SELECT scheduler.item_id 
                               FROM scheduler 
                               WHERE scheduler.item_id = posts.id 
                                       AND scheduler.item_type = "POST"
                                       LIMIT 0, 1
                       ) AS is_schedule
               FROM posts
                       JOIN telegram_chanels ON telegram_chanels.id = posts.channel_id
               WHERE posts.id = :post_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':post_id' => $post_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Метод  получит канал пользователя по урл канала
     * @param 	string 	$url 	- ссылка на канал
     * @return 	obj 		- Объек
     */
    public function getChannelByUrl($url) {
        $query = 'SELECT id,
                        user_id, 
                        url,
                        status,
                        comment
               FROM telegram_chanels
               WHERE url = :url';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':url' => $url]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Удалит канал пользователя в БД сервиса
     * @param 	int 	$chanel_id 		- Ид канала
     */
    public function deletePost($post_id, $user_id) {
        $query = 'DELETE FROM posts WHERE id = :post_id AND user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':post_id' => $post_id, ':user_id' => $user_id]);
    }

}
