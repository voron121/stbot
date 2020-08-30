<?php
class RSSImport {
    protected $db; 

    public function __construct() {
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
     * Сохранит правило импорта RSS в БД
     * @param 	int 	$user_id 	- Ид пользователя в сервисе
     * @param 	array 	$rule_set 	- Массив с параметрами импорта
     * @return 	int 				- Ид созданного правила
     */
    public function addRSSImportRule($user_id, $rule_set) {
        $query = 'INSERT INTO rss_import_rules
                      SET user_id               = :user_id,
                          rss_id                = :rss_id,
                          chanel_id             = :chanel_id,
                          publish_image         = :publish_image,
                          image_tag             = :image_tag,
                          image_tag_mode        = :image_tag_mode,
                          publish_url           = :publish_url,
                          read_more_text        = :read_more_text,
                          stop_words            = :stop_words,
                          sheduler              = :sheduler,
                          name                  = :name,
                          `limit`               = :limit,
                          state                 = :state';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':user_id'			=> $user_id,
            ':rss_id'                   => $rule_set['rss_id'],
            ':chanel_id'		=> $rule_set['chanel_id'],
            ':name'		        => $rule_set['name'],
            ':publish_image'            => $rule_set['publish_image'],
            ':image_tag'                => $rule_set['image_tag'],
            ':image_tag_mode'           => $rule_set['image_tag_mode'],
            ':publish_url'		=> $rule_set['publish_url'],
            ':read_more_text'           => $rule_set['read_more_text'],
            ':stop_words'               => $rule_set['stop_words'],
            ':sheduler'                 => $rule_set['sheduler'],
            ':limit'                    => $rule_set['limit'],
            ':state'                    => $rule_set['state']
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Сохранит изменения правила  импорта RSS в БД
     * @param   int     $user_id        - Ид пользователя в сервисе
     * @param 	array 	$rule_set 	- Массив с параметрами импорта
     * @return 	int                     - Ид редактированного правила
     */
    public function editRSSImportRule($user_id, $rule_set) {
        $query = 'UPDATE rss_import_rules
			SET user_id                 = :user_id,
                            rss_id                  = :rss_id,
                            chanel_id               = :chanel_id,
                            publish_image           = :publish_image,
                            image_tag               = :image_tag,
                            image_tag_mode          = :image_tag_mode,
                            publish_url             = :publish_url,
                            read_more_text          = :read_more_text,
                            stop_words              = :stop_words,
                            sheduler                = :sheduler,
                            name                    = :name,
                            `limit`                 = :limit,
                            state                   = :state 
                      WHERE id                      = :id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':user_id'			=> $user_id,
            ':rss_id'                   => $rule_set['rss_id'],
            ':chanel_id'		=> $rule_set['chanel_id'],
            ':name'		        => $rule_set['name'],
            ':publish_image'            => $rule_set['publish_image'],
            ':image_tag'                => $rule_set['image_tag'],
            ':image_tag_mode'           => $rule_set['image_tag_mode'],
            ':publish_url'		=> $rule_set['publish_url'],
            ':read_more_text'           => $rule_set['read_more_text'],
            ':stop_words'               => $rule_set['stop_words'],
            ':sheduler'                 => $rule_set['sheduler'],
            ':limit'                    => $rule_set['limit'],
            ':state'                    => $rule_set['state'],
            ':id'                       => $rule_set['rss_rule_id']
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * 
     * @param type $rule_id
     * @return type
     */
    public function getRSSImportRuleById($rule_id) {
        $query = 'SELECT id,
                        rss_id,
                        chanel_id,
                        user_id,
                        name,
                        state,
                        publish_image,
                        image_tag,
                        image_tag_mode,
                        publish_url,
                        read_more_text,
                        stop_words,
                        `limit`,
                        sheduler
               FROM rss_import_rules
               WHERE id = :rule_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':rule_id' => $rule_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Удаляет правило импорта RSS
     * @param   int  $rss_import_rule_id    - Ид RSS в системме
     */
    public function deleteImportRuleRSS($rss_import_rule_id) {
        $query = 'DELETE FROM rss_import_rules WHERE id = :rss_import_rule_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':rss_import_rule_id' => $rss_import_rule_id]);
    }

    /**
     * Получает список правил импорта для RSS
     * @param int $user_id - Ид пользователя в системме
     * @return
     */
    public function getRSSImportRulesCount($user_id) {
        $query = 'SELECT COUNT(*)
               FROM rss_import_rules
               WHERE user_id = :user_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Получает список правил импорта для RSS
     * @param int $user_id - Ид пользователя в системме
     * @return
     */
    public function getRSSImportRulesList($user_id) {
        $query = 'SELECT id,
                        rss_id,
                        user_id,
                        chanel_id,
                        name,
                        `limit`,
                        state
               FROM rss_import_rules
               WHERE user_id = :user_id
               ORDER BY id ASC';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
?>