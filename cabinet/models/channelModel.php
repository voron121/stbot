<?php

class TelegramChannel {

    protected $db;

    public function __construct() {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    /**
     * Метод добавит канал в БД
     * @param 	string 	$url 			- Ссылка на канал
     * @param 	int 	$user_id 		- Ид пользователя
     * @param 	string 	$telegram_chat_id       - Ид чата канала
     * @return 	int 	lastInsertId            - Ид добавленной записи
     */
    public function addChannel($url, $user_id, $telegram_chat_id) {
        $query = 'INSERT INTO telegram_chanels
                    SET user_id             = :user_id,
                        url                 = :url,
                        telegram_chat_id    = :telegram_chat_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':user_id'          => $user_id,
            ':url'              => $url,
            ':telegram_chat_id' => $telegram_chat_id
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getChannelsCount($user_id) {
        $query = 'SELECT COUNT(*)
               FROM telegram_chanels
               WHERE user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Метод  получит список всех добавленных в сервис каналов пользователя
     * @param 	int 	$user_id    - Ид пользователя
     * @return 	obj                 - Объек со списком каналов пользователя в БД
     */
    public function getUserTelegramChannelsList($user_id, $options = []) {
        $query = 'SELECT id,
                         user_id, 
                         url,
                         status,
                         comment,
                         channel_title,
                         channel_description,
                         channel_users_counter,
                         (SELECT COUNT(id) FROM vkgroup_import_rules WHERE vkgroup_import_rules.channel_id = telegram_chanels.id) AS rules_count
               FROM telegram_chanels
               WHERE user_id = :user_id';
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
     * Метод  получит список всех добавленных в сервис каналов пользователя
     * @param 	int 	$user_id    - Ид пользователя
     * @return 	obj                 - Объек со списком каналов пользователя в БД
     */
    public function getUserTelegramChannelsListAll($user_id) {
        $query = 'SELECT id,
                         user_id, 
                         url,
                         status,
                         comment,
                         channel_title,
                         channel_description,
                         channel_users_counter,
                         (SELECT COUNT(id) FROM vkgroup_import_rules WHERE vkgroup_import_rules.channel_id = telegram_chanels.id) AS rules_count
               FROM telegram_chanels
               WHERE user_id = :user_id  
                    ORDER BY Id DESC ';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Метод  получит канал пользователя по урл канала
     * @param 	string 	$url 	- ссылка на канал
     * @return 	obj 			- Объек
     */
    public function getChannelByUrl($url) {
        $query = 'SELECT id,
                         user_id, 
                         url,
                         status,
                         comment,
                         telegram_chat_id,
                         channel_title,
                         channel_description,
                         channel_users_counter
               FROM telegram_chanels
               WHERE url = :url';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':url' => $url]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Метод  получит канал пользователя по ид
     * @param 	int 	$id 	- Ид канала
     * @return 	obj 			- Объек
     */
    public function getChannelById($id) {
        $query = 'SELECT id,
                         user_id, 
                         url,
                         status,
                         comment,
                         telegram_chat_id,
                         channel_title,
                         channel_description,
                         channel_users_counter,
                         (SELECT COUNT(id) FROM vkgroup_import_rules WHERE vkgroup_import_rules.channel_id = telegram_chanels.id) AS rules_count
               FROM telegram_chanels
               WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Метод установит статус "approved" каналу в БД прошедшему верификацию
     * @param 	int 	$chanel_id 		- Ид канала
     * @return 	int 	lastInsertId 	- Ид добавленной записи
     */
    public function setChannelVerificationStatus($chanel_id) {
        $query = 'UPDATE telegram_chanels
					SET status 	= "approved"
					WHERE id 	= :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $chanel_id]);
        return $this->db->lastInsertId();
    }

    /**
     * Удалит канал пользователя в БД сервиса
     * @param 	int 	$chanel_id 		- Ид канала
     */
    public function deleteChannel($chanel_id) {
        try {
            $this->db->beginTransaction();
            // Получим правила импорта
            $query = 'SELECT id FROM vkgroup_import_rules WHERE channel_id = :channel_id';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':channel_id' => $chanel_id]);
            $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rules)) {
                // Удалим правила импорта для группы
                $query = 'DELETE FROM vkgroup_import_rules WHERE id IN(' . implode(",", array_column($rules, 'id')) . ')';
                $stmt = $this->db->query($query);
                $stmt->execute();

                // Удалим мету импорта для правил группы
                $query = 'DELETE FROM vkimport_messages_meta WHERE rule_id IN(' . implode(",", array_column($rules, 'id')) . ')';
                $stmt = $this->db->query($query);
                $stmt->execute();

                // Удалим офсеты импорта для правил группы
                $query = 'DELETE FROM vkgroups_import_meta WHERE rule_id IN(' . implode(",", array_column($rules, 'id')) . ')';
                $stmt = $this->db->query($query);
                $stmt->execute();
            }
            // Удалим статистику канала
            $query = 'DELETE FROM telegram_channels_stat WHERE channel_id = :chanel_id';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':chanel_id' => $chanel_id]);

            // Удалим канал телеграм из сервиса
            $query = 'DELETE FROM telegram_chanels WHERE id = :chanel_id';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':chanel_id' => $chanel_id]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
        }
    }

    /**
     * Метод вернет ид телеграм канала в формате @ChannelName
     * @param 	int 	$chanel_id  - Ид канала
     * @return 	string              - Ид телеграм канала в формате @ChannelName
     */
    public function getTelegramChannelId($id) {
        $query = 'SELECT url FROM telegram_chanels WHERE id = :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $chanel_url = $stmt->fetchColumn();
        return str_replace('https://t.me/', '@', $chanel_url);
    }

}
