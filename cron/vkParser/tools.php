<?php

include(__DIR__ . '/../../config.php');
include(__DIR__ . "/../../core/coreTools.php");

// VK parser  bot helper
class VKParserTools {

    const MESSAGE_LIMIT = 4096;

    public function __construct($param = null) {
        $this->db = $this->getDBConnection();
    }

    /**
     * Коннектор к БД
     */
    protected function getDBConnection() {
        $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $db;
    }

    /**
     * Получает информацию для импорта
     *
     * @param 	string 	$login 	- Логин пользователя в сервисе
     * @return 	obj 			- Объект с параметрами импорта, токеном и тд
     */
    public function getUserRulesInfoByLogin($login) {
    $query = 'SELECT users.id AS user_id,
                    vkgroup_import_rules.id AS rule_id,
                    vkgroup_import_rules.vk_user_id,
                    vkgroup_import_rules.order,
                    vkgroup_import_rules.name,
                    vkgroup_import_rules.channel_id,
                    vkgroup_import_rules.group_id,
                    vkgroup_import_rules.mode,
                    vkgroup_import_rules.text_mode,
                    vkgroup_import_rules.url_mode,
                    vkgroup_import_rules.stop_words,
                    vkgroup_import_rules.sheduler,
                    vkgroup_import_rules.limit,
                    vkaccounts.vk_user_id,
                    vkaccounts.access_token,
                    vkgroups.screen_name,
                    vkgroups.record_count,
                    telegram_chanels.url,
                    telegram_chanels.telegram_chat_id,
                    vkgroups_import_meta.offset
           FROM vkgroup_import_rules
                   JOIN users                   ON vkgroup_import_rules.user_id 	= users.id
                   JOIN vkaccounts 		    ON vkaccounts.vk_user_id            = vkgroup_import_rules.vk_user_id
                   JOIN vkgroups                ON vkgroups.group_id                = vkgroup_import_rules.group_id
                   JOIN telegram_chanels 	    ON vkgroup_import_rules.channel_id 	= telegram_chanels.id
                   JOIN vkgroups_import_meta   ON vkgroups_import_meta.rule_id 	= vkgroup_import_rules.id
           WHERE users.login = :login
                   AND vkgroup_import_rules.state ="on"';
    $stmt = $this->db->prepare($query);
    $stmt->execute([':login' => $login]);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * 
     */
    protected function splitText($str, $len = 1) {
        $arr = [];
        $length = mb_strlen($str, 'UTF-8');

        for ($i = 0; $i < $length; $i += $len) {
            $arr[] = mb_substr($str, $i, $len, 'UTF-8');
        }
        return $arr;
    }

    /**
     * Разбивает сообщение на несколько частей
     *
     * @param 	string 	$message 	- Сообщение
     * @return  array 	$messages 	- Массив с сообщениями
     */
    public function splitMessage($message) {
        $messages = $this->splitText($message, self::MESSAGE_LIMIT);
        return $messages;
    }

    /**
     * Сохраняет изображение из ВК в папку на сервере
     *
     * @param 	string 	$url 	- Ссылка на изображение ВК 	 
     * @param 	string 	$dir 	- Ссылка на папку на сервере
     * @return  string 	$image 	- Ссылка на локальную версию файла  
     */
    public function saveImage($url, $dir) {
        $image = null;
        preg_match("/(\w+[.png|.jpg]{4,255})/", $url, $matches);
        $file_name = (isset($matches[0])) ? $matches[0] : null;
        if (null != $file_name && true == file_put_contents($dir . $file_name, file_get_contents($url))) {
            $image = $dir . $file_name;
        }
        return $image;
    }

    /**
     * Создает папку на сервере для сохранения данных
     * 
     * @param 	int 	$user_id 	- Ид пользователя в сервсие 	 
     * @param 	int 	$rule_id 	- Ид правила испорта
     * @return  string 	$images_dir - Ссыдка на папку на сервере
     */
    public function createUploadDir($user_id, $rule_id) {
        $user_dirs  = coreTools::getUserDirs($user_id);
        $images_dir = __DIR__."/../..".$user_dirs["vk_import_rules_path"] . $rule_id . '/';
        if (!file_exists($images_dir)) {
            if (true == mkdir($images_dir, 0755, true)) {
                file_put_contents($images_dir."index.html", " ");
            }
        }
        return $images_dir;
    }

    /**
     *
     */
    public function clearUploadDir($images_to_delete) {
        if (!empty($images_to_delete)) {
            foreach ($images_to_delete as $image_to_delete) {
                unlink($image_to_delete);
            }
        }
    }

    /**
     *
     */
    public function updateGroupOffset($offset, $import_count, $rule_id) {
        $query = 'UPDATE vkgroups_import_meta 
                        SET  offset     = offset 	+ :offset,
							 count  	= count 	+ :count
						WHERE rule_id 	= :rule_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':offset' => $offset,
            ':count' => $import_count,
            ':rule_id' => $rule_id
        ]);
    }
    
    /**
     * Сохранит аватар пользователя на сервере
     * @param string    $photo          - ссылка на аватар
     * @param int       $vk_user_id     - Ид пользователя вк
     */
    public function saveVKAGroupPhoto($photo, $vk_group_id, $user_id) {
        $user_dirs      = coreTools::getUserDirs($user_id);
        $user_photo_dir = __DIR__."/../..".$user_dirs["vk_public_images_path"];
        file_put_contents($user_photo_dir.$vk_group_id.".jpg", file_get_contents($photo));
    }

    /**
     *
     */
    public function saveGroupsMeta($group_id, $record_count) {
        $query = 'UPDATE vkgroups 
                        SET record_count    = :record_count,
                            updated         = NOW()       
                        WHERE group_id      = :group_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':group_id' => $group_id,
            ':record_count' => $record_count
        ]);
    }

    /**
     *
     */
    public function getGroupsToUpdateMeta() {
        $query = 'SELECT vkaccounts.user_id,
                        vk_user_id, 
                        access_token, 
                        screen_name, 
                        group_id
               FROM vkaccounts
                       JOIN vkgroups USING(vk_user_id)';
        $stmt = $this->db->query($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     *
     */
    public function saveMessagesImportMeta($import_meta) {
        $query = 'INSERT INTO vkimport_messages_meta
						SET user_id 			= :user_id,
							rule_id 			= :rule_id,
							vk_message_id 		= :vk_message_id,
							telegram_message_id = :telegram_message_id,
							telegram_chat_id 	= :telegram_chat_id, 
							timestamp 			= :timestamp';
        $stmt = $this->db->prepare($query);
        foreach ($import_meta as $meta) {
            $stmt->execute([
                ':user_id' => $meta['user_id'],
                ':rule_id' => $meta['rule_id'],
                ':vk_message_id' => $meta['vk_message_id'],
                ':telegram_chat_id' => $meta['chat_id'],
                ':telegram_message_id' => $meta['telegram_message_id'],
                ':timestamp' => $meta['timestamp'],
            ]);
        }
    }

    /**
     *
     */
    public function getResponse($response, $telegram_message) {
        $response = json_decode($response, true);
        $result = [];
        if (isset($response['ok']) && true == $response['ok']) {
            if (isset($response['result']['message_id'])) { // Ответ для одного сообщения в телеграм
                $result[] = [
                    'user_id' => $telegram_message['user_id'],
                    'telegram_message_id' => $response['result']['message_id'],
                    'vk_message_id' => $telegram_message['vk_message_id'],
                    'chat_id' => $response['result']['chat']['id'],
                    'rule_id' => $telegram_message['rule_id'],
                    'timestamp' => date('Y-m-d H:m:i', $response['result']['date']),
                ];
            } else {
                foreach ($response['result'] as $item) { // Ответ для нескольких сообщений (альбом и тд) в телеграм
                    $result[] = [
                        'user_id' => $telegram_message['user_id'],
                        'telegram_message_id' => $item['message_id'],
                        'vk_message_id' => $telegram_message['vk_message_id'],
                        'chat_id' => $item['chat']['id'],
                        'rule_id' => $telegram_message['rule_id'],
                        'timestamp' => date('Y-m-d H:m:i', $item['date']),
                    ];
                }
            }
        }
        return $result;
    }

    /**
     *
     */
    public function getImportedMessagesIds($rule_id, $limit) {
        $query = 'SELECT vk_message_id
                        FROM vkimport_messages_meta
                        WHERE rule_id = :rule_id
                        ORDER BY id DESC 
                        LIMIT 0, ' . $limit;
        $stmt = $this->db->prepare($query);
        $stmt->execute([':rule_id' => $rule_id]);
        return $stmt->fetchAll(PDO::FETCH_UNIQUE);
    }

    /**
     *
     */
    public function checkUrlInText($text) {
        preg_match_all("/(www.?[\w\-\.!~?&=+\*'(),\/\#\:]+)|(https?:\/\/[\w\-\.!~?&=+\*'(),\/\#\:]+)|(w+.+w)/", $text, $matches);
        return (empty($matches[0])) ? false : true;
    }

    /**
     *
     */
    public function cutUrlInText($text) {
        return preg_replace("/(www.?[\w\-\.!~?&=+\*'(),\/\#\:]+)|(https?:\/\/[\w\-\.!~?&=+\*'(),\/\#\:]+)|(w+.+w)/", '', $text);
    }

    /**
     *
     */
    public function checkStopWords($words, $text) {
        $stop_words = explode(',', $words);
        foreach ($stop_words as $stop_word) {
            if (false != stristr($text, trim($stop_word))) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 
     * @param type $sheduler
     * @return boolean
     */
    public function isMustRobotStart($sheduler) {
        $shedule    = json_decode($sheduler, true);
        $today      = date('N') - 1;
        $now        = date('G');
        if ( $shedule[$today]['time'][$now] == 0 ) {
            return false;
        } 
        return true;
    }

}

?>