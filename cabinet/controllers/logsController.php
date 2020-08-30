<?php 
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/userController.php';
require_once __DIR__.'/../models/logsModel.php';
require_once __DIR__.'/../../core/libs/paginator.php';
require_once __DIR__.'/../../core/coreTools.php';

class logsController {

    public function __construct() {
        $this->user_id          = (int)$_SESSION['uid'];
        $this->user             = new UserController();
        $this->logsmodel        = new Logs();
        $this->user_login       = $this->user->login;
        $this->page             = (isset($_GET['page'])) ? (int)$_GET['page'] : 0;
        $this->tools            = new coreTools($this->user_id);
    }
    
    /**
     * Получает список логов
     * @return obj список логов
     */
    public function getLogsList() {
        $options    = $this->tools->getPaginationsOffsets($this->page);
        return $this->logsmodel->getLogsList($this->user_login, $options);
    }
    
    /**
     * 
     * @return type
     */
    public function getPaginations() {
        return Paginator::getPagination($this->logsmodel->getLogsCount($this->user_login), $this->page);
    }
}

?>