<?php 
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../models/rssModel.php';
require_once __DIR__.'/../../core/libs/paginator.php';
require_once __DIR__.'/../../core/coreTools.php';

class RSSController {

    public function __construct() {
        $this->user_id 	    = (int)$_SESSION['uid'];
        $this->rssmodel     = new RSS();
        $this->page         = (isset($_GET['page'])) ? (int)$_GET['page'] : 0;
        $this->tools        = new coreTools($this->user_id);
    }

    /**
     * Проверяет доступность файла RSS
     * @param   string  $url    - Ссылка на RSS фид
     * @return  bool            - TRUE|FALSE в зависимости от того можем добавить фид или нет
    */
    protected function checkUrlAvailable($url) {
        $curlInit = curl_init($url);
        curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlInit, CURLOPT_HEADER, true);
        curl_setopt($curlInit, CURLOPT_NOBODY, true);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curlInit);
        $info = curl_getinfo($curlInit);
        curl_close($curlInit);
        if (200 == $info['http_code']) {
            return true;
        }
        return false;
    }
    
    /**
     * 
     * @param type $user_limit
     * @return boolean
     */
    public function isUserRSSLimitExceeded($user_limit) {
        $is_limit_exceeded = false; 
        $rss_count = $this->rssmodel->getRSSCount($this->user_id);
        if ($rss_count >= $user_limit) {
            $is_limit_exceeded = true;
        }
        return $is_limit_exceeded;
    }
    
    /**
     * Скачивает RSS фид
     * @param       string  $url            - Ссылка на RSS фид
     * @return      string  $file_name      - Имя сохраненного файла на сервере
     */
    public function downloadRSS($url) {
        $user_dirs  = coreTools::getUserDirs($this->user_id);
        $file_name  = md5($url).".xml";
        $path       = __DIR__."/../..".$user_dirs["xml_path"]."/".$file_name;
        if (false == file_put_contents($path, file_get_contents($url))) {
            $file_name = null;
        }
        return $file_name;
    }

    /**
     * Валидирует RSS используя сервис http://feedvalidator.org
     * @param       string  $url        - Ссылка на RSS фид
     * @return      bool                - TRUE|FALSE в зависимости от того валиден RSS или нет
     */
    public function validateFeed( $url ) {
        $sValidator = 'http://feedvalidator.org/check.cgi?url=';
        if ($sValidationResponse = @file_get_contents($sValidator . urlencode($url))) {
            if (stristr($sValidationResponse, 'This is a valid RSS feed') !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Добавляет RSS фид
     * @param   string  $url        - Ссылка на RSS фид
     * @param   string  $comment    - Комментарий
    */
    public function addRSS($url, $comment) {
        try {
            // Проверим можем ли добавить RSS
            if (false === $this->checkUrlAvailable($url)) {
                Logger::getInstance()->userActionWriteToLog('addRSSError', 'Пользователю не удалось добавить ленту RSS: фаил не доступен '.$url);
                header('location: /cabinet/home.php?template=rss&view=add&message=addRSSError');
                exit;
            }
            // Проверка есть ли в ситсеммен такой RSS
            if (null != $this->rssmodel->checkRSSInDB($this->user_id, $url)) {
                Logger::getInstance()->userActionWriteToLog('addRSSIssetError', 'Не удалось сохранить RSS фид по ссылке '.$url." Фид уже добавлен");
                header('location: /cabinet/home.php?template=rss&view=add&message=addRSSIssetError');
                exit;
            }
            // Валидация RSS
            /*
            if (false == $this->validateFeed($url)) {
                Logger::getInstance()->userActionWriteToLog('addRSSIssetError', 'RSS '.$url.' не валиден!');
                header('location: /cabinet/home.php?template=rss&view=add&message=addRSSIssetValidationError');
                exit;
            }
            */

            $local_rss_url = $this->downloadRSS($url);
            if (null == $local_rss_url) {
                Logger::getInstance()->userActionWriteToLog('downloadRSSError', 'Не удалось сохоранить RSS фид по ссылке '.$url);
                header('location: /cabinet/home.php?template=rss&view=add&message=downloadRSSError');
                exit;
            }

            if (false === $this->rssmodel->addRSS($this->user_id, $url, $comment, $local_rss_url)) {
                Logger::getInstance()->userActionWriteToLog('addRSSError', 'Не удалось сохранить фаил RSS ленты '.$url);
                header('location: /cabinet/home.php?template=rss&view=add&message=addRSSError');
            } else {
                Logger::getInstance()->userActionWriteToLog('addRSSSuccess', 'Добавлен фаил RSS ленты: '.$url);
                header('location: /cabinet/home.php?template=rss&view=list&message=addRSSSuccess');
            }
        } catch (Exception $e) {
            Logger::getInstance()->userActionWriteToLog('addRSSError', 'Не удалось сохранить фаил RSS ленты '.$e->getMessage());
            header('location: /cabinet/home.php?template=rss&view=add&message=addRSSError');
        }
    }

    /**
     * Удаляет RSS фид
     * @param   int  $rss_id    - Ид RSS в системме
    */
    public function deleteRSS($rss_id) {
        $rss        = $this->rssmodel->getRSSById($rss_id);
        $user_dirs  = coreTools::getUserDirs($this->user_id);
        unlink(__DIR__."/../..".$user_dirs["xml_path"]."/".$rss->download_file);
        $this->rssmodel->deleteRSS($rss_id);
        Logger::getInstance()->userActionWriteToLog('deleteRSSSuccess', 'Пользователь удалил RSS '.$rss_id);
    }
    
    /**
     * 
     * @return type
     */
    public function getRSSList() {
        $options    = $this->tools->getPaginationsOffsets($this->page);
        return $this->rssmodel->getRSSList($this->user_id, $options);
    }
    
    /**
     * Получает список RSS лент в системме
     * @return   obj    - Объект с RSS лентами
    */
    public function getRSSListAll() {
        return $this->rssmodel->getRSSListAll($this->user_id);
    }

    /**
     * Формирует CSS класс в зависимости от статуса
     * @param   string  $state      - Статус
     * @return  string  $class      - CSS класс
     */
    public function getRuleCSSClassByStatus($state) {
        $class = 'active_item';
        if ('yes' == $state) {
            $class = 'active_item';
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
        return Paginator::getPagination($this->rssmodel->getRSSCount($this->user_id), $this->page);
    }
}

?>