<?php
class RSS {
    protected $db; 

    public function __construct($param = null) {
        $this->db = $this->getDBConnection();
    }

    /**
     * Коннектор к БД
     * @return  obj     - Объект PDO
     */
    protected function getDBConnection() {
        $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $db;
    }

    /**
     * Добавляет RSS фид
     * @param   int     $user_id        - Ид пользователя в системме
     * @param   string  $url            - Ссылка на RSS фид
     * @param   string  $comment        - Комментарий
     * @param   string  $download_file  - Ссылка на скачаный фаил
     */
    public function addRSS($user_id, $url, $comment, $download_file) {
        $query = 'INSERT INTO rss
                      SET user_id           = :user_id,
                          url               = :url,
                          comment           = :comment,
                          download_file     = :download_file,
                          available         = "yes",
                          created           = NOW(),
                          checked           = NOW()';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':user_id'	        => $user_id,
            ':url'              => $url,
            ':comment'	        => $comment,
            ':download_file'	=> $download_file,
        ]);
        return $this->db->lastInsertId();
    }
    
    public function getRSSById($rss_id) {
        $query = 'SELECT id,
                         url,
                         user_id,
                         comment,
                         available,
                         created,
                         checked,
                         download_file
                FROM rss
                WHERE id = :id
                ORDER BY id ASC';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':id' => $rss_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Удаляет RSS фид
     * @param   int  $rss_id    - Ид RSS в системме
     */
    public function deleteRSS($rss_id) {
        try {
            $this->db->beginTransaction();
            // Получим правила импорта
            $query  = 'SELECT id FROM rss_import_rules WHERE rss_id = :rss_id';
            $stmt   = $this->db->prepare($query);
            $stmt->execute([':rss_id' => $rss_id]);
            $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rules)) {
                // Удалим мета данные для правил импорта на основе RSS
                $query  = 'DELETE FROM rss_import_meta WHERE rss_id = :rss_id';
                $stmt   = $this->db->prepare($query);
                $stmt->execute([':rss_id' => $rss_id]);

                // Удалим правила импорта для RSS
                $query  = 'DELETE FROM rss_import_rules WHERE rss_id = :rss_id';
                $stmt   = $this->db->prepare($query);
                $stmt->execute([':rss_id' => $rss_id]);
            }
            
            // Удалим RSS из сервиса
            $query  = 'DELETE FROM rss WHERE id = :rss_id';
            $stmt   = $this->db->prepare($query);
            $stmt->execute([':rss_id' => $rss_id]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
        }
    }
    
    public function getRSSCount($user_id) {
        $query = 'SELECT COUNT(*)
               FROM rss
               WHERE user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    /**
     * Получает список RSS лент в системме
     * @param    int    $user_id    - Ид пользователя в системме
     * @return   obj                - Объект с RSS лентами
     */
    public function getRSSListAll($user_id) {
        $query = 'SELECT id,
                         url,
                         user_id,
                         comment,
                         available,
                         created,
                         checked
                FROM rss
                WHERE user_id = :user_id
                ORDER BY id ASC';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Получает список RSS лент в системме с учетом фильтраций
     * @param    int        $user_id    - Ид пользователя в системме
     * @param    array      $options    - Массив с параметрами
     * @return   obj                    - Объект с RSS лентами
     */
    public function getRSSList($user_id, $options = []) {
        $query = 'SELECT id,
                         url,
                         user_id,
                         comment,
                         available,
                         created,
                         checked
                FROM rss
                WHERE user_id = :user_id
                ORDER BY id DESC';
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
     * Проверит есть ли  фаил с таким url в базе
     * @param 	int 	$user_id    - Ид пользователя в сервисе
     * @param 	string 	$url 	    - Ссылка на RSS
     * @return 	obj 	            - Объект с данными из БД
     */
    public function checkRSSInDB($user_id, $url) {
        $query = 'SELECT id 
                    FROM rss 
                    WHERE url = :url 
                        AND user_id = :user_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':url'      => $url,
            ':user_id'  => $user_id
        ]);
        return $stmt->fetch();
    }
}
?>