<?php
class VKGroups {
	protected $db; 

	public function __construct($param = null) {
		$this->db = $this->getDBConnection();
	}
 	
	protected function getDBConnection() {
		$db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']);
		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	    $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		return $db;
	}
 	
 	/**
	 * Сохраняет в БД группу пользователя VK (сообщество)
     * @param 	int 	$user_id 	- Ид пользователя в сервисе
     * @param 	int 	$vk_user_id - Ид пользователя в ВК
	 * @param 	int 	$group_info	- Массив с данныыми группы
	*/
 	public function addVKGroup($user_id, $vk_user_id, $group_info) {
 		$query = 'REPLACE INTO vkgroups
						  SET user_id		= :user_id,
					          vk_user_id	= :vk_user_id,
					          group_id		= :group_id,
				          	  group_name	= :group_name,
				           	  screen_name	= :screen_name,
			            	  group_image	= :group_image';
		$stmt  = $this->db->prepare($query);
		$stmt->execute([
			':user_id'		=> $user_id,
            ':vk_user_id'	=> $vk_user_id,
            ':group_id'		=> $group_info['id'],
			':group_name'	=> $group_info['name'],
			':screen_name'	=> $group_info['screen_name'],
			':group_image'	=> $group_info['photo_200']
		]);
 	}

 	/**
	 * Удалит группу VK из сервиса 
	 * @param 	int 	$group_id 	- Ид группы VK
	*/
 	public function deleteVKGroup($group_id) {
            try {
                $this->db->beginTransaction();
                // Получим правила импорта
                $query  = 'SELECT id FROM vkgroup_import_rules WHERE group_id = :group_id';
                $stmt   = $this->db->prepare($query);
                $stmt->execute([':group_id' => $group_id]);
                $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rules)) {
                    // Удалим правила импорта для группы
                    $query  = 'DELETE FROM vkgroup_import_rules WHERE id IN('.implode(",", array_column($rules, 'id')).')';
                    $stmt   = $this->db->query($query);
                    $stmt->execute();

                    // Удалим мету импорта для правил группы
                    $query  = 'DELETE FROM vkimport_messages_meta WHERE rule_id IN('.implode(",", array_column($rules, 'id')).')';
                    $stmt   = $this->db->query($query);
                    $stmt->execute();

                    // Удалим офсеты импорта для правил группы
                    $query  = 'DELETE FROM vkgroups_import_meta WHERE rule_id IN('.implode(",", array_column($rules, 'id')).')';
                    $stmt   = $this->db->query($query);
                    $stmt->execute();    
                }
                
                // Удалим группу ВК
                $query = 'DELETE FROM vkgroups WHERE group_id = :group_id';
                $stmt  = $this->db->prepare($query);
                $stmt->execute([':group_id' => $group_id]); 
                
                $this->db->commit();
            } catch(Exception $e){
                $this->db->rollBack();
            }
 	}
        
        /**
         * 
         * @param type $user_id
         * @return type
         */
        public function getVKGroupsCount($user_id) {
            $query = 'SELECT COUNT(*) FROM vkgroups WHERE user_id = :user_id';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchColumn();
        }
        
 	/**
	 * Вернет список групп Vk пользователя, подключенных в сервисе
	 * @param 	int 	$user_id 	- Ид пользователя в сервисе
	 * @return 	obj 				- Объект с группами пользователя
	*/
 	public function getUserVKGroupsList($user_id, $options = []) {
 		$query = 'SELECT user_id,
                                 group_id,
                                 group_name,
                                 screen_name,
                                 group_image,
                                 record_count,
                                 updated,
                                 (SELECT COUNT(id) FROM vkgroup_import_rules WHERE vkgroup_import_rules.group_id = vkgroups.group_id) AS rules_count
                       FROM vkgroups
                       WHERE user_id = :user_id
                       ORDER BY group_id DESC ';
                if (isset($options['page'])) {
                    $query .= ' LIMIT '.$options['offset'].', '.ITEMS_ON_PAGE_LIMIT.' ';
                } else {
                    $query .= ' LIMIT 0, '.ITEMS_ON_PAGE_LIMIT.' ';
                }
		$stmt  = $this->db->prepare($query);
		$stmt->execute([':user_id' => $user_id]);
		return $stmt->fetchAll(PDO::FETCH_OBJ);
 	}
        
        /**
         * Получит весь список групп ВК пользователя 
         * @param type $user_id
         * @param type $options
         * @return type
         */
        public function getUserVKGroupsListAll($user_id) {
 		$query = 'SELECT user_id,
                                 group_id,
                                 group_name,
                                 screen_name,
                                 group_image,
                                 record_count,
                                 updated,
                                 (SELECT COUNT(id) FROM vkgroup_import_rules WHERE vkgroup_import_rules.group_id = vkgroups.group_id) AS rules_count
                       FROM vkgroups
                       WHERE user_id = :user_id
                       ORDER BY group_id DESC';
		$stmt  = $this->db->prepare($query);
		$stmt->execute([':user_id' => $user_id]);
		return $stmt->fetchAll(PDO::FETCH_OBJ);
 	}

 	/**
	 * Вернет данные о группе Вконтакте из БД
	 * @param 	int 	$group_id 	- Ид группы Вконтакте
	 * @return 	obj 				- Объект с даными группы
	*/
 	public function getUserVKGroupById($group_id) {
 		$query = 'SELECT user_id,
                                group_id,
                                group_name,
                                screen_name,
                                group_image,
                                (SELECT COUNT(*) FROM vkgroup_import_rules WHERE vkgroup_import_rules.group_id = vkgroups.group_id) AS rules_count
                       FROM vkgroups
                       WHERE group_id = :group_id';
		$stmt  = $this->db->prepare($query);
		$stmt->execute([':group_id' => $group_id]);
		return $stmt->fetch(PDO::FETCH_OBJ);
 	}
}
?>