<?php
class VKAccount {
    protected $db; 

    public function __construct($param = null) {
        $this->db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC); 
    }

    /**
     * 
     * @param type $vk_user_id
     * @param type $user_id
     * @return type
     */
    public function getVKAccountCountByVkUserId($vk_user_id, $user_id) {
        $query = 'SELECT count(*)
                    FROM vkaccounts
                    WHERE vk_user_id = :vk_user_id
                        AND user_id != :user_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':vk_user_id'   => $vk_user_id,
            ':user_id'      => $user_id
        ]);
        return $stmt->fetchColumn();
    }

    /**
     * 
     * @param type $vk_user_id
     * @param type $user_id
     * @return type
     */
    public function getVKAccountCount($user_id) {
        $query = 'SELECT count(*)
                    FROM vkaccounts
                    WHERE user_id != :user_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':user_id'      => $user_id
        ]);
        return $stmt->fetchColumn();
    }

    /**
     * Сохраняет пользовательский аккаунт VK в БД
     * @param 	array 	$token 			- Массив с данными аккаунта пользователя VK 
     * @param 	int 	$user_id 		- ИД пользователя в сервисе  
     * @return 	bool 	$is_success 	- TRUE|FALSE в зависимости от того удалось сохранить данные в БД или нет
    */
    public function saveVKAccount($token, $user_id) {
        $is_success = false;
        $query = 'REPLACE INTO vkaccounts
                        SET user_id = :user_id,
                        access_token    = :access_token,
                        vk_user_id      = :vk_user_id,
                        first_name      = :first_name,
                        last_name       = :last_name,
                        photo           = :photo,
                        created         = NOW()';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
                ':access_token'     => $token['access_token'],
                ':vk_user_id'       => $token['user_id'],
                ':first_name'       => $token['first_name'],
                ':last_name'        => $token['last_name'],
                ':photo'            => $token['photo_400'],
                ':user_id'          => (int)$user_id
        ]);

        if (null != $this->db->lastInsertId()) {
            $is_success = true;
        }
        return $is_success;
    }

    /**
     * Возвращает список подключенных пользователем аккаунтов VK
     * @param 	int 	$user_id 	- Ид пользователя в сервисе  
     * @return 	obj 				- Объект с подключенными аккаунтами
    */
    public function getUserVKAccountsList($user_id) {
        $query = 'SELECT vkaccounts.vk_user_id,
                        vkaccounts.user_id,
                        vkaccounts.first_name,
                        vkaccounts.last_name,
                        vkaccounts.photo,
                        vkaccounts.created,
                        (SELECT COUNT(*) FROM vkgroups WHERE vkaccounts.vk_user_id = vkgroups.vk_user_id) AS group_count,
                        (SELECT COUNT(*) FROM vkgroup_import_rules WHERE vkgroup_import_rules.vk_user_id = vkaccounts.vk_user_id) AS import_rules_count
               FROM vkaccounts
               WHERE user_id = :user_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Возвращает access token учетной записи пользователя в VK
     * @param 	int 	$user_vk_id - Ид пользователя в VK  
     * @return 	string 				- Токкен
    */
    public function getVKAccountAccessToken($user_vk_id) {
        $query = 'SELECT access_token
                    FROM vkaccounts
                   WHERE vk_user_id = :vk_user_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':vk_user_id' => $user_vk_id]);
        return $stmt->fetchColumn();
    }

    /**
     * Возвращает данные аккаунта ВК по ид пользователю ВК
     * @param 	int 	$user_vk_id - Ид пользователя в VK  
     * @return 	obj 				- Данные для конкретного аккаунта VK
    */
    public function getVKAccountById($user_vk_id) {
        $query = 'SELECT vk_user_id,
                        user_id,
                        first_name,
                        last_name,
                        photo,
                        created,
                        access_token
               FROM vkaccounts
               WHERE vk_user_id = :vk_user_id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':vk_user_id' => $user_vk_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

}
?>