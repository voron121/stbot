<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/libs/logger.php';
require_once __DIR__.'/../../core/libs/paginator.php';
require_once __DIR__.'/../../core/coreTools.php';
require_once __DIR__ . '/../models/vkgroupsModel.php';
require_once __DIR__ . '/../models/vkgroupsImportModel.php';
require_once __DIR__ . '/../models/channelModel.php';

class VKGroupsImportController {
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
    
    public $rule_modes = [
        'mode' => [
            'text_only' => 'Только текст',
            'image_only' => 'Только изображение',
            'text_and_image' => 'Текст  и изображение',
            'albom_with_caption' => 'Текст и изображения (альбом)',
            'albom' => 'Только изображения (альбом)',
            'animation_with_caption' => 'Анимация(gif) вместе с текстом',
            'animation' => 'Анимация(gif) без текста'
        ],
        'text_mode' => [
            'all' => 'Публиковать текст полность',
            'cut' => 'Обрезать текст',
        ],
        'order' => [
            'ASC' => 'Старые сначала (импорт начиная с первой записи)',
            'DESC' => 'Новые сначала (импорт начиная с последней записи)',
        ],
        'url_mode' => [
            'skipp' => 'Игнорировать сообщения в которых есть ссылка',
            'cut' => 'Вырезать ссылки из текста',
            'ignore' => 'Ничего не делать'
        ],
    ];

    public function __construct($vk_user_id = null) {
        $this->user_id          = (int) $_SESSION['uid'];
        $this->vkgroupsmodel    = new VKGroups();
        $this->vkimportmodel    = new VKGroupsImport();
        $this->channel          = new TelegramChannel();
        $this->page             = (isset($_GET['page'])) ? (int)$_GET['page'] : 0;
        $this->tools            = new coreTools($this->user_id);
    }
    
    /**
     * 
     * @param type $user_limit
     * @return boolean
     */
    public function isUserVKRulesLimitExceeded($user_limit) {
        $is_limit_exceeded = false; 
        $import_rules_count = $this->vkimportmodel->getRulesCount($this->user_id, []);
        if ($import_rules_count >= $user_limit) {
            $is_limit_exceeded = true;
        }
        return $is_limit_exceeded;
    }

    /**
     * Сохранит правило импорта сообществ Вконтакте в БД
     * @param 	array 	$rule_set - Массив с параметрами импорта
     */
    public function addVKGroupImportRule($rule_set) {
        // TODO: добавить валидацию
        try {
            $rule_id = $this->vkimportmodel->addVKGroupImportRule($this->user_id, $rule_set);
            Logger::getInstance()->userActionWriteToLog('addVKGroupImportRuleSuccess', 'Пользователь добавил правило импорта с ид ' . $rule_id);
            header('location: /cabinet/home.php?template=vkgroupsimport&view=list&message=addVKGroupImportRuleSuccess');
        } catch (PDOException $e) {
            Logger::getInstance()->userActionWriteToLog('addVKGroupImportRuleError', $e->getMessage());
            header('location: /cabinet/home.php?template=vkgroupsimport&view=add&message=addVKGroupImportRuleError');
               
        }
    }

    /**
     * Редактирует правила импорта сообществ Вконтакте в БД
     * @param 	array 	$rule_set - Массив с параметрами импорта
     */
    public function editVKGroupImportRule($rule_set) {
        // TODO: добавить валидацию
        try {
            $rule_id = $this->vkimportmodel->editVKGroupImportRule($rule_set);
            $message = 'Пользователь изменил правило импорта: ';
            foreach ($rule_set as $item => $value) {
                $message .= $item . " => " . $value . " <br>";
            }
            Logger::getInstance()->userActionWriteToLog('editVKGroupImportRuleSuccess', $message);
            header('location: /cabinet/home.php?template=vkgroupsimport&view=list&message=editVKGroupImportRuleSuccess');
        } catch (PDOException $e) {
            Logger::getInstance()->userActionWriteToLog('editVKGroupImportRuleError', 'Ошибка добавления правила импорта: '.$e->getMessage());
            header('location: /cabinet/home.php?template=vkgroupsimport&view=edit&message=addVKGroupImportRuleError');
               
        }
    }
    
    /**
     * Удалилт папку с файлами для правила импорта на сервере
     * @param type $rule_id
     */
    protected function deleteRuleDir($rule_id) {
        $user_dirs  = coreTools::getUserDirs($this->user_id);
        $file_dir   = __DIR__."/../..".$user_dirs["vk_import_rules_path"] . $rule_id . '/';
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
     * Удалит правило испорта сообществ Вконтакте в БД
     * @param 	int 	$rule_id - Ид правила импорта
     */
    public function deleteVKImportRule($rule_id) {
        $this->deleteRuleDir($rule_id);
        $this->vkimportmodel->deleteVKImportRule($rule_id);
        Logger::getInstance()->userActionWriteToLog('deleteVKGroupImportRuleSuccess', 'Пользователь удалил правило импорта с ид ' . $rule_id);
    }

    /**
     * Изменит состояние правила испорта сообществ Вконтакте в БД
     * @param 	int 	$rule_id - Ид правила импорта
     */
    public function changeStateVKImportRule($state, $rule_id) {
        $this->vkimportmodel->changeStateVKImportRule($state, $rule_id);
        Logger::getInstance()->userActionWriteToLog('changeStateImportRuleSuccess', 'Пользователь изменил статус правила с ид ' . $rule_id . ' на ' . $state);
        header('location: /cabinet/home.php?template=vkgroupsimport&view=list&message=changeStateImportRuleSuccess');
    }

    /**
     * Вернет список правил импорта в ВК
     * @return 	obj 	$rules_list - массив с объектами правил импорта
     */
    public function getVKImportRulesList($options = []) {
        $options    = array_merge($options, $this->tools->getPaginationsOffsets($this->page));
        $rules_list = $this->vkimportmodel->getVKImportRulesList($this->user_id, $options);
        if (!empty($rules_list)) {
            foreach ($rules_list as $rule) {
                $channel                    = $this->channel->getChannelById($rule->channel_id);
                $rule->humanized_mode       = $this->rule_modes['mode'][$rule->mode];
                $rule->humanized_text_mode  = $this->rule_modes['text_mode'][$rule->text_mode];
                $rule->humanized_order      = $this->rule_modes['order'][$rule->order];
                $rule->humanized_url_mode   = $this->rule_modes['url_mode'][$rule->url_mode];
                $rule->vk_group             = $this->vkgroupsmodel->getUserVKGroupById($rule->group_id)->group_name;
                $rule->telegram_chanel      = (null != $channel->channel_title) ? $channel->channel_title : $channel->url ;
            }
        }
        return $rules_list;
    }

    /**
     * Вернет правило импорта в ВК по ид
     * param    int     $rule_id    - Ид правила импорта
     * @return 	obj 	$rule       - Объект правила импорта
     */
    public function getImportRuleById($rule_id) {
        return $this->vkimportmodel->getImportRuleById($rule_id);
    }

    /**
     * Метод возвращает css класс в зависимости от статуса записи
     * @param 	string 	$status - Статус записи
     * @return 	string 	$class	- Css класс
     */
    public function getRuleCSSClassByStatus($status) {
        $class = 'active_item';
        if ('on' == $status) {
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
    
    /**
     * 
     * @return type
     */
    public function getPaginations() {
        // TODO: зашить массив с опциями в контроллер и не получать извне
        $options = [];
        if (isset($_GET['group_id'])) {
            $options['group_id'] = strip_tags($_GET['group_id']);
        }
        if (isset($_GET['channel_id'])) {
            $options['channel_id'] = strip_tags($_GET['channel_id']);
        }
        return Paginator::getPagination($this->vkimportmodel->getRulesCount($this->user_id, $options), $this->page);
    }

}

?>