<?php 
require_once __DIR__.'/../../cabinet/models/schedulerModel.php';
require_once __DIR__.'/../../cabinet/models/postModel.php';
require_once __DIR__.'/../../cabinet/models/pollModel.php';
require_once __DIR__.'/../../cabinet/models/channelModel.php';
require_once __DIR__.'/../../core/libs/telegram/telegramTools.php';
require_once __DIR__.'/../../core/libs/logger.php';

class SchedulerTools {

	public function __construct() {
		$this->schedulerModel 	= new SchedulerModel();
		$this->postModel 		= new PostModel();
		$this->pollModel 		= new PollModel();
		$this->channelModel 	= new TelegramChannel();
		$this->telegram 		= new TelegramTools(TELEGRAM_BOT_TOKEN);
	}

	/**
	 * Публикует публикацию в Телеграм.
	 * @param 	int 	$post_id 	- Ид канала
	 * @return 	bool 	$status 	- TRUE | FALSE в зависимости от того опубликована запись в телеграм или нет 
	*/
	public function publishPost($post_id) {
		$status = false;
		$post = $this->postModel->getUserPostById((int)$post_id);
		if (!empty($post)) {
			$channel 			= $this->channelModel->getChannelById($post->channel_id);
			$telegram_chanel_id = $this->telegram->getTelegramChannelIdByUrl($channel->url);

			$response = $this->telegram->sendMessage($post->text, $telegram_chanel_id, $post->notification);
			$response = json_decode($response, true);
			if (isset($response['ok']) && true == $response['ok']) {
				$status = true;
				$this->postModel->updatePostAfterPublish(
						(int)$post_id, 
						$response['result']['message_id'], 
						$response['result']['chat']['id']
					);
				Logger::getInstance()->robotActionWriteToLog('postScheduler', 'publishPostSuccess', 'Публикация  с ид '.$post_id.' опубликована автоматически');
			} else {
				$this->postModel->updatePostStatus((int)$post_id, 'PUBLISHED_ERROR');
				Logger::getInstance()->robotActionWriteToLog('postScheduler', 'publishPostError', $response['error_code'].": ".$response['description']);
			}
		
		}
		return $status;
	}

	/**
	 * Метод удалит сообщение из чата в телеграм
	 * @param 	string 		$telegram_chanel_id - Ид канала в телеграм или ид чата
	 * @param 	int 		$message_id 		- Ид сообщения в телеграм
	 * @return 	string		json 				- JSON ответ от API
	*/
	public function deletePostFromChanel($telegram_chanel_id, $message_id) {
		$status 			= false;
		$response 			= $this->telegram->deleteMessage($telegram_chanel_id, (int)$message_id);
		$response 			= json_decode($response, true);
		if (isset($response['ok']) && true == $response['ok']) {
			$status = true;
			$this->postModel->updatePostStatus((int)$message_id, 'ACTIVE');
			Logger::getInstance()->robotActionWriteToLog('postScheduler', 'deletePostSuccess', 'Публикация  с ид '.$message_id.' удалена автоматически');
		} else {
			$this->postModel->updatePostStatus((int)$message_id, 'PUBLISHED_ERROR');
			Logger::getInstance()->robotActionWriteToLog('postScheduler', 'deletePostError', $response['error_code'].": ".$response['description']);
		}
		return $status = false;;
	}

	//----------------------------------------------------------------------------------------//

	/**
	 * Публикует опрос в Телеграм.
	 * @param 	string 	$telegram_chanel_id 	- Ид канала в телеграм 
	 * @param 	int 	$poll_id 				- Ид опроса 
	 * @return 	bool 	$status 				- TRUE | FALSE  
	*/
	public function publishPoll($telegram_chanel_id, $poll_id) {
		$status = false;
		$poll 	= $this->pollModel->getUserPollById((int)$poll_id);
		if (!empty($poll)) {
			$response = $this->telegram->sendPool(
													$telegram_chanel_id, 
													$poll->question,
													json_decode($poll->answers), 
													$poll->notification, 
													false, 
													false
												);
			$response  = json_decode($response, true);
			if (isset($response['ok']) && true == $response['ok']) {
				$status = true;
				$this->pollModel->updatePolltAfterPublish(
						(int)$poll_id, 
						$response['result']['message_id'], 
						$response['result']['chat']['id']
					);
				Logger::getInstance()->robotActionWriteToLog('pollScheduler', 'publishPollSuccess', 'Робот опубликовал опрос с ид '.$poll_id);
			} else {
				$this->pollModel->updatePollStatus((int)$poll_id, 'PUBLISHED_ERROR');
				Logger::getInstance()->robotActionWriteToLog('pollScheduler', 'publishPollError', $response['error_code'].": ".$response['description']);
			}
		}
		return $status;
	}

	/**
	 * Метод закроет опрос на телеграм канале
	 * @param 	string 	$telegram_chanel_id 	- Ид канала в телеграм
	 * @param 	int   	$poll_id 				- Ид опроса
	 * @return 	bool 	$status 				- TRUE | FALSE  
	*/
	public function stopPoll($telegram_chanel_id, $poll_id) {
		$status 			= false;
		$poll 				= $this->pollModel->getUserPollById((int)$poll_id);
		if (!empty($poll)) {
			$response 			= $this->telegram->stopPoll($telegram_chanel_id, $poll->message_id);
			$response 			= json_decode($response, true);
			if (isset($response['ok']) && true == $response['ok']) {
				$status = true;
				$this->pollModel->updatePollStatus((int)$poll_id, 'CLOSE');
				Logger::getInstance()->robotActionWriteToLog('pollScheduler', 'pollStopSuccess', 'Робот закрыл опрос на канале '.$poll->channel_id.' с ид '.$poll_id);
			} else {
				Logger::getInstance()->robotActionWriteToLog('pollScheduler', 'pollStopError', $response['error_code'].": ".$response['description']);
			}
		}
		return $status;
	}
	
	//----------------------------------------------------------------------------------------//

	/**
	 * Обновит статус задачи
	 * @param 	int 	$id 	- Ид задачи
	 * @return 	string 	$status - Желаемый статус
	*/
	public function updateTaskStatus($id, $status) {
		return $this->schedulerModel->updateTaskStatus($id, $status);
	}
}

?>