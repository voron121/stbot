<?php

class TelegramChannelStat {

    protected $db;

    public function __construct() {
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }
 
    /**
     * Метод  получит канал пользователя по ид
     * @param 	int         $id 	- Ид канала
     * @param 	datetime    $period 	- Период выборки статистики
     * @return 	array                   - Массив с данными
     */
    public function getChannelStatById($id, $period) {
        $query = 'SELECT users_count,
                         timestamp
                FROM telegram_channels_stat
                WHERE channel_id = :channel_id';
        $query .= " AND ";
        if ('year' == $period) {
            $query .= "date_format(TIMESTAMP, '%Y') = date_format(now(), '%Y')";
        } else if ('month' == $period) {
            $query .= "date_format(TIMESTAMP, '%Y%m') = date_format(now(), '%Y%m')";
        } else if ('week' == $period) {
            $query .= "TIMESTAMP >= DATE_SUB(NOW(), INTERVAL 1 WEEk)";
        } else {
            $query .= "timestamp >= CURDATE()";
        }
        $query .= " ORDER BY timestamp ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':channel_id' => $id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
