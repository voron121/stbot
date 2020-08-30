<?php
// Вспомогательный класс для валидации данных
class Validator {
	// Singleton
    
    private static $instance;
    protected $data;
    protected $response = [
		'status' 	=> 'ok',
		'message' 	=> 'ok'
	]; 


    public static function getInstance(): Validator
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	//-----------------------------------------------------------------------------------------------------------------//

    /**
     * Конструктор для инициализации проверки данных. На основе ключа "validation_type" в переданном массиве $data 
     * проверяется наличие метода. Метод состоит из ключевого слова "check"  и значение из массива  $data['validation_type']
     *
     * Если метод есть - вызовим его и передадим данные из $data['input_value'];
     * @param 	array 	$data 	- Массив данных, имеющий сл. структуру: 
	 *								[	 	
	 *									input_name' 		=> 'имя поля с данными', 
	 *						     		'input_value' 		=> 'значение из поля с данными', 
	 *						     		'validation_type' 	=> 'название метода для проверки данных без  ключевого слова check'
	 *								] 
     * @return 	string 	json 	- JSON строка с ответом от валидатора или пустая строка, если не удалось отправить данные на проверку.  
    */
    public function init($data) {
    	$response 	= false;
    	$name 		= 'check'.$data['validation_type'];
		
		if (method_exists('Validator', $name)) {
			$response 				= $this->$name($data['input_value']);
			$response['input_name']	= $data['input_name'];
		}

		return json_encode($response);
    }

	/**
	 * Проверит переданнную строку на соответствие стандарту урл 
	 * и на предмет того что урл соответсвует  урл телеграм канала
	 * @param 	string 	$url 			- Строка, предположительно урл
	 * @return 	array 	$this->response - Массив с данными валидации
	*/
	protected function checkTelegramUrl($url) {
		if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Ссылка не валидна!';
		} elseif (!preg_match("/^(https:\/\/t.me\/)/", $url)) {
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Ссылка не валидна!';
		} else {
			$this->response['status'] 	= 'ok';
			$this->response['message'] 	= '';
		}
                $this->response['status'] 	= 'ok';
			$this->response['message'] 	= '';
		//return $this->response;
	}

	/**
	 * Проверит переданнную строку на соответствие с троке комментария для телеграм канала
	 * @param 	string 	$comment		- Строка, предположительно комментарий
	 * @return 	array 	$this->response - Массив с данными валидации
	*/
	protected function checkTelegramChannelCommet($comment) {
		if ( mb_strlen($comment) > 255 ) {
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Длина текста привышает 255 символов!';
		} elseif($comment != strip_tags($comment)) { // наличие не обработанных символов 
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'В тексте присутствуют запрещенные символы!';
		}
		return $this->response;
	}

	/**
	 * Проверит переданнную строку на соответствие вопроса для опроса
	 * @param 	string 	$text			- Строка, предположительно комментарий
	 * @return 	array 	$this->response - Массив с данными валидации
	*/
	protected function checkTelegramPollQuestion($text) {
		if ( mb_strlen($text) > 255 ) {
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Длина текста привышает 255 символов!';
		} elseif (mb_strlen($text) < 1) { // наличие не обработанных символов 
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Введите название опроса!';
		} elseif ($text != strip_tags($text)) { // наличие не обработанных символов 
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'В тексте присутствуют запрещенные символы!';
		}
		return $this->response;
	}

	/**
	 * Проверит переданнную строку на соответствие ответу для опроса
	 * @param 	string 	$answer			- Строка, предположительно комментарий
	 * @return 	array 	$this->response - Массив с данными валидации
	*/
	protected function checkTelegramPollAnswer($answer) {
		if ( mb_strlen($answer) > 100 ) {
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Длина текста привышает 100 символов!';
		} elseif (mb_strlen($answer) < 1) { // наличие не обработанных символов 
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Укажите текст ответа!';
		} elseif ($answer != strip_tags($answer)) { // наличие не обработанных символов 
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'В тексте присутствуют запрещенные символы!';
		}
		return $this->response;
	}

	/**
	 * Проверит переданнную строку на соответствие набору ответов для опроса
	 * @param 	string 	$comment		- Строка, предположительно комментарий
	 * @return 	array 	$this->response - Массив с данными валидации
	*/
	protected function checkTelegramPollAnswers($answers) {
		if (!is_array(json_decode($answers,  true))) {
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Не корректная структура массива с ответами!';
		} elseif (count(json_decode($answers,  true)) < 2) {
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Минимум 2 варианта ответов!!';
		} elseif (count(json_decode($answers,  true)) > 10) {
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Превышено максимальное количество вариантов ответов!';
		}
		return $this->response;
	}


	/**
	 * Проверит переданнную строку на соответствие с троке комментария для телеграм канала
	 * @param 	string 	$comment		- Строка, предположительно комментарий
	 * @return 	array 	$this->response - Массив с данными валидации
	*/
	protected function checkTelegramMessageTitle($title) {
		if ( mb_strlen($title) > 1024 ) {
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Длина текста заголовка привышает 1024 символов!';
		} elseif($title != strip_tags($title)) { // наличие не обработанных символов 
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'В тексте заголовка присутствуют запрещенные символы!';
		}
		return $this->response;
	}

	/**
	 * Проверит переданнную строку на соответствие с троке комментария для телеграм канала
	 * @param 	string 	$comment		- Строка, предположительно комментарий
	 * @return 	array 	$this->response - Массив с данными валидации
	*/
	protected function checkTelegramMessageText($text) {
		if ( mb_strlen($text) > 4096 ) {
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'Длина текста привышает 4096 символов!';
		} elseif ( true == preg_match_all('/<(?!b|\/b|i|\/i|a|\/a|strong|\/strong|code|\/code|pre|\/pre|em|\/em|p|\/p)[^>]*>/', $text, $matches) ) {
			$this->response['status'] 	= 'error';
			$this->response['message'] 	= 'В тексте присутствуют запрещенные символы!';
		}
		return $this->response;
	}
}
?>