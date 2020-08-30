<?php

class VKGroupsImport {

    protected $db;

    public function __construct($param = null) {
        $this->db = $this->getDBConnection();
    }

    protected function getDBConnection() {
        $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $db;
    }

    /**
     * Сохранит правило импорта сообществ Вконтакте в БД
     * @param 	int 	$user_id 	- Ид пользователя в сервисе
     * @param 	array 	$rule_set 	- Массив с параметрами импорта
     * @return 	int                     - Ид созданного правила
     */
    public function addVKGroupImportRule($user_id, $rule_set) {
        $rule_id = null;
        try {
            $this->db->beginTransaction();
            $query = 'INSERT INTO vkgroup_import_rules
                          SET user_id		= :user_id,
                              vk_user_id	= :vk_user_id,
                              state             = :state,
                              name		= :name,
                              channel_id	= :channel_id,
                              group_id		= :group_id,
                              mode		= :mode,
                              `order`		= :order,
                              text_mode		= :text_mode,
                              url_mode		= :url_mode,
                              sheduler		= :sheduler,
                              `limit`		= :limit,
                              stop_words	= :stop_words';
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':user_id'      => $user_id,
                ':vk_user_id'   => $rule_set['vk_user_id'],
                ':state'        => $rule_set['state'],
                ':name'         => $rule_set['rule_name'],
                ':channel_id'   => $rule_set['user_chanel_id'],
                ':group_id'     => $rule_set['vk_group_id'],
                ':mode'         => $rule_set['mode'],
                ':order'        => $rule_set['order'],
                ':text_mode'    => $rule_set['text_mode'],
                ':url_mode'     => $rule_set['url_mode'],
                ':sheduler'     => $rule_set['sheduler'],
                ':limit'        => $rule_set['limit'],
                ':stop_words'   => $rule_set['stop_words']
            ]);
            $rule_id = $this->db->lastInsertId();
            $query = 'INSERT INTO vkgroups_import_meta
                              SET rule_id	= :rule_id,
                                  offset	= :offset,
                                  count         = 0';
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':rule_id'  => $rule_id,
                ':offset'   => 0
            ]);
            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new PDOException($e->getMessage());
        }
        return $rule_id;
    }

    /**
     * Сохранит изменения правила  импорта сообществ Вконтакте в БД
     * @param 	array 	$rule_set 	- Массив с параметрами импорта
     * @return 	int 				- Ид редактированного правила
     */
    public function editVKGroupImportRule($rule_set) {
        try {
            $query = 'UPDATE vkgroup_import_rules
                            SET vk_user_id	= :vk_user_id,
                            state               = :state,
                            name                = :name,
                            channel_id          = :channel_id,
                            group_id		= :group_id,
                            mode                = :mode,
                            `order`             = :order,
                            text_mode		= :text_mode,
                            url_mode		= :url_mode,
                            sheduler		= :sheduler,
                            stop_words          = :stop_words,
                            `limit`             = :limit
                    WHERE id          = :rule_id';
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':rule_id'          => $rule_set['rule_id'],
                ':vk_user_id'       => $rule_set['vk_user_id'],
                ':state'            => $rule_set['state'],
                ':name'             => $rule_set['rule_name'],
                ':channel_id'       => $rule_set['user_chanel_id'],
                ':group_id'         => $rule_set['vk_group_id'],
                ':mode'             => $rule_set['mode'],
                ':order'            => $rule_set['order'],
                ':text_mode'        => $rule_set['text_mode'],
                ':url_mode'         => $rule_set['url_mode'],
                ':sheduler'         => $rule_set['sheduler'],
                ':stop_words'       => $rule_set['stop_words'],
                ':limit'            => $rule_set['limit']
            ]);
        } catch (PDOException $e) {
            $this->db->rollback();
            throw new PDOException($e->getMessage());
        }
        return $this->db->lastInsertId();
    }

    /**
     * Удалит правило импорта в БД
     * @param 	int 	$rule_id 	- Ид правила импорта
     */
    public function deleteVKImportRule($rule_id) {
        try {
            $this->db->beginTransaction();

            $query = 'DELETE FROM vkimport_messages_meta WHERE rule_id = :rule_id';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':rule_id' => $rule_id]);

            $query = 'DELETE FROM vkgroups_import_meta WHERE rule_id = :rule_id';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':rule_id' => $rule_id]);

            $query = 'DELETE FROM vkgroup_import_rules WHERE id = :rule_id';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':rule_id' => $rule_id]);

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollback();
        }
    }

    /**
     * Включит или выключит правило импорта в БД
     * @param 	string 	$state 		- Состояние (on/off)
     * @param 	int 	$rule_id 	- Ид правила импорта
     */
    public function changeStateVKImportRule($state, $rule_id) {
        $query = 'UPDATE vkgroup_import_rules
                        SET state = :state
                    WHERE id = :rule_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':state' => $state, ':rule_id' => $rule_id]);
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getRulesCount($user_id, $options = []) {
        $query = 'SELECT count(*)
               FROM vkgroup_import_rules
                   JOIN vkgroups_import_meta ON vkgroups_import_meta.rule_id = vkgroup_import_rules.id
               WHERE user_id = :user_id ';
        if (isset($options['group_id'])) {
            $query .= ' AND group_id = :group_id ';
        }
        if (isset($options['channel_id'])) {
            $query .= ' AND channel_id = :channel_id ';
        }
        $query .= ' ORDER BY state DESC';
        $stmt = $this->db->prepare($query);
        $exec_params = [':user_id' => $user_id];
        if (isset($options['group_id'])) {
            $exec_params[':group_id'] = $options['group_id'];
        }
        if (isset($options['channel_id'])) {
            $exec_params[':channel_id'] = $options['channel_id'];
        }
        
        $stmt->execute($exec_params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Вернет список правил импорта групп ВК
     * @param 	int 	$user_id 	- Ид пользователя в сервисе
     * @return 	obj 				- Объект с правилами импорта групп ВК
     */
    public function getVKImportRulesList($user_id, $options = []) {
        $query = 'SELECT id,
                        state,
                        vk_user_id,
                        name,
                        channel_id,
                        group_id,
                        mode,
                        text_mode,
                        `order`,
                        url_mode,
                        stop_words,
                        vkgroups_import_meta.count
               FROM vkgroup_import_rules
                   JOIN vkgroups_import_meta ON vkgroups_import_meta.rule_id = vkgroup_import_rules.id
               WHERE user_id = :user_id ';
        
        if (isset($options['group_id'])) {
            $query .= ' AND group_id = :group_id ';
        }
        if (isset($options['channel_id'])) {
            $query .= ' AND channel_id = :channel_id ';
        }
        $query .= ' ORDER BY state ASC';
        if (isset($options['page'])) {
            $query .= ' LIMIT '.$options['offset'].', '.ITEMS_ON_PAGE_LIMIT.' ';
        } else {
            $query .= ' LIMIT 0, '.ITEMS_ON_PAGE_LIMIT.' ';
        }
         
        $stmt = $this->db->prepare($query);

        $exec_params = [':user_id' => $user_id];
        if (isset($options['group_id'])) {
            $exec_params[':group_id'] = $options['group_id'];
        }
        if (isset($options['channel_id'])) {
            $exec_params[':channel_id'] = $options['channel_id'];
        }
        
        $stmt->execute($exec_params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Вернет правило импорта в ВК по ид
     * param    int     $rule_id    - Ид правила импорта
     * @return 	obj 	$rule       - Объект правила импорта
     */
    public function getImportRuleById($rule_id) {
        $query = 'SELECT id,
                        state,
                        vk_user_id,
                        name,
                        channel_id,
                        group_id,
                        mode,
                        text_mode,
                        `order`,
                        url_mode,
                        sheduler,
                        stop_words,
                        `limit`
               FROM vkgroup_import_rules
               WHERE id = :rule_id
               ORDER BY state ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':rule_id' => $rule_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

}

?>