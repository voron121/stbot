<?php 
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../core/libs/logger.php';
require_once __DIR__.'/../models/userModel.php';

class UserController {
    public $user = [];

    public function __construct($action = null) {
        $this->user_id = (int)$_SESSION['uid']; 
        if (null != $this->user_id) {
            $user = new User($this->user_id);
            $this->id                       = $user->id;
            $this->login                    = $user->login;
            $this->password                 = $user->password;
            $this->balance                  = $user->balance;
            $this->active                   = $user->active;
            $this->registration_date        = $user->registration_date;
            $this->last_interaction         = $user->last_interaction;
            $this->subscription_id          = $user->subscription_id;
            $this->subscription_name        = $user->name;
            $this->subscription_description = $user->description;
            $this->cost                     = $user->cost;
            $this->vkaccounts_count         = $user->vkaccounts_count;
            $this->channels_count           = $user->channels_count;
            $this->vk_publics_count         = $user->vk_publics_count;
            $this->vk_rule_count            = $user->vk_rule_count;
            $this->rss_count                = $user->rss_count;
            $this->rss_rule_count           = $user->rss_rule_count;
            $this->sheduler_task_count      = $user->sheduler_task_count;
            $this->disc_space               = $user->disc_space;
        }
    }
 
} 
?>