<?php 
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../../core/libs/paginator.php';
require_once __DIR__.'/../../core/coreTools.php';
require_once __DIR__.'/../../core/libs/VK/VK.php';
require_once __DIR__.'/../models/vkaccountModel.php';
require_once __DIR__.'/../models/vkgroupsModel.php';

class VKGroupsController {

    public function __construct($vk_user_id = null) {
        $this->user_id 		= (int)$_SESSION['uid']; 
        $this->vkmodel          = new VKAccount();
        $this->vkgroupsmodel 	= new VKGroups();
        $this->page             = (isset($_GET['page'])) ? (int)$_GET['page'] : 0;
        $this->tools            = new coreTools($this->user_id);
        $this->vk 		= null;
        if (null != $vk_user_id) {
            $access_token 	= $this->vkmodel->getVKAccountAccessToken($vk_user_id);
            $this->vk 		= new VK($vk_user_id, $access_token); 
        }
    }

    protected function getGroupDomainByURL($url) {
        $group_domain = str_replace('https://vk.com/', '', $url);
        return $group_domain;
    }

    public function getGroupById($group_id) {
        return $this->vkgroupsmodel->getUserVKGroupById($group_id);
    }
    
    /**
     * 
     * @param type $user_limit
     * @return boolean
     */
    public function isUserVKGroupsLimitExceeded($user_limit) {
        $is_limit_exceeded = false; 
        $groups_count = $this->vkgroupsmodel->getVKGroupsCount($this->user_id);
        if ($groups_count >= $user_limit) {
            $is_limit_exceeded = true;
        }
        return $is_limit_exceeded;
    }
    
    /**
     * Проверит может ли пользователь считать записи со стены в группе VK
     * @param 	int 	$vk_user_id 	- Ид пользователя в VK
     * @param 	string 	$group_domain	- Псевдоним группы (ид или псевдоним)
     * @return 	bool 					- True|False в зависимости от того есть у пользователя доступ к записям сообщества или нет
    */
    protected function checkVKUserPermission($vk_user_id, $group_domain) {
        $wall = $this->vk->getWall($group_domain);
        if (isset($wall['error'])) {
                return false;
        }
        return true;	
    }

    /**
     * Сохранит аватар пользователя на сервере
     * @param string    $photo          - ссылка на аватар
     * @param int       $vk_user_id     - Ид пользователя вк
     */
    public function saveVKAGroupPhoto($photo, $vk_group_id) {
        $user_dirs      = coreTools::getUserDirs($this->user_id);
        $user_photo_dir = __DIR__."/../..".$user_dirs["vk_public_images_path"];
        file_put_contents($user_photo_dir.$vk_group_id.".jpg", file_get_contents($photo));
    }
    
    /**
     * Сохраняет  группу пользователя VK (сообщество)
     * @param 	int 	$vk_user_id 	- Ид пользователя в VK
     * @param 	string 	$url			- Ссылка на группу VK
    */
    public function addVKGroup($vk_user_id, $url) {
        $group_domain = $this->getGroupDomainByURL($url);

        if (false == $this->checkVKUserPermission($vk_user_id, $group_domain)) {
            Logger::getInstance()->userActionWriteToLog('addVKGroupError', 'Ошибка добавления группы ВК');
            header('location: /cabinet/home.php?template=vkgroups&view=add&message=addVKGroupError');
        } else {
            $group_info = $this->vk->getGroupInfo($group_domain); 
            if (!empty($group_info)) {
                $this->vkgroupsmodel->addVKGroup($this->user_id, $vk_user_id, $group_info);
                $this->saveVKAGroupPhoto($group_info["photo_200"], $group_info["id"]);
                Logger::getInstance()->userActionWriteToLog('addVKGroupSuccess', 'Пользователь добавил группу ВК '.$group_info['name']);
                header('location: /cabinet/home.php?template=vkgroups&view=list&message=addVKGroupSuccess');
            } else {
                Logger::getInstance()->userActionWriteToLog('addVKGroupError', 'Ошибка сохранения группы ВК');
                header('location: /cabinet/home.php?template=vkgroups&view=add&message=addVKGroupSaveError');
            }
        }
    }

    /**
     * Удаляет группу пользователя VK (сообщество)
     * @param 	int 	$group_id 	- Ид пользователя в VK
    */
    public function deleteVKGroup($group_id) {
        $user_dirs  = coreTools::getUserDirs($this->user_id);
        $group      = $this->getGroupById($group_id);
        if (true == file_exists(__DIR__."/../..".$user_dirs["vk_public_images_path"].$group->group_id.".jpg")) {
            unlink(__DIR__."/../..".$user_dirs["vk_public_images_path"].$group->group_id.".jpg");
        }
        $this->vkgroupsmodel->deleteVKGroup($group_id);
        Logger::getInstance()->userActionWriteToLog('deleteVKGroupSuccess', 'Пользователь удалил группу ВК');
    }

    /**
     * Возвращает список групп Vk пользователя, подключенных в сервисе
     * @return 	obj - Объект с группами пользователя
    */
    public function getGroupsList() {
        $user_dirs  = coreTools::getUserDirs($this->user_id);
        $options    = $this->tools->getPaginationsOffsets($this->page);
        $vk_groups  = $this->vkgroupsmodel->getUserVKGroupsList($this->user_id, $options);
        array_walk($vk_groups, function(&$item) use ($user_dirs) {
            $item->group_photo = "/images/no_image.jpg";
            if (true == file_exists(__DIR__."/../..".$user_dirs["vk_public_images_path"].$item->group_id.".jpg")) {
                $item->group_photo = $user_dirs["vk_public_images_path"].$item->group_id.".jpg";
            }
            if (null == $item->record_count) {
                $item->record_count = 0;
            }
            if (null == $item->updated) {
                $item->updated = 'Еще не было';
            }
        });
        return $vk_groups;
    }

    /**
     * Возвращает весь список групп Vk пользователя, подключенных в сервисе
     * @return 	obj - Объект с группами пользователя
    */
    public function getGroupsListAll() {
        $vk_groups  = $this->vkgroupsmodel->getUserVKGroupsListAll($this->user_id);
        return $vk_groups;
    }

    /**
     * Возвращает список подключенных пользователем аккаунтов VK
     * @return 	array 	- Массив с данными пользователького аккаунта из БД
    */
    public function getUserVKAccountsList() {
        return $this->vkmodel->getUserVKAccountsList($this->user_id);
    }

    /**
    * 
    * @return type
    */
    public function getPaginations() {
        return Paginator::getPagination($this->vkgroupsmodel->getVKGroupsCount($this->user_id), $this->page);
    }
}

?>