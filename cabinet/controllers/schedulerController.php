<?php
/**
 * Класс - контроллер для реализации взаимодействия пользовательского ввода
 * с БД и обратно. 
*/
require_once __DIR__.'/../models/schedulerModel.php';
require_once __DIR__.'/../models/channelModel.php';
require_once __DIR__.'/../../core/libs/telegram/telegramTools.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../../core/libs/paginator.php';
require_once __DIR__.'/../../core/coreTools.php';

class SchedulerController {

    public function __construct($action = null) {
        $this->user_id 		= (int)$_SESSION['uid']; 
        $this->schedulerModel 	= new SchedulerModel();
        $this->channelModel 	= new TelegramChannel();
        $this->page             = (isset($_GET['page'])) ? (int)$_GET['page'] : 0;
        $this->telegram 	= new TelegramTools(TELEGRAM_BOT_TOKEN);
        $this->tools            = new coreTools($this->user_id);
    }

    /**
     * Метод добавит расписание в БД
     *
     * @param 	int 	$id         - Ид айтема (поста, опроса, чего-либо)
     * @param 	string 	$type       - Тип айтема (POST для статей, POLL для опросов, MEDIA для медии)
     * @param 	string 	$date       - Дата публикации в формате yyyy-mm-dd
     * @param 	string  $time       - Время публикации в формате hh-mm (24 часовой формат)
     * @param 	string 	$action     - Действие, которое необходимо произвести
     * @param 	int 	$channel_id - Ссылка на канал в телеграм
    */
    public function addTask($id, $type, $date, $time, $action, $channel_id) {
        $task_id    = null;
        $channel    = $this->channelModel->getChannelById($channel_id);
        if (null != $channel->id) {
            $task_id = $this->schedulerModel->addTask($this->user_id, $channel->id, $id, $type, $date, $time, $action, $channel->telegram_chat_id);
            if (null != $task_id) {
                Logger::getInstance()->userActionWriteToLog('addTaskSuccess', 'Пользователь создал задание по расписанию');
            } else {
                Logger::getInstance()->userActionWriteToLog('addTaskError', 'Ошибка создания задачи: ошибка записи в БД');
            }
        }
        return $task_id;
    }
    
    /**
     * 
     * @param type $user_limit
     * @return boolean
     */
    public function isUserSchedulerLimitExceeded($user_limit) {
        $is_limit_exceeded = false; 
        $scheduler_task_count = $this->schedulerModel->getSchedulerTaskCount($this->user_id);
        if ($scheduler_task_count >= $user_limit) {
            $is_limit_exceeded = true;
        }
        return $is_limit_exceeded;
    }
    
    /**
     * Метод добавит расписание в БД. Создан как обработчик ajax запросов
     *
     * @param 	int 	$id                     - Ид айтема (поста, опроса, чего-либо)
     * @param 	string 	$type                   - Тип айтема (POST для статей, POLL для опросов, MEDIA для медии)
     * @param 	string 	$date                   - Дата публикации в формате yyyy-mm-dd
     * @param 	string  $time                   - Время публикации в формате hh-mm (24 часовой формат)
     * @param 	string 	$action                 - Действие, которое необходимо произвести
     * @param 	int 	$channel_id             - Ссылка на канал в телеграм
     * @return 	string 	json_encode($response) 	- JSON ответ
    */
    public function addTaskAjax($id, $type, $date, $time, $action, $channel_id) {
        $response   = [];
        $id         = $this->addTask($id, $type, $date, $time, $action, $channel_id);
        if  (null != $id) {
            $response['status'] 	= 'ok';
            $response['message'] 	= 'Расписание успешно сохранено!';
        } else {
            $response['status'] 	= 'error';
            $response['message'] 	= 'Не удалось сохранить расписание!';
        }
        echo json_encode($response);
    }

    /**
     * Метод обновит расписание в БД. Создан как обработчик ajax запросов
     *
     * @param 	int 	$id 	 				- Ид айтема (поста, опроса, чего-либо)
     * @param 	string 	$type 	 				- Тип айтема (POST для статей, POLL для опросов, MEDIA для медии)
     * @param 	string 	$date  					- Дата публикации в формате yyyy-mm-dd
     * @param 	string  $time  					- Время публикации в формате hh-mm (24 часовой формат)
     * @param 	string 	$action 				- Действие, которое необходимо произвести
     * @param 	string 	$channel_url                            - Ссылка на канал в телеграм
     * @return 	string 	json_encode($response) 	- JSON ответ
    */
    public function updateTaskAjax($id, $item_id, $type, $date, $time, $action, $channel_url) {
        $response = [];
        /*
        $id = $this->updateTask($id, $item_id, $type, $date, $time, $action, $channel_url);
        if  (null != $id) {
            $response['status'] 	= 'ok';
            $response['message'] 	= 'Расписание успешно сохранено!';
        } else {
            $response['status'] 	= 'error';
            $response['message'] 	= 'Не удалось сохранить расписание!';
        }
        echo json_encode($response);
         * 
         */
    }

    /**
     * Метод возвращает css класс в зависимости от статуса записи
     * @param 	string 	$status - Статус записи
     * @return 	string 	$class	- Css класс
    */
    public function getSchedulerTaskCSSClassByStatus($status) {
        $class = 'shedule_item';
        if ('DONE' == $status) {
                $class = 'active_item';
        } elseif ('FAIL' == $status) {
                $class = 'error_item';
        }
        return $class;
    }

    /**
     * Вернет ссылку на страницу с айтемом в зависимости от типа айтема
     * @param 	string 	 	$item_type 	- Тип айтема
     * @return 	string 		$item_url 	- Ссылка на айтем
    */
    public function getItemLinkByItemType($item_type) {
        $item_url = "/";
        if ('POST' == $item_type) {
            $item_url = '/cabinet/home.php?template=post&view=edit';
        } elseif ('POLL' == $item_type) {
            $item_url = '/cabinet/home.php?template=post&view=pollEdit';
        }
        return $item_url;
    }

    /**
     * Вернет человекочитаемый типайтема в зависимости от типа айтема
     * @param 	string 	 	$item_type 		- Тип айтема
     * @return 	string 		$hitem_type 	- Человекочитаемый тип айтем
    */
    public function getHumanityItemType($item_type) {
        $hitem_type = "";
        if ('POST' == $item_type) {
                $hitem_type = 'Публикация';
        } elseif ('POLL' == $item_type) {
                $hitem_type = 'Опрос';
        }
        return $hitem_type;
    }

    /**
     * Вернет человекочитаемый тип действия для айтема в зависимости от экшена в БД
     * @param 	string 	 	$item_type 		- Экшен айтема
     * @return 	string 		$hitem_type 	- Человекочитаемый тип айтем
    */
    public function getHumanityItemAction($item_action) {
        $hitem_type = "";
        if ('PUBLISH' == $item_action) {
                $hitem_type = 'Опубликовать';
        } elseif ('UNPUBLISH' == $item_action) {
                $hitem_type = 'Удалить';
        } elseif ('CLOSE' == $item_action) {
                $hitem_type = 'Закрыть опрос';
        }
        return $hitem_type;
    }

    /**
     * Вернет человекочитаемый типайтема в зависимости от типа айтема
     * @param 	string 	 	$item_type 		- Тип айтема
     * @return 	string 		$hitem_status 	- Человекочитаемый тип айтем
    */
    public function getHumanityItemStatus($item_status) {
        $hitem_status = "В очереди";
        if ('DONE' == $item_status) {
                $hitem_status = 'Выполнено!';
        } elseif ('FAIL' == $item_status) {
                $hitem_status = 'Ошибка!';
        }
        return $hitem_status;
    }

    /**
     * Вернет список расписаний задач для пользователя
     *
     * @return  obj - Объекст со списком заданий пользователя
    */
    public function getUserSchedulerList() {
        $options    = $this->tools->getPaginationsOffsets($this->page);
        $list = $this->schedulerModel->getUserSchedulerList($this->user_id, $options);
        foreach ($list as $key => &$item) {
                $item->chanel_url = $this->telegram->getTelegramChannelIdByUrl($item->chanel_url);
        }
        return $list;
    }

    /**
     * Удалит задание по ид
     *
     * @param 	int $id 	- Ид задания
    */
    public function deleteTask($id) {
        $this->schedulerModel->deleteTask($id);
        Logger::getInstance()->userActionWriteToLog('deleteTaskSuccess', 'Пользователь удалил задание с ид '.$id);
    }

    /**
    * 
    * @return type
    */
    public function getPaginations() {
        return Paginator::getPagination($this->schedulerModel->getSchedulerCount($this->user_id), $this->page);
    }
}