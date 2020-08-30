<?php

include(__DIR__ . '/../../config.php');
include(__DIR__."/../../core/coreTools.php");
// RSS parser  bot helper
class RSSParserTools {

    const MESSAGE_LIMIT = 4096;

    protected $chanel_id;

    public function __construct($param = null) {
        $this->db = $this->getDBConnection();
    }

    public function setChanelId($chanel_id) {
        $this->chanel_id = $chanel_id;
        return $this->chanel_id;
    }
    
    /**
     * Проверяет доступность файла RSS
     * @param   string  $url    - Ссылка на RSS фид
     * @return  bool            - TRUE|FALSE в зависимости от того можем добавить фид или нет
    */
    public function checkUrlAvailable($url) {
        $curlInit = curl_init($url);
        curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlInit, CURLOPT_HEADER, true);
        curl_setopt($curlInit, CURLOPT_NOBODY, true);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlInit);
        $info = curl_getinfo($curlInit);
        curl_close($curlInit);
        if (200 == $info['http_code']) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $url
     * @param type $user_id
     * @param type $rule_id
     * @return      string  $file_name      - Имя сохраненного файла на сервере
     */
    public function downloadRSS($url, $user_id, $rule_id) {
        $user_dirs  = coreTools::getUserDirs($user_id);
        $file_name  = md5($url).".xml";
        $path       = __DIR__."/../..".$user_dirs["xml_path"].$file_name; 
        if (false == file_put_contents($path, file_get_contents($url))) {
            $file_name = null;
        }
        return $file_name;
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
     * 
     */
    public function getUserRSSByLogin($login) {
        $query = 'SELECT url, 
                        user_id, 
                        rss.id as id
                    FROM rss 
                        JOIN users ON rss.user_id = users.id
                    WHERE users.login = :login';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':login' => $login]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * 
     * @param type $rss_id
     * @param type $available
     */
    public function updateRSSAvailableState($rss_id, $available) {
        $query = 'UPDATE rss SET
                        available   = :available,
                        checked     = NOW()
                WHERE id = :rss_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
                ':available'    => $available,
                ':rss_id'       => $rss_id
            ]);
    }
    
    /**
     * Получает информацию для импорта
     *
     * @param 	string 	$login 	- Логин пользователя в сервисе
     * @return 	obj 			- Объект с параметрами импорта, токеном и тд
     */
    public function getUserRulesInfoByLogin($login) {
        $query = 'SELECT rss_import_rules.id as rule_id,
                             rss_import_rules.rss_id,
                             rss_import_rules.chanel_id,
                             rss_import_rules.user_id,
                             rss_import_rules.name,
                             rss_import_rules.publish_image,
                             rss_import_rules.image_tag,
                             rss_import_rules.image_tag_mode,
                             rss_import_rules.publish_url,
                             rss_import_rules.read_more_text,
                             rss_import_rules.stop_words,
                             rss_import_rules.sheduler,
                             rss_import_rules.limit,
                             telegram_chanels.url,
                             telegram_chanels.telegram_chat_id,
                             rss.download_file
                        FROM rss_import_rules
                            JOIN users		        ON rss_import_rules.user_id 	= users.id
                            JOIN telegram_chanels 	ON rss_import_rules.chanel_id 	= telegram_chanels.id
                            JOIN rss 		        ON rss_import_rules.rss_id      = rss.id
                        WHERE users.login = :login
                            AND rss_import_rules.state ="on"';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':login' => $login]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Получает записи постов из RSS
     *
     * @param   string  $rss    - Ссылка на RSS фаил
     * @return  DOM     $items  - Список объектов с айтемами
     */
    public function getRSSItems($rss) {
        $xmlDoc = new DOMDocument();
        $xmlDoc->load($rss);
        $items = $xmlDoc->getElementsByTagName("item");
        return $items;
    }

    /**
     * Вернет значение элемента XML по его тегу
     * @param   obj     $item       - Объект DOMDocument
     * @param   string  $tagName    - Название тега
     * @return  string              - Значение элемента
     */
    public function getNodeValueByTagName($item, $tagName) {
        $nodes = $item->getElementsByTagName($tagName);
        if (!$nodes->length) {
            return null;
        }
        return $nodes->item(0)->nodeValue;
    }

    /**
     * Создает папку на сервере для сохранения данных
     *
     * @param 	int 	$user_id 	    - Ид пользователя в сервисе
     * @param 	int 	$rule_id 	    - Ид правила импорта
     * @return  string 	$images_dir     - Ссылка на папку на сервере
     */
    public function createUploadDir($user_id, $rule_id) {
        $user_dirs      = coreTools::getUserDirs($user_id);
        $images_dir     = __DIR__."/../..".$user_dirs["rss_import_rules_path"].$rule_id. "/";; 
        if (!file_exists($images_dir)) {
            if (true == mkdir($images_dir, 0755, true)) {
                file_put_contents($images_dir."index.html", " ");
            }
        }
        return $images_dir;
    }

    /**
     * Сохраняет изображение из RSS в папку на сервере
     *
     * @param 	string 	$url 	- Ссылка на изображение ВК
     * @param 	string 	$rule 	- Ссылка на папку на сервере
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
     * Получит из кеша данные об импорте RSS
     *
     * @param   int     $rule_id    - Ид правила импорта
     * @return  array               - Индексированные по хешу записи массив с данными из таблицы rss_import_meta
     */
    public function getCachedRSSItems($rule_id) {
        $query = 'SELECT user_id,
                         rule_id,
                         rss_id,
                         rss_item_hash,
                         telegram_message_id,
                         telegram_chat_id, 
                         timestamp
                     FROM rss_import_meta
                     WHERE rule_id = :rule_id
                     ORDER BY id DESC
                     LIMIT 0, 1000';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':rule_id' => $rule_id]);
        $import_cache = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($import_cache, null, 'rss_item_hash');
    }

    /**
     * Получит ссылку на изображение для записи из RSS
     *
     * @param   DOM     $item       - Объект DOM из RSS фида (item)
     * @param   string  $tag        - Название тега в которои урл на изображение
     * @param   string  $mode       - Режим получения ссылки (attr - из атрибута тега, value - из значения тега)
     * @return  string  $image_url  - Ссылка на изображение
     */
    public function getItemImageFromRSS($item, $tag, $mode) {
        if ('attr' == $mode) {
            $image_url = $item->getElementsByTagName($tag)->item(0)->getAttribute('url');
        } elseif ('value' == $mode) {
            $image_url = $this->getNodeValueByTagName($item, $tag);
        }
        return $image_url;
    }

    /**
     * Формирует ссылку "Подробнее" на основе правила импорта и режима публикации в телеграм
     *
     * @param   string  $url    - Ссылка
     * @param   string  $text   - Текст для ссылки
     * @param   string  $mode   - Режим для которого будет сформирована ссылка (HTML /  Markdown)
     * @return  string  $link   - Ссылка для статьи
     */
    public function getReadMoreUrl($url, $text, $mode = 'HTML') {
        if ('HTML' != $mode) {
            $link = '  [' . $text . '](' . $url . ')';
        } else {
            $link = ' <a href="' . $url . '">' . $text . '</a>';
        }
        return $link;
    }

    /**
     * Получит ответ из API телеграм и сформирует массив с ответом для дальнейшей обработки
     *
     * @param   string  $response   - JSON ответ от API
     * @param   array   $message    - Массив с данными которые публиковали
     * @return  array   $result     - Сгенерированный для удобства массив с ответом от API и данными об публикации
     */
    public function getResponse($response, $message) {
        $response = json_decode($response, true);
        $result = [];
        if (isset($response['ok']) && true == $response['ok']) {
            if (isset($response['result']['message_id'])) { // Ответ для одного сообщения в телеграм
                $result[] = [
                    'user_id' => $message['user_id'],
                    'rss_id' => $message['rss_id'],
                    'rss_item_hash' => $message['rss_item_hash'],
                    'telegram_message_id' => $response['result']['message_id'],
                    'chat_id' => $response['result']['chat']['id'],
                    'rule_id' => $message['rule_id'],
                    'timestamp' => date('Y-m-d H:m:i', $response['result']['date']),
                ];
            }
        }
        return $result;
    }

    /**
     * Сохранит  мету импорта в БД
     *
     * @param   array   $import_meta    - Массив с данными импорта
     */
    public function saveImportMeta($import_meta) {
        $query = 'INSERT INTO rss_import_meta
                        SET user_id 			= :user_id,
                            rule_id 			= :rule_id,
                            rss_id   			= :rss_id,
                            rss_item_hash 		= :rss_item_hash,
                            telegram_message_id         = :telegram_message_id,
                            telegram_chat_id            = :telegram_chat_id, 
                            timestamp 			= :timestamp';
        $stmt = $this->db->prepare($query);
        foreach ($import_meta as $meta) {
            $stmt->execute([
                ':user_id'              => $meta['user_id'],
                ':rule_id'              => $meta['rule_id'],
                ':rss_id'               => $meta['rss_id'],
                ':rss_item_hash'        => $meta['rss_item_hash'],
                ':telegram_chat_id'     => $meta['chat_id'],
                ':telegram_message_id'  => $meta['telegram_message_id'],
                ':timestamp'            => $meta['timestamp'],
            ]);
        }
    }

    /**
     * Удалит изображения из папки на сервере
     *
     * @param   array   $images_to_delete   - Массив с изображениями к удалению
     */
    public function clearUploadDir($images_to_delete) {
        if (!empty($images_to_delete)) {
            foreach ($images_to_delete as $image_to_delete) {
                unlink($image_to_delete);
            }
        }
    }

    /**
     * TODO: пофиксить. Потенциально багованный метод. 
     * Функция обрежет текст с учетом текста ссылки
     * @param       obj     $rule               - Объек с параметрами импорта
     * @param       string  $text               - Строка с текстом для сообщения
     * @param       string  $url                - ссылка на статью
     * @return      string  $prepared_message   - Обрезанный текст сообщения
     */
    public function cutText($rule, $text, $url) {
        $limit = SELF::MESSAGE_LIMIT + (mb_strlen($rule->read_more_text, 'utf-8') + mb_strlen($url, 'utf-8'));
        //echo $text ." \r\n".$url." \r\n".mb_substr($text, 0, $limit, 'utf-8');
        return mb_substr($text, 0, $limit, 'utf-8');
    }

    /**
     * Проверит наличие стоп-слов в строке
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