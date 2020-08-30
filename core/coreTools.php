<?php 

class coreTools {
    public function __construct($user_id = null) {
        $this->user_id = $user_id;
    }
    
    /**
     * Разбирает данные, переданные в командной строке
     * @param  array $argv
     * @param  int $argc
     * @return array разобранные аргументы коммандной строки, ключ=значение, где ключ - ключ массива
    */
    public static function getCommandLineArgs($argv, $argc) {
        $cmd_args = array();
        for ($i = 1; $i < $argc; $i++) {
            $tmp = explode('=', $argv[$i]);
            if (isset($tmp[0], $tmp[1])) {
                    $cmd_args[$tmp[0]] = $tmp[1];
            }
        }
        return $cmd_args;
    }

    /**
     * Выводит сообщение с фоформлением
     * @param  string   $message    - Сообщение, которое нужно вывести
     * @param  string   $state      - Статус сообщения. По умолчанию null(белый)
     *                                Параметры: success(зеленый) | warning(желтый) | error(красный)
     */
    public static function printColorMessage($message, $state = null) {
        if ('success' == $state) {
            echo "\033[0;32m".$message."\033[0m\n";
        } elseif ('warning' == $state) {
            echo "\033[0;33m".$message."\033[0m\n";
        } elseif ('error' == $state) {
            echo "\033[0;31m".$message."\033[0m\n";
        } else {
            echo "\033[0;37m".$message."\033[0m\n";
        }
    }
    
    /**
     * Получит абсолютный путь файлу на сервере
     * @param   int     $post_id    - Ид записи
     * @return  string              - Путь к файлу
     */
    protected function getPostImageDir($user_id, $post_id) {
        $user_dirs  = $this->getUserDirs($user_id);
        return __DIR__."/..".$user_dirs['attachments_post_path'] . $post_id . '/';
    }
    
    /**
     * Получит путь к файлу с учтомо протокола http
     * @param   int   $post_id      - Ид записи
     * @return          string      - Путь к файлу
     */
    protected function getWebImageDir($user_id, $post_id) {
        $user_dirs  = $this->getUserDirs($user_id);
        return MAIN_HOST . $user_dirs['attachments_post_path'] . $post_id . '/';
    }
    
    /**
     * Получит массив с изображениями для записи.
     * @param   int     $user_id    - Ид пользователя в системме
     * @param   int     $post_id    - Ид записи
     * @return  array   $images     - Массив с ссылками на фаилы
     */
    public function getPostImages($user_id, $post_id) {
        $user_dirs  = $this->getUserDirs($user_id);
        $image_dir  = __DIR__."/../".$user_dirs['attachments_post_path'].$post_id."/"; 
        $images     = scandir($image_dir);
        $images     = array_filter($images, function($item) {
            return true == preg_match('/jpg|jpeg/', $item);
        }); 
        array_walk($images, function(&$item) use ($image_dir, $post_id, $images, $user_id) {
            $item = (count($images) > 1) ? $this->getWebImageDir($user_id, $post_id) . $item : $this->getPostImageDir($user_id, $post_id) . $item;
        });
        
        return array_values($images);
    }
    
    /**
     * Очистит папку с публикацией и удалил папку
     * @param   int     $user_id    - Ид пользователя в системме
     * @param   int     $post_id    - Ид записи
     */
    public function removePostImagesDir($user_id, $post_id) {
        $user_dirs  = $this->getUserDirs($user_id);
        $image_dir  = __DIR__."/../".$user_dirs['attachments_post_path'].$post_id."/"; 
        if (!file_exists($image_dir)) {
            return true;
        }
        $images     = scandir($image_dir);
        $images     = array_filter($images, function($item) {
            return true == preg_match('/jpg|jpeg|png|html/', $item);
        });
        
        if (!empty($images)) {
            foreach ($images as $image) {
                unlink($image_dir.$image);
            }
        }
        rmdir($image_dir);
    }
    
    /**
     * 
     * @param type $page
     * @return array
     */
    public function getPaginationsOffsets($page) {
        $options    = [];
        $offset     = 0;
        if ($page > 1) {
            $offset = ($page * ITEMS_ON_PAGE_LIMIT) - ITEMS_ON_PAGE_LIMIT;
        }
        if (isset($page) && $page > 0) {
            $options['page']    = $page;
            $options['offset']  = $offset;
        }
        return $options;
    }
    
    /**
     * 
     * @param type $user_id
     * @return string
     */
    public static function getUserDirs($user_id) {
        $user_dirs = [];
        $user_dirs["root_path"]                 = "/users_data/".$user_id;
        $user_dirs["xml_path"]                  = "/users_data/".$user_id."/user_xml/";
        $user_dirs["images_path"]               = "/users_data/".$user_id."/user_images/";
        $user_dirs["vk_accounts_path"]          = "/users_data/".$user_id."/user_images/vk_accounts/";
        $user_dirs["vk_public_images_path"]     = "/users_data/".$user_id."/user_images/vk_public_images/";
        $user_dirs["telegram_channels_path"]    = "/users_data/".$user_id."/user_images/telegram_channels_images/";
        $user_dirs["attachments_path"]          = "/users_data/".$user_id."/user_attachments/";
        $user_dirs["attachments_post_path"]     = "/users_data/".$user_id."/user_attachments/post/";
        $user_dirs["rss_import_rules_path"]     = "/users_data/".$user_id."/user_attachments/rss_import_rules/";
        $user_dirs["vk_import_rules_path"]      = "/users_data/".$user_id."/user_attachments/vk_import_rules/";
        return $user_dirs;
    }
    
    /**
     * Проверит нужно ли запустить робот
     * @param   string      $robot      - Имя робота для запуска
     * @param   int         $interval   - Интервал запуска робота в секундах
     * @return  boolean                 - TRUE|FALSE в зависимости от того нуно запустить робот или нет соответственно
     */
    public function mustStartRobot($robot, $interval) {
        $robot_lock = __DIR__."/../cron/locks/".$robot;
        if (false == file_exists($robot_lock)) {
            file_put_contents($robot_lock, "");
            return true;
        } else {
            $time = time() - filectime($robot_lock);
            if ($time >= $interval) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Проверить запущен ли робот для данного юзера или нет
     * 
     * @param   string      $robot      - Фаил робота
     * @param   string      $login      - Ллогин пользователя
     * @return  bool        $is_lock    - TRUE|FALSE в зависимости от того заблокирован сейчас фаил или нет
     */
    public static function checkRobotLock($robot, $login) { 
        static $lock;
        $is_lock    = true;
        $lock_file  = md5($robot." login=".$login).".txt";
        $lock       = fopen( __DIR__."/../cron/locks/".$lock_file, 'w+');
        
        if (flock($lock, LOCK_EX | LOCK_NB)) {
            $is_lock = false;
        }
        return $is_lock;
          
    }
    
    /**
     * Создаст фаил для записи в логи вывода из командной строки для робота
     * 
     * @param type $robot
     * @param type $user
     * @return string
     */
    public static function createRobotLogFile($robot, $user) {
        $robot_name = str_replace(".php", "", $robot);
        $log_file   = __DIR__."/../cron/logs/robot_".$robot_name."_date_". time()."_login_".$user.".txt";
        return $log_file;
    } 
    
    /**
     * 
     * @param type $dir
     * @return int
     */
    public static function getDirSize($dir_path) {
        $size   = 0;
        $path   = $dir_path;
	$dir    = opendir($path);
	
        while (false !== ($file = readdir($dir))) {
            if ($file == '.' || $file == '..') {
                continue;
            } elseif (is_dir($path . $file)) {
                $size += self::getDirSize($path . DIRECTORY_SEPARATOR . $file);
            } else {
                $size += filesize($path . DIRECTORY_SEPARATOR . $file);
            }
	}
	closedir($dir);
        return $size;
    }
}

?>