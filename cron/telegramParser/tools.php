<?php

include(__DIR__ . '/../../config.php');
include(__DIR__ . '/../../core/libs/telegram/telegramTools.php');

// VK parser  bot helper
class TelegramParserTools {

    const MESSAGE_LIMIT = 4096;

    public function __construct($param = null) {
        $this->db = $this->getDBConnection();
        $this->telegram = new TelegramTools(TELEGRAM_BOT_TOKEN);
    }
    
    /**
     * Получит список всех активных пользователей сервиса
     * @return obj
     */
    public function getUsers() {
        $query = 'SELECT id,
                         login
                    FROM users
                    WHERE active = "Yes"';
        $stmt = $this->db->query($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Получет список всех заапрувенных каналов пользователя
     * @param   int     $user_id    - Ид пользователя в сервисе
     * @return  obj                 - Объект со списком каналов
     */
    public function getUserChannels($user_id) {
        $query = 'SELECT id,
                         url,
                         telegram_chat_id
                    FROM telegram_chanels
                    WHERE status = "approved" 
                        AND user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        $channels = $stmt->fetchAll(PDO::FETCH_OBJ);
        foreach ($channels as $channel) {
            $channel->telegram_channel_id = $this->telegram->getTelegramChannelIdByUrl($channel->url);
        }
        return $channels;
    }
    
    /**
     * Получит мета информацию канала 
     * @param   string      $channel_id    - Ид чата канала
     * @return  array       $channel_info  - Массив с данными о канале
     */
    protected function getChannelMetaInfo($channel_id) {
        $channel_info = [];
        // TODO: внедрить эксепшн
        $members_count_request = json_decode($this->telegram->getChatMembersCount($channel_id));
        
        if (true == $members_count_request->ok) {
            $channel_info['count'] = $members_count_request->result;
        } else {
            throw new \Exception('Не удалось получить мета информацию для канала '.$channel_id.": ". $members_count_request->error_code." ".$members_count_request->description);
        }
        
        $chat_info_request = json_decode($this->telegram->getChat($channel_id));
        if (true == $chat_info_request->ok) {
            $channel_info['title']          = $chat_info_request->result->title;
            $channel_info['username']       = (isset($chat_info_request->result->username)) ? $chat_info_request->result->username : '';
            $channel_info['type']           = $chat_info_request->result->type;
            $channel_info['description']    = (isset($chat_info_request->result->description)) ? $chat_info_request->result->description : '';
        } else {
            throw new \Exception('Не удалось получить мета информацию для канала '.$channel_id.": ". $chat_info_request->error_code." ".$chat_info_request->description);
        }
        
        return $channel_info;
    }
    
    /**
     * Получит путь к фаилу фото канала
     * @param type $channel_chat_id
     * @return type
     * @throws \Exception
     */
    protected function getChannelPhoto($channel_chat_id) {
        $response       = $this->telegram->getChat($channel_chat_id);
        $channel_info   = json_decode($response, true);
        if (false == $channel_info['ok']) {
            return null;
            //throw new \Exception('Не удалось получить информацию о фото '.$channel_id.": ". $response->error_code." ".$response->description);
        }
        $image_id   = isset($channel_info['result']['photo']['small_file_id']) ? $channel_info['result']['photo']['small_file_id'] : null ;
        $file       = $this->telegram->getFile($image_id);
        $file_info  = json_decode($file, true);
        if (false == $file_info['ok']) {
            return null;
            //throw new \Exception('Не удалось получить фаил фото для канала '.$channel_info['result']['title'].": ". $file_info['error_code']." ".$file_info['description']);
        }
        $file_path = $file_info['result']['file_path'];
        return $file_path;
    }
    
    /**
     * Сохранит фото канала на сервере
     * @param type $channel_id
     * @param type $file
     * @return type
     */
    protected function saveChannelPhoto($user_id, $channel_id, $file) {
        return $this->telegram->downloadFile($user_id, $channel_id, $file);
    }
    
    /**
     * Запишет в БД статистику канала
     * @param   array       $meta       - Массив с данными канала
     * @param   int         $channel_id - Ид канала в сервисе
     */
    protected function updateСhannelStat($meta, $channel_id) {
        $query = 'INSERT INTO telegram_channels_stat 
                        SET channel_id  = :channel_id,
                            users_count = :users_count,
                            timestamp   = NOW()';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':users_count'  => $meta['count'],
            ':channel_id'   => $channel_id
        ]);
    }
    
    /**
     * Запишет мету канала в БД
     * @param   array       $meta       - Массив с данными канала
     * @param   int         $channel_id - Ид канала в сервисе
     */
    protected function saveChannelMetaIntoCache($meta, $channel_id) {
        $query = 'UPDATE telegram_chanels 
                        SET channel_title           = :channel_title,
                            channel_description     = :channel_description,
                            channel_users_counter   = :channel_users_counter
                        WHERE id      = :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':channel_title'            => $meta['title'],
            ':channel_description'      => $meta['description'],
            ':channel_users_counter'    => $meta['count'],
            ':id'                       => $channel_id
        ]);
    }
    
    /**
     * Обновит мета информацию о канале в БД
     * @param   string      $telegram_channel_id    - Ид канала телеграм вида @channel_name
     * @param   int         $channel_id             - Ид канала в сервисе
     */
    public function updateChannelMeta($user_id, $telegram_channel_id, $channel_id) {
        $channel_meta = $this->getChannelMetaInfo($telegram_channel_id);
        if (!empty($channel_meta)) {
            $channel_photo = $this->getChannelPhoto($telegram_channel_id);
            if (true == $channel_photo) {
                $this->saveChannelPhoto($user_id, $channel_id, $channel_photo);
            }
            $this->saveChannelMetaIntoCache($channel_meta, $channel_id);
            $this->updateСhannelStat($channel_meta, $channel_id);
            return true;
        }
        return false;
    }
    
    /**
     * Коннектор к БД
     */
    protected function getDBConnection() {
        $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $db;
    }

}
?>