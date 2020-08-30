<?php 
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../models/paymentModel.php';
require_once __DIR__.'/../../core/coreTools.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../../core/libs/paginator.php';

class paymentController {

    public function __construct($sum = null) {
        $this->user_id          = (isset($_SESSION['uid'])) ? (int)$_SESSION['uid'] : null;
        $this->payment_model    = new Payment();
        $this->sum              = (null != $sum) ? $sum : null;
        $this->page             = (isset($_GET['page'])) ? (int)$_GET['page'] : 0;
        $this->tools            = new coreTools($this->user_id);
    }
    
    /**
     * Метод создаст новый платеж в БД
     * @return int - Id нового платежа
     */
    protected function createPayment() {
        $payment_id             = $this->payment_model->createPayment($this->sum, $this->user_id);
        $_SESSION["payment_id"] = $payment_id;
        return $payment_id;
    }
    
    /**
     * Метод генерирует подпись платежа
     */
    protected function createPaymentSign() {
        $payment_id = $this->createPayment();
        return md5(FREE_CASSA_MERCHANT_ID.':'.$this->sum.':'.FREE_CASSA_SECRET1.':'.$payment_id);
    }

    /**
     * Метод отправит запрос в платежный шлюз для оплаты
     * @param   string   $sign - MD5 хеш сумма платежа, по умолчанию false 
     */
    public function createPaymentRequest($sign = false) {
        if (false == $sign) {
            $sign           = $this->createPaymentSign();
        }
        $payment_id     = $_SESSION["payment_id"];
        $payment_url    = "https://www.free-kassa.ru/merchant/cash.php?m=".FREE_CASSA_MERCHANT_ID."&oa=".$this->sum."&s=".$sign."&o=".$payment_id;
        if (false == $sign) {
            Logger::getInstance()->userActionWriteToLog('paymentRequest', 'Пользователь с ид ' . $this->user_id .' создал платеж с ид '.$payment_id.' на сумму ' . $this->sum);
        } else {
            Logger::getInstance()->userActionWriteToLog('paymentRequest', 'Пользователь с ид ' . $this->user_id .' продолжил платеж с ид '.$payment_id.' на сумму ' . $this->sum);
        }
        header('Location: '.$payment_url);
    }

    /**
     * Метод реализует продолжение платежа
     * @param   int   $payment_id           - Ид платежа в сервисе
     */
    public function paymentContinue($payment_id) {
        $user_payment = $this->payment_model->getUserPaymentById($payment_id);
        if (null == $user_payment) {
            Logger::getInstance()->userActionWriteToLog('paymentError', 'Ошибка повторного платежа с ид '.$payment_id.': отсутсвует ид платежа или ид платежа в системме мерчанта ');
            header('location: /cabinet/home.php?template=payment&view=payment&message=paymentError1');
            exit;
        }
        $_SESSION["payment_id"] = $payment_id;
        $sign = md5(FREE_CASSA_MERCHANT_ID.':'.$this->sum.':'.FREE_CASSA_SECRET1.':'.$payment_id);
        $this->createPaymentRequest($sign);
    }
    
    /**
     * Метод реализует платеж и обновление баланса пользователя
     * @param   int   $payment_id           - Ид платежа в сервисе
     * @param   int   $merchant_payment_id  - Ид платежа в системме мерчанта
     */
    public function paymentSuccess($payment_id, $merchant_payment_id ) {
        if (null == $payment_id || null == $merchant_payment_id) {
            Logger::getInstance()->userActionWriteToLog('paymentError', 'Ошибка платежа с ид '.$payment_id.': отсутсвует ид плажеа или ид платежа в системме мерчанта ');
            header('location: /cabinet/home.php?template=payment&view=payment&message=paymentError1');
        }

        $user_payment = $this->payment_model->getUserPaymentById($payment_id);
        
        if (null == $user_payment) {
            Logger::getInstance()->userActionWriteToLog('paymentError', 'Ошибка платежа с ид '.$payment_id.': платеж не найден в базе данных');
            header('location: /cabinet/home.php?template=payment&view=payment&message=paymentError2');
        }

        if ("success" == $user_payment->status) {
            Logger::getInstance()->userActionWriteToLog('paymentError', 'Ошибка платежа с ид '.$payment_id.': платеж уже проведен');
            header('location: /cabinet/home.php?template=payment&view=payment&message=paymentError3');
        }

        try {
            $this->payment_model->completePayment($user_payment->user_id, $user_payment->sum, $user_payment->id, $merchant_payment_id);
        } catch (PDOException $e) {
            Logger::getInstance()->userActionWriteToLog('paymentError', 'Ошибка зачисления платежа с ид '.$payment_id.': '.$e->getMessage());
        }
        
        Logger::getInstance()->userActionWriteToLog('paymentSuccess', 'Платеж с ид '.$payment_id.' завершен. Средства зачисленны на баланс пользователя', $user_payment->user_id);
        $_SESSION["payment_id"] = null;
        header('location: /cabinet/home.php?template=payment&view=payment&message=paymentSuccess');
    }
    
    /**
     * Метод изменит статус платежа на "error"
     */
    public function paymentError() {
        $payment_id = $_SESSION["payment_id"];
        $this->payment_model->updatePaymentStatus($payment_id, "error");
        $_SESSION["payment_id"] = null;
        header('location: /cabinet/home.php?template=payment&view=payment&message=paymentError');
    }
    
    /**
     * Метод вернет список платежей пользователя
     * @return obj  - Список платежей пользователя
     */
    public function getPaymentsList() {
        $options    = $this->tools->getPaginationsOffsets($this->page);
        return $this->payment_model->getUserPaymentsList($this->user_id, $options);
    }
    
    /**
     * Метод возвращает css класс в зависимости от статуса записи
     * @param 	string 	$status - Статус записи
     * @return 	string 	$class	- Css класс
    */
    public function getPaymentCSSClassByStatus($status) {
        $class = 'shedule_item';
        if ('success' == $status) {
            $class = 'active_item';
        } elseif ('error' == $status) {
            $class = 'error_item';
        } else {
            $class = 'shedule_item';
        }
        return $class;
    }
    
    /**
     * 
     * @return type
     */
    public function getPaginations() {
        return Paginator::getPagination($this->payment_model->getPaymentsCount($this->user_id), $this->page);
    }
}

?>