<?php 
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../models/rssImportModel.php';;

class RSSImportController {
    public $options = [];
    public $week_dictionary = ['Пн','Вт','Ср','Чт','Пт','Сб', 'Вс'];
    
    public $time_dictionary = [
        '00:00', '01:00', '02:00', '03:00', '04:00', '05:00'
        , '06:00', '07:00', '08:00', '09:00', '10:00', '11:00'
        , '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'
        , '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'
    ];
    
    protected $set = '[{"time":["0","0","0","0","0","0","0","0","1","1","1","0",'
            . '"0","0","0","0","0","0","1","1","1","0","0","0"]},{"time":["0",'
            . '"0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0",'
            . '"0","0","0","0","0","0","0"]},{"time":["0","0","0","0","0","0",'
            . '"0","0","1","1","1","0","0","0","0","0","0","0","1","1","1","0",'
            . '"0","0"]},{"time":["0","0","0","0","0","0","0","0","0","0","0",'
            . '"0","0","0","0","0","0","0","0","0","0","0","0","0"]},{"time":["'
            . '0","0","0","0","0","0","0","0","1","1","1","0","0","0","0","0",'
            . '"0","0","1","1","1","0","0","0"]},{"time":["0","0","0","0","0","0'
            . '","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0"'
            . ',"0","0"]},{"time":["0","0","0","0","0","0","0","0","0","0","0",'
            . '"0","0","0","0","0","0","0","0","0","0","0","0","0"]}]';
    
    public function __construct() {
        $this->user_id 	        = (int)$_SESSION['uid'];
        $this->rssimportmodel 	= new RSSImport();
    }

    public function addRSSImportRule($rule_set) {
        // TODO: добавить валидацию
        $rule_id = $this->rssimportmodel->addRSSImportRule($this->user_id, $rule_set);
        if (null != $rule_id) {
            Logger::getInstance()->userActionWriteToLog('addRSSImportRuleSuccess', 'Пользователь добавил правило импорта RSS с ид '.$rule_id);
            header('location: /cabinet/home.php?template=rssimport&view=list&message=addRSSImportRuleSuccess');
        } else {
            Logger::getInstance()->userActionWriteToLog('addRSSImportRuleError', 'Ошибка добавления правила импорта RSS');
            header('location: /cabinet/home.php?template=rssimport&view=add&message=addRSSImportRuleError');
        }
    }
    
    /**
     * 
     * @param type $user_limit
     * @return boolean
     */
    public function isUserRSSImportRulesLimitExceeded($user_limit) {
        $is_limit_exceeded = false; 
        $rss_rules_count = $this->rssimportmodel->getRSSImportRulesCount($this->user_id);
        if ($rss_rules_count >= $user_limit) {
            $is_limit_exceeded = true;
        }
        return $is_limit_exceeded;
    }
    
    /**
     * Редактирует правила импорта RSS в БД
     * @param 	array 	$rule_set - Массив с параметрами импорта
     */
    public function editRSSImportRule($rule_set) {
        // TODO: добавить валидацию
        $rule_id = $this->rssimportmodel->editRSSImportRule($this->user_id, $rule_set);
        if (null != $rule_id) {
            $message = 'Пользователь изменил правило импорта RSS: ';
            foreach ($rule_set as $item => $value) {
                $message .= $item . " => " . $value . " <br>";
            }
            Logger::getInstance()->userActionWriteToLog('editRSSImportRuleSuccess', $message);
            header('location: /cabinet/home.php?template=rssimport&view=list&message=editVKGroupImportRuleSuccess');
        } else {
            Logger::getInstance()->userActionWriteToLog('editRSSImportRuleError', 'Ошибка добавления правила импорта');
            header('location: /cabinet/home.php?template=rssimport&view=edit&message=addVKGroupImportRuleError');
        }
    }
    
    /**
     * Удалилт папку с файлами для правила импорта на сервере
     * @param type $rule_id
     */
    protected function deleteRuleDir($rule_id) {
        $user_dirs  = coreTools::getUserDirs($this->user_id);
        $file_dir   = __DIR__."/../..".$user_dirs["rss_import_rules_path"] . $rule_id . '/';
        $files      = scandir($file_dir);
        
        $files = array_filter($files, function($item) {
            return !in_array($item, [".","..","..","...","...."]);
        });
        
        if (!empty($files)) {
            foreach ($files as $file) {
                unlink($file_dir.$file);
            }
        }
        rmdir($file_dir);
    }

    /**
     * Удаляет правило импорта для RSS
     * @param   int  $rss_import_rule_id    - Ид правила импорта RSS в системме
    */
    public function deleteImportRuleRSS($rss_import_rule_id) {
        $this->deleteRuleDir($rss_import_rule_id);
        $this->rssimportmodel->deleteImportRuleRSS($rss_import_rule_id);
        Logger::getInstance()->userActionWriteToLog('deleteRSSImportRuleSuccess', 'Пользователь удалил правило импорта RSS с ид '.$rss_import_rule_id);
    }

    /**
     * Получает список правил импорта для RSS
     * @return   obj    - Объект с правилами импорта
    */
    public function getRSSImportRulesList() {
        return $this->rssimportmodel->getRSSImportRulesList($this->user_id);
    }

    /**
     * Вернет правило импорта в RSS по ид
     * param    int     $rule_id    - Ид правила импорта
     * @return 	obj 	$rule       - Объект правила импорта
     */
    public function getRSSImportRuleById($rule_id) {
        return $this->rssimportmodel->getRSSImportRuleById($rule_id);
    }
    
    /**
     * Формирует CSS класс в зависимости от статуса
     * @param   string  $state      - Статус
     * @return  string  $class      - CSS класс
     */
    public function getImportRuleCSSClassByStatus($state) {
        $class = 'active_item';
        if ('on' == $state) {
            $class = 'active_item';
        } else {
            $class = 'shedule_item';
        }
        return $class;
    }
    
    /**
     * Построит календарь для выбора дат запуска робота
     * 
     * @param   string      $values     - Параметры в контроле
     * @return  arrray      $calendar   - Массив с параметрами 
     */
    public function shedulerCalendarConstruct($values = null) {
        $calendar = [];
        $set      = json_decode($this->set, true);
        
        if (null != $values) {
            $values = json_decode($values, true);
        }
        for ($i = 0; $i < 7; $i++) {
            $calendar[$i]['day'] = $this->week_dictionary[$i];
            for($j = 0; $j < 24; $j++) {
                $calendar[$i]['time'][$j]['time_name']  = $this->time_dictionary[$j];
                $calendar[$i]['time'][$j]['time_value'] = isset($values[$i]['time'][$j]) ? $values[$i]['time'][$j] : $set[$i]['time'][$j];
            }
        } 
        return $calendar;
    }
}

?>