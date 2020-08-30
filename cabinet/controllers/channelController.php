<?php
/**
 * Класс - контроллер для реализации взаимодействия пользовательского ввода
 * с БД и обратно. 
*/
require_once __DIR__.'/../models/channelModel.php';
require_once __DIR__.'/../../core/libs/telegram/telegramTools.php';
require_once __DIR__.'/../../core/libs/validator.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../../core/libs/paginator.php';
require_once __DIR__.'/../../core/coreTools.php';

class ChannelController {

    public function __construct($action = null) {
        $this->channelModel     = new TelegramChannel();
        $this->user_id 		= (int)$_SESSION['uid'];
        $this->page             = (isset($_GET['page'])) ? (int)$_GET['page'] : 0;
        $this->telegram 	= new TelegramTools(TELEGRAM_BOT_TOKEN);
        $this->tools            = new coreTools($this->user_id);
    }

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
     * Метод  получит канал пользователя по урл канала
     * @param 	string 	$url 	- ссылка на канал
     * @return 	obj 			- Объек
    */
    protected function getChannelByUrl($url) {
        return $this->channelModel->getChannelByUrl(strip_tags($url));
    }

    /**
     * Метод  получит канал пользователя ид канала 
     * @param 	int 	$id 	- Ид канала 
     * @return 	obj 			- Объек
    */
    public function getChannelById($id) {
        return $this->channelModel->getChannelById((int)$id);
    }
    
    /**
     * 
     * @param type $url
     * @return type
     */
    protected function isPrivateWebUrl($url) {
        return preg_match("/(web.telegram.org\/#\/im\?p=)+(?!@)/", $url);
    }
    
    /**
     * 
     * @param type $url
     * @return type
     */
    protected function getPrivateChannelChatId($url) {
        preg_match('/(\d*+_)/', $url, $matches);
        $channel_id = -100 . str_replace('_', '', $matches[0]);
        return (int)$channel_id;
    }
    
    /**
     * 
     * @param type $url
     * @return type
     */
    protected function isWebChannelUrl($url) {
        return preg_match("/^(https:\/\/t.me)/", $url);
    }
    
    /**
     * 
     * @param type $url
     * @return type
     */
    protected function isTelegramChannelId($url) {
        return preg_match("/(@+\w*)/", $url);
    }

    /**
     * Проверит превышен ли лимит каналов у пользователя, согластно его тарифному плану
     * @param type $user_limit
     * @return boolean
     */
    public function isUserChannelsLimitExceeded($user_limit) {
        $is_limit_exceeded = false; 
        $user_channels_count = $this->channelModel->getChannelsCount($this->user_id);
        if ($user_channels_count >= $user_limit) {
            $is_limit_exceeded = true;
        }
        return $is_limit_exceeded;
    }
    
    /**
     * Метод добавит канал пользователя в БД
     * Метод получит ид чата канала по урл. Если канала приватный. На этом этапе так же
     * будет проверка корректности введенного урл.
     * Если ид канала или чата получен - попробуем проверить права на публикацию на канале.
     * Если все ок - добавим канала в БД и обновим фото.
     * 
     * @param string $url       - Ссылка на канал в телеграм
    */
    public function addChannel($url) {
        $channel_id = null;
        // Получение ид чата канала по урл в зависимости от типа канала
        if (true == $this->isPrivateWebUrl($url)) {
            $channel_id = $this->getPrivateChannelChatId($url);
        } elseif(true == $this->isWebChannelUrl($url)) {
            $channel_id = $this->telegram->getTelegramChannelIdByUrl($url);
        } elseif(true == $this->isTelegramChannelId($url)) {
            $channel_id = $url;
        } elseif(true == preg_match("/((t.me\/joinchat))/", $url)) {
            header('location: /cabinet/home.php?template=channel&view=list&message=channelAddErrorUrl');
        } else {
            header('location: /cabinet/home.php?template=channel&view=list&message=channelAddErrorUrl');
        }
        // Если зафейлили с урл 
        if (null == $channel_id) {
            header('location: /cabinet/home.php?template=channel&view=list&message=channelAddErrorChannelId');
        }
        // Если канал уже был добавлен
        if (null != $this->getChannelByUrl($url)) {
            Logger::getInstance()->userActionWriteToLog('addChannelError', 'Канал не добавлен: дубликат');
            header('location: /cabinet/home.php?template=channel&view=list&message=channelDublicateWarning');
        }
        // Получим ид ЧАТА канала и проверим права на публикацию
        $channel_chat_id = $this->telegram->verifyChannel($channel_id);
        
        if (null != $channel_chat_id) {
            $id = $this->channelModel->addChannel(strip_tags(trim($url)), $this->user_id, $channel_chat_id);
            if (null == $id) {
                header('location: /cabinet/home.php?template=channel&view=list&message=channelAddError');
            }
            
            Logger::getInstance()->userActionWriteToLog('addChannelSuccess', 'Пользователь успешно добавил канал телеграм');
            $this->channelModel->setChannelVerificationStatus($id);
            header('location: /cabinet/home.php?template=channel&view=list&message=verificationSuccess');
            
            try {
                // Обновим мету для канала. TODO: исправить пасту метода из тулзов робота
                $this->updateChannelPhoto($channel_id, $id);
            } catch (\Exception $e) {
                Logger::getInstance()->userActionWriteToLog('addChannelUpdateError', $e->getMessage());
                header('location: /cabinet/home.php?template=channel&view=list&message=verificationSuccess');
            }
        } else {
            Logger::getInstance()->userActionWriteToLog('addChannelAddError', 'Не удалось добавить канал по техническим причинам');
            header('location: /cabinet/home.php?template=channel&view=list&message=verificationError');
        }
    }

    /**
     * Метод вернет список телеграм каналов пользователя из БД
     * @return obj - Объект со списком каналов пользователя в БД
    */
    public function getUserTelegramChannelsList() {
        $user_dirs  = coreTools::getUserDirs($this->user_id);
        $options    = $this->tools->getPaginationsOffsets($this->page);
        $channels   = $this->channelModel->getUserTelegramChannelsList($this->user_id, $options);
        array_walk($channels, function(&$item) use ($user_dirs) {
            $item->channel_photo = "/images/no_image.jpg";
            if (true == file_exists(__DIR__."/../..".$user_dirs["telegram_channels_path"]."channel-photo-".$item->id.".jpg")) {
                $item->channel_photo = $user_dirs["telegram_channels_path"]."channel-photo-".$item->id.".jpg";
            }
            $item->channel_name = ('' == trim($item->channel_title) || null == $item->channel_title) ? $item->url : $item->channel_title;
        });
        return $channels;
    }
    
    /**
     * Метод вернет список подтвержденных телеграм каналов пользователя из БД
     * @return obj - Объект со списком каналов пользователя в БД
    */
    public function getApprovedUserTelegramChannelsList() {
        $channel_list 		= $this->channelModel->getUserTelegramChannelsListAll($this->user_id);
        $user_channel_list 	= array_filter($channel_list, function($item) {
            return $item->status == 'approved';
        });
        array_walk($user_channel_list, function(&$item) {
            $item->channel_name = (isset($item->channel_title) && "" != $item->channel_title) ? $item->channel_title : $item->url ;
        });
        return $user_channel_list;
    }
    
    /**
     * Возвращает весь список групп Vk пользователя, подключенных в сервисе
     * @return 	obj - Объект с группами пользователя
    */
    public function getTelegramChannelsListAll() {
        $channel_list  = $this->channelModel->getUserTelegramChannelsListAll($this->user_id);
        return $channel_list;
    }
 
    /**
     * Метод проверит наличие бота в канале пользователя  и обновит статус
     * канала в БД
     * @param int $id - Ид канала в БД
    */
    public function synchChannel($id) {
        $channel_id                 = $this->channelModel->getTelegramChannelId($id);
        $is_channel_verification    = $this->telegram->verifyChannel($channel_id);

        if (true == $is_channel_verification) {
            $this->channelModel->setChannelVerificationStatus($id);
            Logger::getInstance()->userActionWriteToLog('synchChannelSuccess', 'Успешно верифицирован канал телеграм');
            header('location: /cabinet/home.php?template=channel&view=list&message=verificationSuccess');
        } else {
            Logger::getInstance()->userActionWriteToLog('synchChannelError', 'Канал не прошел верификацию (не добавлен бот)');
            header('location: /cabinet/home.php?template=channel&view=list&message=verificationError');
        }
    }
    
        
    /**
     * Получит путь к фаилу фото канала
     * @param type $channel_chat_id
     * @return type
     * @throws \Exception
     */
    protected function getChannelPhoto($channel_chat_id) {
        $response       = $this->telegram->getChat($channel_chat_id);
        $channel_info   = json_decode($response, true);
        if (false == $channel_info['ok']) {
            throw new \Exception('Не удалось получить информацию о фото '.$channel_id.": ". $response->error_code." ".$response->description);
        }
        $image_id   = $channel_info['result']['photo']['small_file_id'];
        $file       = $this->telegram->getFile($image_id);
        $file_info  = json_decode($file, true);
        if (false == $file_info['ok']) {
            throw new \Exception('Не удалось получить фаил фото для канала '.$channel_id.": ". $file->error_code." ".$file->description);
        }
        $file_path = $file_info['result']['file_path'];
        return $file_path;
    }
    
    /**
     * Сохранит фото канала на сервере
     * @param type $channel_id
     * @param type $file
     * @return type
     */
    protected function saveChannelPhoto($channel_id, $file) {
        $this->telegram->downloadFile($this->user_id, $channel_id, $file);
    }
    
    /**
     * Обновит мета информацию о канале в БД
     * @param   string      $telegram_channel_id    - Ид канала телеграм вида @channel_name
     * @param   int         $channel_id             - Ид канала в сервисе
     */
    public function updateChannelPhoto($telegram_channel_id, $channel_id) {
        $channel_photo = $this->getChannelPhoto($telegram_channel_id);
        $this->saveChannelPhoto($channel_id, $channel_photo);
    }
    
    /**
     * Удалим канал и его изображение с сервера
     * @param int   $id - Bl канала в сервисе
     */
    public function deleteChannel($id) {
        $user_dirs  = coreTools::getUserDirs($this->user_id);
        if (true == file_exists(__DIR__."/../..".$user_dirs["telegram_channels_path"]."/channel-photo-".$id.".jpg")) {
            unlink(__DIR__."/../..".$user_dirs["telegram_channels_path"]."/channel-photo-".$id.".jpg");
        }
        $this->channelModel->deleteChannel($id);
        Logger::getInstance()->userActionWriteToLog('channelDeleteSuccess', 'Пользователь удалил телеграм канал с ид '.$id);
    }
    
    /**
     * 
     * @return type
     */
    public function getPaginations() {
        return Paginator::getPagination($this->channelModel->getChannelsCount($this->user_id), $this->page);
    }

}