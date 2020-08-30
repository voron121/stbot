<?php
/**
 * Класс - контроллер для реализации взаимодействия пользовательского ввода
 * с БД и обратно. 
*/
require_once __DIR__.'/../models/pollModel.php';
require_once __DIR__.'/../models/channelModel.php';
require_once __DIR__.'/../../core/libs/telegram/telegramTools.php';
require_once __DIR__.'/../../core/libs/validator.php';
require_once __DIR__.'/../../core/libs/logger.php';

class PollController {

    public function __construct() {
        $this->pollModel 	= new pollModel();
        $this->channelModel     = new TelegramChannel();
        $this->user_id          = (int)$_SESSION['uid']; 
        $this->telegram 	= new TelegramTools(TELEGRAM_BOT_TOKEN);
    }

    /**
     * Метод валидирует данные и возврящает булевый тип данных. 
     * Метод реализован для контроллера
     *
     * @param string 	$input_value 		- Идинтичикатор поля ввода, из которого проверяются данные
     * @param string 	$validation_type 	- Название метода, для валидации. Пример TelegramPollQuestion
     * @param string 	$input_name 		- Значение атрибутя name  для поля ввода. 
     * @param bool 	$is_valid        	- True | false если данные валидным | не валидны
    */	
    protected function validationData($input_value, $validation_type, $input_name = null) {
        $is_valid 			= false;
        $validation_response 	= Validator::getInstance()->init(['input_name' => $input_name, 'input_value' => $input_value, 'validation_type' => $validation_type]);
        $validation_response 	= json_decode($validation_response, true);
        if (isset($validation_response['status']) && 'ok' == $validation_response['status']) {
                $is_valid = true;
        }
        return $is_valid;
    }

    /** 
     * Запишет в БД опрос
     *
     * @param 	int 	$channel_id             - Ид канала 
     * @param 	string 	$question 		- Вопрос (тайтл опроса)
     * @param 	string 	$answers 		- Json с ответами
     * @param 	bool 	$notification           - Отправлять ли уведомления в чат
    */
    public function addPoll($channel_id, $question, $answers, $notification) {
        $question 	= strip_tags(trim($question));
        $answers 	= array_filter($answers, function(&$item) {return '' != trim($item);});
        $answers 	= json_encode(array_values($answers));

        if (false == $this->validationData($question, 'TelegramPollQuestion') 
            ||  false == $this->validationData($answers, 'TelegramPollAnswers')) {
            Logger::getInstance()->userActionWriteToLog('addPollError', 'Опрос не добавлен: ошибка валидации полей');
            header('location: /cabinet/home.php?template=poll&view=list&message=pollError');
        } else { 
            $id = $this->pollModel->addPoll(
                $channel_id,
                $this->user_id,
                $question,
                $answers,
                $notification
            ); 
            if (null == $id) {
                Logger::getInstance()->userActionWriteToLog('addPollError', 'Опрос не добавлен: ошибка записи в БД');
                header('location: /cabinet/home.php?template=poll&view=list&message=pollAddError');
            } else {
                Logger::getInstance()->userActionWriteToLog('addPollSuccess', 'Пользователь создал опрос с ид '.$id);
                header('location: /cabinet/home.php?template=poll&view=list&message=pollAddSuccess');
            }
        }
    }

    /**
     * Публикует опрос в Телеграм.
     * @param int $poll_id - Ид опроса 
    */
    public function publishPoll($poll_id) {
        $poll = $this->pollModel->getUserPollById((int)$poll_id);
        if (!empty($poll)) {
            $channel 			= $this->channelModel->getChannelById($poll->channel_id);
            $response 			= $this->telegram->sendPool($channel->telegram_chat_id, $poll->question,json_decode($poll->answers), $poll->notification, false, false);
            $response 			= json_decode($response, true);
            if (isset($response['ok']) && true == $response['ok']) {
                $this->pollModel->updatePolltAfterPublish(
                    (int)$poll_id, 
                    $response['result']['message_id'], 
                    $response['result']['chat']['id']
                );
                Logger::getInstance()->userActionWriteToLog('publishPollSuccess', 'Пользователь опубликовал опрос с ид '.$poll_id);
                header('location: /cabinet/home.php?template=poll&view=list&message=pollPublishSuccess');
            } else {
                $this->pollModel->updatePollStatus((int)$poll_id, 'PUBLISHED_ERROR');
                Logger::getInstance()->userActionWriteToLog('publishPollError', $response['error_code'].": ".$response['description']);
                header('location: /cabinet/home.php?template=poll&view=list&message=pollPublishError');
            }
        }
    }

    /**
     * Метод вернет список опросов пользователя из БД
     * @return obj - Объект со списком каналов пользователя в БД
    */
    public function getUserPollsList() {
        $pollsList = $this->pollModel->getUserPollsList($this->user_id);
        array_walk($pollsList, function(&$item) {
            $item->answers = json_decode($item->answers, true);
            $item->is_schedule 	= (null != $item->is_schedule) ? 'Yes' : 'No';
            $item->can_delete 	= ( strtotime("+2 day", strtotime($item->published)) > strtotime(date('Y-m-d h:m:i')) ) ? 'Yes' : 'No';
        });
        return $pollsList;
    }

    /**
     * Метод вернет опрос пользователя по ид
     * @param 	int 	$id - ИД записи в БД
     * @return  obj         - Объект с постом пользователя
    */
    public function getUserPollById($id) {
        $poll               = $this->pollModel->getUserPollById($id);
        $poll->answers 	= json_decode($poll->answers, true);
        $poll->is_schedule 	= (null != $poll->is_schedule) ? 'Yes' : 'No';
        $poll->can_delete 	= ( strtotime("+2 day", strtotime($poll->published)) > strtotime(date('Y-m-d h:m:i')) ) ? 'Yes' : 'No';
        return $poll;
    }

    /**
     * Метод удалит сообщение с опросом из чата в телеграм
     * @param 	string 		$telegram_chanel_id - Ид канала в телеграм или ид чата
     * @param 	int 		$message_id         - Ид сообщения в телеграм
     * @return 	string		json                - JSON ответ от API
    */
    protected function deletePollFromChanel($telegram_chanel_id, $message_id) {
        $response   = $this->telegram->deleteMessage($telegram_chanel_id, (int)$message_id);
        $response   = json_decode($response, true);
        return $response;
    }

    /**
     * Метод удалит опрос пользователя в БД
     * @param int $id - Ид канала в БД
    */
    public function deletePoll($id) {
        $poll = $this->getUserPollById((int)$id);
        // Удалим пост в телеграм , если он не старше 48 часов с момента публикации
        if ('Yes' == $poll->can_delete) {
            $channel        = $this->channelModel->getChannelById($poll->channel_id);
            $response       = $this->deletePollFromChanel($channel->telegram_chat_id, $poll->message_id);
            if (isset($response['ok']) && true == $response['ok']) {
                Logger::getInstance()->userActionWriteToLog('pollDeleteFromChanelSuccess', 'Пользователь удалил опрос с канала '.$poll->channel_id.' с ид '.$id);
            } else {
                Logger::getInstance()->userActionWriteToLog('pollDeleteFromChanelError', $response['error_code'].": ".$response['description']);
            }
        }
        // Удалим запись в БД
        $this->pollModel->deletePoll($id, $this->user_id);
        Logger::getInstance()->userActionWriteToLog('deletePollSuccess', 'Пользователь удалил опрос с ид '.$id);
        //header('location: /cabinet/home.php?template=poll&view=list&message=pollDeleteSuccess');
    }

    /**
     * Метод закроет опрос на телеграм канале
     * @param 	int   $id - Ид опроса
    */
    public function stopPoll($id) {
        $poll           = $this->getUserPollById((int)$id);
        $channel        = $this->channelModel->getChannelById($poll->channel_id);
        $response       = $this->telegram->stopPoll($channel->telegram_chat_id, $poll->message_id);
        $response       = json_decode($response, true);
        if (isset($response['ok']) && true == $response['ok']) {
            $this->pollModel->updatePollStatus((int)$id, 'CLOSE');
            Logger::getInstance()->userActionWriteToLog('pollStopSuccess', 'Пользователь закрыл опрос на канале '.$poll->channel_id.' с ид '.$id);
            header('location: /cabinet/home.php?template=poll&view=list&message=pollStopSuccess');
        } else {
            Logger::getInstance()->userActionWriteToLog('pollStopError', $response['error_code'].": ".$response['description']);
            header('location: /cabinet/home.php?template=poll&view=list&message=pollStopError');
        }
    }

    //---------------------------------------------------------------------------------------------------//

    /**
     * Метод возвращает css класс в зависимости от статуса записи
     * @param 	string 	$status - Статус записи
     * @return 	string 	$class	- Css класс
    */
    public function getPostCSSClassByStatus($status) {
        $class = 'published_item';
        if ('ACTIVE' == $status) {
                $class = 'active_item';
        } elseif ('SHEDULE' == $status) {
                $class = 'shedule_item';
        } elseif ('PUBLISHED_ERROR' == $status) {
                $class = 'error_item';
        }
        return $class;
    }

    /**
     * Метод возвращает html бейдж в зависимости от статуса публикации
     * @param 	string 	$status - Статус записи
     * @return 	string 	$badge	- HTML бейдж
    */
    public function getPostStatusBadge($status) {
        $badge = '<span class="label'; // 4
        if ('ACTIVE' == $status) {
            $badge .= ' label-default"><i class="glyphicon glyphicon-ok"></i> <span>Активно</span>';
        } elseif ('SHEDULE' == $status) {
            $badge .= ' label-default"><i class="glyphicon glyphicon-time"></i> <span>Ожидает</span>';
        } elseif ('PUBLISHED_ERROR' == $status) {
            $badge .= ' label-default"><i class="glyphicon glyphicon-warning-sign"></i> <span>Ошибка публикации</span>';
        } elseif ('PUBLISHED' == $status) {
            $badge .= ' label-default"><i class="glyphicon glyphicon-ok-sign"></i> <span>Опубликовано</span>';
        }
        $badge .= '</span>';
        return $badge;
    }

    /**
     * Метод вернет ид телеграм канала в формате @telegram_chanel по полному урлу ссылки на канал
     * @param 	string 	$url    - Ссылка на телеграм канал формата https://link
     * @return 	string          - Ид телеграм канала вида @telegram_chanel
    */
    public function getTelegramChannelIdByUrl($url) {
        return $this->telegram->getTelegramChannelIdByUrl($url);
    }
 
}