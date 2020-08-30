<?php
/**
 * Класс - контроллер для реализации взаимодействия пользовательского ввода
 * с БД и обратно. 
*/
require_once __DIR__.'/../models/postModel.php';
require_once __DIR__.'/../models/channelModel.php';
require_once __DIR__.'/../../core/libs/telegram/telegramTools.php';
require_once __DIR__.'/../../core/libs/validator.php';
require_once __DIR__.'/../../core/libs/paginator.php';
require_once __DIR__.'/../../core/libs/uploader.php';
require_once __DIR__.'/../../core/libs/logger.php';

class PostController {

    public function __construct() {
        $this->user_id          = (int)$_SESSION['uid']; 
        $this->page             = (isset($_GET['page'])) ? (int)$_GET['page'] : 0; 
        $this->postModel 	= new PostModel();
        $this->channelModel     = new TelegramChannel();
        $this->telegram 	= new TelegramTools(TELEGRAM_BOT_TOKEN);
        $this->tools            = new coreTools($this->user_id);
    }

    /**
     * Метод валидирует данные и возврящает булевый тип данных. 
     * Метод реализован для контроллера
     *
     * @param string 	$input_value 		- Идинтичикатор поля ввода, из которого проверяются данные
     * @param string 	$validation_type 	- Название метода, для валидации. Пример TelegramPollQuestion
     * @param string 	$input_name 		- Значение атрибутя name  для поля ввода. 
     * @param bool 	$is_valid 		- True | false если данные валидным | не валидны
    */	
    protected function validationData($input_value, $validation_type, $input_name = null) {
        $is_valid               = false;
        $validation_response 	= Validator::getInstance()->init(['input_name' => $input_name, 'input_value' => $input_value, 'validation_type' => $validation_type]);
        $validation_response 	= json_decode($validation_response, true);
        if (isset($validation_response['status']) && 'ok' == $validation_response['status']) {
            $is_valid = true;
        }
        return $is_valid;
    }
    
    /**
     * Метод добавит публикацию в БД
     * TODO: метод перегружен. Разбить на отдельно валидацию и отдельно запись в БД
     * 
     * @param 	int 	$channel_id		- Ид телеграм канала
     * @param 	string 	$title 			- Заголовок публикации
     * @param 	string 	$text 			- Текст публикации
     * @param 	array 	$files 			- Массив с файлами
     * @param 	string 	$notification           - Отправлять ли уведомления в чат
    */
    public function addPost($channel_id, $title, $text, $buttons, $files, $notification) {
        $title 	= strip_tags(trim($title));
        $type   = 'text';
        
        // Если есть файлы для загрузки - обработаем их
        if (!empty($files)) {
            $this->uploader = new Uploader();
            try {
                $this->uploader->checkFiles($files, $this->user_id);
                $type = 'album';
            } catch (\Exception  $e) {
                Logger::getInstance()->userActionWriteToLog('addPostError', 'Публикация не добавлена: Ошибка файлов ' . $e->getMessage());
                header('location: /cabinet/home.php?template=post&view=list&message=postAddFile'.$e->getCode().'Error');
                exit;
            }
        }
        
        $id = $this->postModel->addPost(
            $channel_id,
            $this->user_id,
            $text,
            $buttons,
            $title,
            $type,
            $notification
        );

        if (null == $id) {
            Logger::getInstance()->userActionWriteToLog('addPostError', 'Публикация не добавлена: ошибка записи в БД');
            header('location: /cabinet/home.php?template=post&view=list&message=postAddError');
        } else {
            if (!empty($files)) {
                $this->uploader->uploadFiles($this->user_id, $id, $files);
            }
            Logger::getInstance()->userActionWriteToLog('addPostSuccess', 'Пользователь создал публикацию с ид '.$id);
            header('location: /cabinet/home.php?template=post&view=list&message=postAddSuccess');
        }
    }
    
    /**
     * 
     * @param type $post_id
     * @return type
     */
    protected function getPostAttachments($post_id) {
        $files      = [];
        $user_dirs  = coreTools::getUserDirs($this->user_id);
        $images_dir = __DIR__."/../..".$user_dirs["attachments_post_path"] . $post_id . '/';
        if (file_exists($images_dir)) {
            $dh  = opendir($images_dir);
            while (false !== ($filename = readdir($dh))) {
                if (in_array($filename, [".","..","...","....", "index.html"])) {continue;}
                $files[] = [
                    "file_name" => $filename,
                    "file_path" => $images_dir.$filename,
                ];
            }
            sort($files);
        }
        return $files;
    }
    
    /**
     * 
     * @param type $file
     */
    public function ajaxDeletePostAttachment($file) {
        if (unlink($file)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Обновит запись с публикацией в БД
     * 
     * @param int       $post_id        - Ид публикации
     * @param int       $channel_id     - Ид канала
     * @param string    $title          - Заголовок
     * @param string    $text           - Текст публикации
     * @param array     $files          - Вложения
     * @param string    $notification   - Уведомлять ли пользователя (Yes|No)
     */
    public function updatePost($post_id, $channel_id, $title, $text, $buttons, $files, $notification) {
        $attachments    = $this->getPostAttachments($post_id);
        $type           = 'text';
        // TODO: проверить типы файлов
        if (!empty($files) || !empty($attachments)) {
            $type = 'album';
        }
        
        // Если есть файлы для загрузки - обработаем их
        if (!empty($files)) {
            if (count($attachments) + count($files["name"]) > 10) {
                Logger::getInstance()->userActionWriteToLog('addPostError', 'Количество вложений  привышает 10');
                header('location: /cabinet/home.php?template=post&view=edit&id='.$post_id.'&message=postAddFile1Error');
            }
            $this->uploader = new Uploader();
            try {
                $this->uploader->checkFiles($files);
            } catch (\Exception  $e) {
                Logger::getInstance()->userActionWriteToLog('addPostError', 'Публикация не добавлена: Ошибка файлов ' . $e->getMessage());
                header('location: /cabinet/home.php?template=post&view=edit&id='.$post_id.'&message=postAddFile'.$e->getCode().'Error');
                exit;
            }
        }
        
        $is_update = $this->postModel->updatePost($post_id, $channel_id, $text, $buttons, $title, $type, $notification);
         
        if (0 == $is_update) {
            Logger::getInstance()->userActionWriteToLog('addPostError', 'Публикация не добавлена: ошибка записи в БД');
            header('location: /cabinet/home.php?template=post&view=edit&id='.$post_id.'&message=postAddError');
        } else {
            if (!empty($files)) {
                $this->uploader->uploadFiles($this->user_id, $post_id, $files);
            }
            Logger::getInstance()->userActionWriteToLog('addPostSuccess', 'Пользователь обновил  публикацию с ид '.$post_id);
            header('location: /cabinet/home.php?template=post&view=list&message=postUpdateSuccess');
        }
    }
    
    /**
     * Публикует публикацию в Телеграм.
     * @param int $post_id - Ид записи в системме 
    */
    public function publishPost($post_id) {
        $post       = $this->postModel->getUserPostById((int)$post_id);
        $channel    = $this->channelModel->getChannelById($post->channel_id);
        if (empty($post)) {
            header('location: /cabinet/home.php?template=post&view=list&message=postPublishError');
            exit;
        }
        
        // Опубликуем пост в зависимости от его типа
        if ('text' == $post->type) { 
            // Если это просто текст
            $response = $this->telegram->sendMessage(
                $post->text, 
                $channel->telegram_chat_id, 
                ["disable_notification" => $post->notification, "reply_markup" => $post->buttons]
            );
        } elseif ('album' == $post->type) { 
            // Если это альбом или текст с картинкой
            $images = $this->tools->getPostImages($this->user_id, $post_id);
            if (empty($images)) {
                header('location: /cabinet/home.php?template=post&view=list&message=postPublishError');
            }  
            // Если одна картинка - опубликуем как картинку, иначе как медийный объект (альбом)
            if (count($images) == 1) {
                $response   = $this->telegram->sendImage(
                    $images[0], 
                    $channel->telegram_chat_id, 
                    ["disable_notification" => $post->notification, "caption" => $post->text, "reply_markup" => $post->buttons]
                );
            } else  {
                $media      = $this->telegram->createMediaObj($images, 'photo', $post->text);
                $response   = $this->telegram->sendMediaGroup(
                    $media, 
                    $channel->telegram_chat_id,  
                    ["disable_notification" => $post->notification] 
                );
            }
        }
        $response = json_decode($response, true);
        
        // Обработаем ответ от API
        if (isset($response['ok']) && true == $response['ok']) {
            if (isset($response['result'][0])) {
                $message_id = $response['result'][0]['message_id'];
                $chat_id    = $response['result'][0]['chat']['id'];
            } else {
                $message_id = $response['result']['message_id'];
                $chat_id    = $response['result']['chat']['id'];
            }
            $this->postModel->updatePostAfterPublish(
                (int)$post_id, 
                $message_id, 
                $chat_id
            );
            $this->tools->removePostImagesDir($this->user_id, $post_id);
            Logger::getInstance()->userActionWriteToLog('publishPostSuccess', 'Пользователь опубликовал пост с ид '.$post_id);
            header('location: /cabinet/home.php?template=post&view=list&message=postPublishSuccess');
        } else {
            $this->postModel->updatePostStatus((int)$post_id, 'PUBLISHED_ERROR');
            Logger::getInstance()->userActionWriteToLog('publishPostError', $response['error_code'].": ".$response['description']);
            header('location: /cabinet/home.php?template=post&view=list&message=postPublishError');
        } 
    }
    
    /**
     * Метод вернет список телеграм каналов пользователя из БД
     * @return obj - Объект со списком каналов пользователя в БД
    */
    public function getUserPostsList() {
        $options    = $this->tools->getPaginationsOffsets($this->page);
        $postsList  = $this->postModel->getUserPostsList($this->user_id, $options);
        array_walk($postsList, function(&$item) {
            $item->is_schedule 	= (null != $item->is_schedule) ? 'Yes' : 'No';
            $item->can_delete 	= ( strtotime("+2 day", strtotime($item->published)) > strtotime(date('Y-m-d h:m:i')) ) ? 'Yes' : 'No';
        });
        return $postsList;
    }
     
    /**
     * Метод вернет запись публикации пользователя по ид
     * @param 	int 	$id - Ид записи в БД
     * @return 		obj - Объект с постом пользователя
    */
    public function getUserPostById($id) {
        $post = $this->postModel->getUserPostById($id);
        $post->is_schedule 	= (null != $post->is_schedule) ? 'Yes' : 'No';
        $post->can_delete 	= ( strtotime("+2 day", strtotime($post->published)) > strtotime(date('Y-m-d h:m:i')) ) ? 'Yes' : 'No';
        $post_attachments = $this->getPostAttachments($id);
        if (!empty($post_attachments)) {
             $post->attachments = $post_attachments;
        }
        return $post;
    }

    /**
     * Метод удалит сообщение из чата в телеграм
     * @param 	string 		$telegram_chanel_id     - Ид канала в телеграм или ид чата
     * @param 	int 		$message_id 		- Ид сообщения в телеграм
     * @return 	string		json 			- JSON ответ от API
    */
    protected function deletePostFromChanel($telegram_chanel_id, $message_id) {
        $response   = $this->telegram->deleteMessage($telegram_chanel_id, (int)$message_id);
        $response   = json_decode($response, true);
        return $response;
    }

    /**
     * Метод удалит запись с чата в телеграм и из БД
     * @param int $id - Ид канала в БД
    */
    public function deletePost($id) {
        $post       = $this->getUserPostById((int)$id);
        $channel    = $this->channelModel->getChannelById($post->channel_id);
        // Удалим пост в телеграм , если он не старше 48 часов с момента публикации
        $channel    = $this->channelModel->getChannelById($post->channel_id);
        $response   = $this->deletePostFromChanel($channel->telegram_chat_id, $post->message_id);
        if (isset($response['ok']) && true == $response['ok']) {
            Logger::getInstance()->userActionWriteToLog('postDeleteFromChanelSuccess', 'Пользователь удалил пост с канала '.$post->channel_id.' с ид '.$id);
        } else {
            Logger::getInstance()->userActionWriteToLog('postDeleteFromChanelError', $response['error_code'].": ".$response['description']);
        }
        $this->postModel->deletePost($id, $this->user_id);
        $this->tools->removePostImagesDir($this->user_id, $id);
        Logger::getInstance()->userActionWriteToLog('deletePostSuccess', 'Пользователь удалил пост с ид '.$id);
    }
    
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
    
    /**
     * 
     * @return type
     */
    public function getPaginations() {
        return Paginator::getPagination($this->postModel->getPostCount($this->user_id), $this->page);
    }
}