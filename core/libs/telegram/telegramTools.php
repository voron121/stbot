<?php
/**
 * Класс реализует запросы к API Telegram 
 * Конструктор принимает токен $token
 */
include(__DIR__ . '/../APIRequestQueue.php');
class TelegramTools {

    public $options             = [];
    public $token               = '';
    private $allow_html_tags    = [
        "<p>", "<a>", "<br>", "<strong>", "<em>", "<pre>", "<code>", "<i>", "<b>"
    ];
    
    const API_URL       = 'https://api.telegram.org/bot';
    const FILE_API_URL  = 'https://api.telegram.org/file/bot';
    
    /**
     * @param string $token - Токен робота в телеграм
     */
    public function __construct($token) {
        $this->token = $token;
    }

    /**
     * Метод конструирует медия объект для дальнейшей публикации как медиа объект
     * @param 	array 	$items 		- Массив с медиа объектами
     * @param 	string 	$type 		- Тип медиа. По умолчанию photo
     * @param 	string 	$caption 	- Описание к объектам
     * @return 	string 	json 		- JSON ответ от API 
     */
    public function createMediaObj($items, $type = null, $caption = null) {
        $media_items    = [];
        $caption        = strip_tags($caption);
        
        $type = ('photo' != $type && in_array($type, ['video'])) ? $type : 'photo';
        foreach ($items as $item) {
            $media_items[] = ['type' => $type, 'media' => $item];
        }
        if (null != $caption && isset($media_items[0])) {
            $media_items[0]['caption'] = $caption;
        }
        return json_encode($media_items);
    }

    /**
     * Метод постит опрос
     * @param 	string 	$chat_id 		- Ид канала вида @chatName
     * @param 	string 	$question 		- Вопрос
     * @param 	array 	$options 		- Массив с вариантами ответов. До 10 в массиве
     * @param 	bool 	$notification           - Уведомлять ли пользователей
     * @param 	bool 	$is_closed 		- Закрытый опрос или нет
     * @param 	bool 	$id 			- Ид сообщения к которому можно прикрепить опрос
     * @return 	string 	json 			- JSON ответ от API 
     */
    public function sendPool($chat_id, $question, $options, $notification, $is_closed = false, $id = false) {
        return $this->setApiCall(
            'sendPoll ',
            [
                'chat_id' => $chat_id,
                'question' => $this->prepareMessageText($question),
                'options' => json_encode($options),
                'disable_notification' => ("No" == $notification || false == $notification) ? true : false,
                'is_closed' => (null != $is_closed) ? true : false,
        ]);
    }

    /**
     * Метод постит изображение в чат
     * @param 	string 	$image 			- Ссылка на изображение
     * @param 	mixed 	$chat_id 		- Ид канала вида @chatName
     * @param 	array 	$options                - Ассоциативный массив с параметрами: 
     *                                                   [ "disable_notification"       => bool, 
     *                                                     "caption"                    => string, 
     *                                                     "reply_markup"               => json 
     *                                                   ]
     * @return 	string 	json 			- JSON ответ от API
     */
    public function sendImage($image, $chat_id, $options = []) {
        $request = [
            'chat_id'               => $chat_id,
            'caption'               => '',
            'photo'                 => new CURLFile($image),
            'disable_notification'  => true,
            'parse_mode'            => 'HTML'
        ];
        if (isset($options['disable_notification'])) {
            $request['disable_notification'] = $options['disable_notification'];
        }
        if (isset($options['caption'])) {
            $caption = $this->prepareMessageText($options['caption']);
            $request['caption'] = $caption;
        }
        if (isset($options['reply_markup'])) {
            $request['reply_markup'] = $options['reply_markup'];
        }
        return $this->setApiCall('sendPhoto', $request);
    }

    /**
     * Метод постит гифки в чат
     * @param 	string 	$animation 		- Ссылка на изображение
     * @param 	string 	$chat_id 		- Ид канала вида @chatName
     * @param 	bool 	$notification           - Уведомлять ли пользователей.
     * @return 	string 	json 			- JSON ответ от API
     */
    public function sendAnimation($animation, $chat_id, $notification = "No", $caption = null) {
        $caption = $this->prepareMessageText($caption);
        return $this->setApiCall(
                        'sendAnimation',
                        [
                            'chat_id' => $chat_id,
                            'animation' => $animation,
                            'caption' => (null != $caption) ? $caption : '',
                            'disable_notification' => ("No" == $notification || false == $notification) ? true : false,
        ]);
    }

    /**
     * Метод постит видео в чат (не тестил)
     * @param 	string 	$video 			- Ссылка на видео или ид файла в телеграм
     * @param 	string 	$chat_id 		- Ид канала вида @chatName
     * @param 	bool 	$notification 	- Уведомлять ли пользователей.
     * @param 	array 	$options 		- Массив дополнительных параметров.
     * @return 	string 	json 			- JSON ответ от API 
     */
    public function sendVideo($video, $chat_id, $options, $notification = "No") {
        return $this->setApiCall(
                        'sendVideo',
                        [
                            'chat_id' => $chat_id,
                            'video' => $video,
                            'disable_notification' => ("No" == $notification || false == $notification) ? true : false,
                            'parse_mode' => 'HTML'
        ]);
    }

    /**
     * Метод постит аудио в чат (не тестил)
     * @param 	string 	$audio 			- Ссылка на видео или ид файла в телеграм
     * @param 	string 	$chat_id 		- Ид канала вида @chatName
     * @param 	bool 	$notification 	- Уведомлять ли пользователей.
     * @param 	array 	$options 		- Массив дополнительных параметров.
     * @return 	string 	json 			- JSON ответ от API 
     */
    public function sendAudio($audio, $chat_id, $options, $notification = "No") {
        return $this->setApiCall(
                        'sendAudio',
                        [
                            'chat_id' => $chat_id,
                            'audio' => $audio,
                            'disable_notification' => ("No" == $notification || false == $notification) ? true : false,
                            'parse_mode' => 'HTML'
        ]);
    }

    /**
     * Метод постит аудио в чат (не тестил)
     * @param 	obj 	$media 			- JSON с данными. ОТ 2 до 10 элементов
     * @param 	string 	$chat_id 		- Ид канала вида @chatName
     * @param 	bool 	$notification           - Уведомлять ли пользователей.
     * @return 	string 	json 			- JSON ответ от API 
     */
    public function sendMediaGroup($media, $chat_id, $options = []) { 
        $request = [
            'chat_id'               => $chat_id,
            'media'                 => $media,
            'disable_notification'  => true,
            'parse_mode'            => 'HTML'
        ];
        
        if (isset($options["disable_notification"])) {
            $request['disable_notification'] = $options["disable_notification"];
        }
        if (isset($options["disable_web_page_preview"])) {
            $request['disable_web_page_preview'] = $options["disable_web_page_preview"];
        } 
        
        return $this->setApiCall('sendMediaGroup', $request);
    }
    
    /**
     * Очень простой метод который влоб заменяет теги. 
     * TODO: использовать более продуманную обработку
     * @param type $message
     * @return type
     */
    public function prepareMessageText($message) {
        $message = strip_tags($message, implode(",",$this->allow_html_tags));
        $message = str_replace('<p>', '', $message);
        $message = str_replace('</p>', '&#10;', $message);
        $message = str_replace('<br>', '&#10;', $message);
        $message = str_replace('<strong>', '<b>', $message);
        $message = str_replace('</strong>', '</b>', $message);
        $message = str_replace('<em>', '<i>', $message);
        $message = str_replace('</em>', '</i>', $message);
        $message = str_replace('&nbsp;', '', $message);
        //echo htmlentities($message);
        //echo htmlentities($message);
        return $message;
    }
    
    /**
     * Метод постит сообщение в чат
     * @param 	string 	$message 		        - Текст сообщения
     * @param 	mixed 	$chat_id                        - Ид канала вида @chatName или ид чата
     * @param 	array 	$options                        - Ассоциативный массив с параметрами: 
     *                                                   [ "disable_notification"       => bool, 
     *                                                     "disable_web_page_preview"   => bool, 
     *                                                     "reply_markup"               => json 
     *                                                   ]
     * @return 	string 	json 			        - JSON ответ от API
     */
    //  $notification, $disable_web_page_preview = true, $keyboard = false
    public function sendMessage($message, $chat_id, $options = []) {
        $request = [
            'chat_id'                   => $chat_id,
            'text'                      => $this->prepareMessageText($message),
            'disable_notification'      => true,
            'disable_web_page_preview'  => true, 
            'parse_mode'                => 'HTML'
        ]; 
        if (isset($options["disable_notification"])) {
            $request['disable_notification'] = $options["disable_notification"];
        }
        if (isset($options["disable_web_page_preview"])) {
            $request['disable_web_page_preview'] = $options["disable_web_page_preview"];
        }
        if (isset($options["reply_markup"])) {
            $request['reply_markup'] = $options["reply_markup"];
        } 
        return $this->setApiCall('sendMessage', $request);
    }

    /**
     * Метод удалит текстовое сообщение из чата
     * @param 	string 	$chat_id        - Ид канала вида @chatName или идинтификатор чата (int)
     * @param 	string 	$message_id 	- Ид сообщения
     * @return 	string 	json            - JSON ответ от API 
     */
    public function deleteMessage($chat_id, $message_id) {
        return $this->setApiCall(
                        'deleteMessage',
                        [
                            'chat_id' => $chat_id,
                            'message_id' => $message_id
        ]);
    }

    /**
     * Метод остановит опрос
     * @param 	string 	$chat_id 		- Ид канала вида @chatName или идинтификатор чата (int)
     * @param 	string 	$message_id 	- Ид сообщения
     * @return 	string 	json 			- JSON ответ от API 
     */
    public function stopPoll($chat_id, $message_id) {
        return $this->setApiCall(
                        'stopPoll',
                        [
                            'chat_id' => $chat_id,
                            'message_id' => $message_id
        ]);
    }

    /**
     * Метод вернет список администраторов телеграм канала
     * @param 	string 	$chat_id 	- Ид канала вида @chatName
     * @return 	string 	json 		- JSON ответ от API 	
     */
    public function getChatAdministrators($chat_id) {
        return $this->setApiCall('getChatAdministrators', ['chat_id' => $chat_id]);
    }
    
    /**
     * Метод проверит есть ли  бот сервиса в телеграмм канале пользователя и имеет ли бот права администратора
     * @param 	string 	$channel_id 	- Ид канала вида @chatName
     * @return 	  	$chat_id 	- Ид чата канала или NULL в случае не удачи 	
     */
    public function verifyChannel($channel_id) {
        $chat_id    = null;
        $users_list = json_decode($this->getChatAdministrators($channel_id), true);

        if (isset($users_list['ok']) && true == $users_list['ok']) {
            // Попробуем опубликовать запись
            $test_message = $this->sendMessage('Ку-ку!', $channel_id, false, false);
            $test_message = json_decode($test_message, true);
            if (false == $test_message['ok']) {
                return $chat_id;
            } else {
                $message_id = $test_message['result']['message_id'];
                $chat_id    = $test_message['result']['chat']['id'];
                $this->deleteMessage($chat_id, $message_id);
            }
            
            foreach ($users_list['result'] as $user_info) {
                if ($user_info['user']['is_bot'] == 1 && $user_info['status'] == 'administrator' && $user_info['user']['username'] == TELEGRAM_BOT_USER_NAME) {
                    break;
                }
            }
        }
        return $chat_id;
    }

    /**
     *
     * 
     * @param type $chat_id
     * @return type
     */
    public function getChat($chat_id) {
        return $this->setApiCall(
            'getChat',
            [
                'chat_id' => $chat_id
            ]);
    }
    
    /**
     * 
     * @param type $file_id
     * @return type
     */
    public function getFile($file_id) {
        return $this->setApiCall(
            'getFile',
            [
                'file_id' => $file_id
            ]);
    }
    
    /**
     * 
     * @param type $channel_id
     * @param type $file_path
     */
    publiC function downloadFile($user_id,$channel_id, $file_path) {
        $user_dirs  = \coreTools::getUserDirs($user_id);
        $file       = __DIR__."/../../..".$user_dirs["telegram_channels_path"]."channel-photo-".$channel_id.".jpg";
        file_put_contents( $file, file_get_contents( self::FILE_API_URL . $this->token . "/" . $file_path ) );
    } 
    
    /**
     * 
     * @param type $chat_id
     * @return type
     */
    public function getChatMembersCount($chat_id) {
        return $this->setApiCall(
            'getChatMembersCount',
            [
                'chat_id' => $chat_id
            ]);
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function getUserProfilePhotos($user_id) {
        return $this->setApiCall(
            'getUserProfilePhotos',
            [
                'user_id' => $user_id
            ]);
    }
    
    /** 
     * 
     * @param type $chat_id
     * @param type $message_id
     * @param type $text
     * @param type $options
     * @return type
     */
    public function updateTextMessage($chat_id, $message_id, $text, $options = []) {
        $request = [
            'chat_id'       => $chat_id,
            'message_id'    => $message_id,
            'text'          => $text,
            'parse_mode' => "HTML"
        ];
        if (isset($options['reply_markup'])) {
            $request['reply_markup'] = $options['reply_markup'];
        }
        return $this->setApiCall('editMessageText', $request);
    }
    
    /**
     * 
     * @param type $chat_id
     * @param type $message_id
     * @param type $text
     * @param type $options
     * @return type
     */
    public function updateCaptionMessage($chat_id, $message_id, $text, $options = []) {
        $request = [
            'chat_id'       => $chat_id,
            'message_id'    => $message_id,
            'caption'       => $text,
            'parse_mode' => "HTML"
        ];
        if (isset($options['reply_markup'])) {
            $request['reply_markup'] = $options['reply_markup'];
        }
        return $this->setApiCall('editMessageCaption', $request);
    }
    
    /**
     * 
     * @return type
     */
    public function getCallBackData() {
        $request    = file_get_contents('php://input');
        //$request    = '{"update_id":746898898,"callback_query":{"id":"4232362623043364362","from":{"id":985423713,"is_bot":false,"first_name":"Jonny","last_name":"Cache","username":"mrJonnyCache","language_code":"ru"},"message":{"message_id":5685,"chat":{"id":-1001419429758,"title":"Dev","username":"tteesst2","type":"channel"},"date":1595619260,"text":"xfsdfdsfdsf","reply_markup":{"inline_keyboard":[[{"text":"\u041f\u0440\u043e\u0441\u0442\u043e \u0442\u0435\u043a\u0441\u0442","callback_data":"counter_1595619241"}],[{"text":"\u041f\u0440\u043e\u0441\u0442\u043e \u0442\u0435\u043a\u0441\u0442","url":"https://test.com/"}]]}},"chat_instance":"2795336694987150387","data":"counter_1595619241"}}';
        $request    = json_decode($request, true);
        return $request['callback_query'];
    }
    
    /**
     * Вернет ИД телеграм канала в формате @telegram_chanel по полному урл телеграм канала в БД сервиса
     *
     * @param 	string 	$url 	- Ссылка на легреам канала с протоколом https
     * @return 	string          - Ид телеграм канада вида @telegram_chanel
     */
    public function getTelegramChannelIdByUrl($url) {
        return str_replace('https://t.me/', '@', $url);
    }
    
    /**
     * Отправит запрос к API
     * @param type $method
     * @param type $data
     * @return type
     */
    protected function sendAPIRequest($method, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type:multipart/form-data"
        ]);
        curl_setopt($ch, CURLOPT_URL, self::API_URL . $this->token . "/" . $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/11.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $output = curl_exec($ch);
        return $output;
    }

    /**
     * Метод реализует запросы к API
     * @param 	string 	$method 	- Название метода к которому обращаемся
     * @param 	array 	$data 		- Массив с параметрами запроса
     * @return 	string 	$output 	- JSON ответ от API 
     */
    protected function setApiCall($method, $data) {
        $i                  = 0;
        $response           = null;
        $APIRequestQueue    = new APIRequestQueue('telegram');
        $APIRequestQueue->addRequest($data, $method);
        
        do {
            $i++; 
            $request    = $APIRequestQueue->getRequest();
            $response   = $this->sendAPIRequest($request->request_method, json_decode($request->request_data, true));
            $response   = json_decode($response, true);

            if (false == $response['ok']) { // Если запрос к API вернулся с ошибкой ограничения лимита
                if ($response['error_code'] == 429) {
                    echo "Must wait ".$response['parameters']['retry_after']."\r\n";
                    sleep($response['parameters']['retry_after']);
                    $response = null;
                }
            } else {
                $APIRequestQueue->updateRequestStatus($request->request_id, 'success');
            }
        } while(null == $response && $i < $APIRequestQueue->retry_limit);

        if (is_null($response)) {
            $APIRequestQueue->updateRequestStatus($request->request_id, 'fail');
            $response = '[{"ok":false,"error_code":0,"description":"Не удалось выполнить запрос после нескольких попыток"}]';
        } 
        return $response;
        
        
        
        
    }

}

?>