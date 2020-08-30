<?php
require_once __DIR__.'/../../core/coreTools.php';
require_once __DIR__.'/../../cabinet/models/userModel.php';

// Вспомогательный класс для загрузки файлов
class Uploader {
    const       IMAGES_COUNT            = 10;
    const       IMAGES_TOTAL_SIZE       = 10485760; // Размер в байтах
    protected   $file_types             = ['image/gif', 'image/jpeg' ];
    
    public function __construct() {}
    
    /**
     * Создаст папку для файлов поста
     * @param   int     $user_id        - Ид пользователя в системме
     * @param   int     $post_id        - Ид поста
     * @return  string  $images_dir     - Путь к папке
     */
    protected function createFileDir($user_id, $post_id) {
        $user_dirs  = coreTools::getUserDirs($user_id);
        $images_dir = __DIR__."/../..".$user_dirs["attachments_post_path"] . $post_id . '/';
        if (!file_exists($images_dir)) {
            if (true == mkdir($images_dir, 0755, true)) {
                file_put_contents($images_dir."/index.html", " ");
            }
        }
        return $images_dir;
    }
    
    /**
     * 
     * @param   int     $user_id        - Ид пользователя в системме
     * @param   int     $post_id        - Ид поста
     * @param   array   $files          - Массив с файлами к загрузке
     */
    public function uploadFiles($user_id, $post_id, $files) {
        $image_dir = $this->createFileDir($user_id, $post_id);
        for ($i = 0; count($files['name']) > $i; $i++) {
            $image_to_upload = $image_dir . basename($files['name'][$i]);
            move_uploaded_file($files['tmp_name'][$i], $image_to_upload);
        }
    }
    
    /**
     * Получит значение дисковой квоты для пользователя 
     * @param   int $user_id    - Ид пользователя
     * @return  int             - Размер дисеовой квоты пользователя в  байтах согласно тарифу
     */
    protected function getUserDiskQuota($user_id) {
        $user = new User($user_id);
        return $user->disc_space * 1024 * 1024;
    }
    
    /**
     * Получит размер загруженных фалов пользователем
     * @param   int $user_id    - Ид пользователя
     * @return  int $size       - Размер загруженных пользователем файлов в байтах
     */
    protected function getUserUploadedFilesSize($user_id) {
        $size       = 0;
        $user_dirs  = coreTools::getUserDirs($user_id);
        $size       = coreTools::getDirSize(__DIR__."/../../".$user_dirs['attachments_post_path']);
        return $size;
    }
    
    /** 
     * Проверяет файлы перед загрузкой. 
     * Загружаемые файлы должны быть поддерживаемого типа, общий размер не должен
     * привышать размер self::IMAGES_COUNT, для пользователя должно быть место на диске
     * согласно его тарифному плану
     * 
     * @param   array   $files      - Массив с файлами к загрузке
     * @throws  Exception
     */
    public function checkFiles($files, $user_id) {
        if (count($files['name']) > self::IMAGES_COUNT) {
            throw new Exception("Количество файлов привышает ограничение в ".self::IMAGES_COUNT." файлов!", 1);
        }
        
        foreach ($files['type'] as $key => $type) {
            if (!in_array($type, $this->file_types)) {
                throw new Exception("Тип файла ".$files['name'][$key]." не поддерживается!", 2);
            } 
        }
        
        $files_size = array_sum($files["size"]);
        if ($files_size >  $this->getUserDiskQuota($user_id)) {
            throw new Exception("Размер всех файлов привышает допустимый лимит!", 3);
        } 
        
        if ( $files_size + $this->getUserUploadedFilesSize($user_id) > $this->getUserDiskQuota($user_id) ) {
            throw new Exception("Превышен лимит дисковой квоты для вашего тарифного плана!", 4);
        }
        
        return true;
    }
    
}
?>