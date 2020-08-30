<?php
class Payment {
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
     * Метод создаст новый платеж в БД
     * @param   int $sum        - сумма платежа
     * @param   int $user_id    - Ид пользователя
     * @return  int             - Ид нового платежа
     */
    public function createPayment($sum, $user_id) {
        $query = 'INSERT INTO payments
                    SET user_id     = :user_id,
                        sum         = :sum,
                       `status`     = "new"';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':user_id'      => (int)$user_id,
            ':sum'          => (int)$sum
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Метод обновит статус платежа
     * @param int       $payment_id     - Ид платежа
     * @param string    $status         - Статус (new, success, error)
     */
    public function updatePaymentStatus($payment_id, $status) {
        $query = 'UPDATE payments SET `status` = :status WHERE id = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':status'      => $status,
            ':id'          => (int)$payment_id
        ]);
    }
    
    /**
     * Вернет платеж пользователя по ид
     * @param   int     $payment_id     - Ид платежа
     * @return  obj                     - Объект с данными платежа
     */
    public function getUserPaymentById($payment_id) {
        $query = 'SELECT id, 
                        user_id, 
                        sum, 
                        status, 
                        date 
                    FROM payments 
                    WHERE id = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':id'          => (int)$payment_id
        ]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Метод завершит платеж пользователя и зачислит средства на счет
     * @param int   $user_id                - Ид пользователя в сервисе
     * @param int   $sum                    - Сумма пополнения
     * @param int   $payment_id             - Ид платежа в сервисе
     * @param int   $merchant_payment_id    - Ид платежа в платежной системе
     */
    public function completePayment($user_id, $sum, $payment_id, $merchant_payment_id) {
        try {
            $this->db->beginTransaction();
            // Обновим запись с платежем в БД
            $query = 'UPDATE payments 
                        SET `status`                = "success",
                            `merchant_payment_id`   = :merchant_payment_id 
                        WHERE id = :id';
            $stmt  = $this->db->prepare($query);
            $stmt->execute([
                ':merchant_payment_id'  => (int)$merchant_payment_id,
                ':id'                   => (int)$payment_id
            ]);
            
            // Зачислим средства на баланс пользователя
            $query = 'UPDATE users SET balance = balance + :balance WHERE id = :id';
            $stmt  = $this->db->prepare($query);
            $stmt->execute([
                ':balance'      => (int)$sum,
                ':id'          => (int)$user_id
            ]); 

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
        }
    }

    /**
     * Метод пополнит баланс пользователя
     * @param int   $user_id    - Ид пользователя в сервисе
     * @param int   $sum        - Сумма пополнения
     */
    public function increaseUserBalance($user_id, $sum) {
        $query = 'UPDATE users SET balance = balance + :balance WHERE id = :id';
        $stmt  = $this->db->prepare($query);
        $stmt->execute([
            ':balance'      => (int)$sum,
            ':id'          => (int)$user_id
        ]); 
    }
    
    /**
     * Метод вернет список платежей пользователя
     * @param   int $user_id    - Ид пользователя
     * @return  obj             - Список платежей пользователя
     */
    public function getUserPaymentsList($user_id, $options = []) {
        $query = 'SELECT id, 
                        user_id, 
                        sum, 
                        status, 
                        date 
                    FROM payments 
                    WHERE user_id = :user_id
                    ORDER BY id DESC';
        if (isset($options['page'])) {
            $query .= ' LIMIT '.$options['offset'].', '.ITEMS_ON_PAGE_LIMIT.' ';
        } else {
            $query .= ' LIMIT 0, '.ITEMS_ON_PAGE_LIMIT.' ';
        }
        $stmt  = $this->db->prepare($query);
        $stmt->execute([':user_id' => (int)$user_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getPaymentsCount($user_id) {
        $query = 'SELECT COUNT(*)
                    FROM payments
                    WHERE user_id = :user_id';
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }
}
?>