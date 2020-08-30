<?php
/* 
 * Класс помогает организовать очередь запросов с учетом лимитом очередей
 */

class APIRequestQueue {
    
    public $retry_limit         = 5;
    public $waiting_for_retry   = 1000000; // 1000000 = 1 секунда 
    public $request_queue_count = 5;
    
    protected $table    = null;
    protected $db       = null;
    
    /**
     * @param string $type - Тип запроса к API (telegram, vk)
     * @throws Exception
     */
    public function __construct($type) { 
        if ('telegram' == $type) {
          $this->table = 'telegram_queue_request';
        } elseif('vk' == $type) {
            $this->table = 'vk_queue_request';
        } else {
            throw new Exception('Wrong type');
        }
        
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_QUEUE_NAME , DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }
    
    /**
     * 
     * @param type $request
     * @param type $method
     * @return type
     */
    public function addRequest($request, $method) {
        $request    = json_encode($request); 
        $query = 'INSERT INTO '.$this->table.'
                        SET request_data    = :request_data, 
                            request_method  = :request_method,
                            request_status  = "new"';
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            "request_method"    => $method,
            "request_data"      => $request
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * 
     * @return type
     */
    public function getRequestsCount() {
        $query  = 'SELECT COUNT(request_id)
                    FROM '.$this->table.' 
                    WHERE request_status  = "new"';
        $stmt   = $this->db->query($query);
        $stmt->execute(); 
        return $stmt->fetchColumn();
    }
    
    /**
     * 
     * @return type
     */
    public function getRequest() {
        $query  = 'SELECT request_id,
                          request_data, 
                          request_method,
                          timestamp
                    FROM '.$this->table.' 
                    WHERE request_status  = "new"
                    ORDER BY timestamp ASC';
        $stmt   = $this->db->query($query);
        $stmt->execute();
        $request = $stmt->fetch(PDO::FETCH_OBJ);
        return $request;
    }
    
    /**
     * 
     * @param type $request_id
     * @param type $status
     */
    public function updateRequestStatus($request_id, $status) {
        $query  = 'UPDATE '.$this->table.'
                    SET request_status  = :request_status
                    WHERE request_id    = :request_id';
        $stmt   = $this->db->prepare($query);
        $stmt->execute([
            ':request_id'        => $request_id,
            ':request_status'    => $status
        ]); 
    }
    
}
