<?php

include(__DIR__ . '/../../config.php');

class DebitBalanceTools {
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
     * Получит список тарифов
     * @return obj
     */
    public function getTarifsList() {
        $query = 'SELECT subscription_id,
                         name, 
                         cost
                    FROM subscription_plans';
        $stmt = $this->db->query($query);
        $stmt->execute();
        $tarifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Посчитаем стоимость тарифа за день
        array_walk($tarifs, function(&$item) {
            $item["cost"] = round(($item["cost"] / 30), 2);
        });
        $tarifs = array_column($tarifs, null, "subscription_id");
        return $tarifs;
    }

    /**
     * Получит пользователей для списания баланса
     * @return obj
     */
    public function getActiveUsers() {
        $query = 'SELECT id,
                         login,
                         subscription_id,
                         balance
                    FROM users
                    WHERE active = "Yes"';
        $stmt = $this->db->query($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Обновит баланс пользователя
     * @param   int $cost
     * @param   int $user_id
     * @return  int
     */
    public function defundsUserBalance($cost, $user_id) {
        $query = 'UPDATE users 
                    SET balance = balance - '.$cost.' 
                    WHERE id    = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([ 
            ':user_id'  => $user_id
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Деактивирует пользователя
     * @param type $user_id
     * @return type
     */
    public function deactivateUser($user_id) {
        $query  = 'UPDATE users SET is_paid = "No" WHERE id = :user_id';
        $stmt   = $this->db->prepare($query);
        $stmt->execute([
            ':user_id'  => $user_id
        ]);
        return $this->db->lastInsertId();
    }
    
}

?>