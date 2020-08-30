<?php
/**
 * Класс - контроллер для реализации взаимодействия пользовательского ввода
 * с БД и обратно. 
*/
require_once __DIR__.'/../models/channelStatModel.php';
require_once __DIR__.'/../../core/libs/validator.php';
require_once __DIR__.'/../../core/libs/logger.php';

class ChannelStatController {
    
    protected $periods  = ['day', 'week', 'month', 'year'];
    protected $days     = [
        "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота", "Воскресенье"
    ]; 
    
    public function __construct($action = null) {
            $this->channelStatModel     = new TelegramChannelStat();
            $this->user_id 		= (int)$_SESSION['uid'];
    }
    
    /**
     * Сформирует статистику за  последние 7 дней
     * @param   array       $stat       - Массив с данными из БД
     * @return  array       $year_stat  - Массив сгруппированный по месяцам и со средним значением за каждый день
     */
    protected function getStatByWeek($stat) {
        $week_stat      = [];
        $prepared_state = [];
        array_walk($stat, function($item) use (&$week_stat) {
            $item_date = date("N", strtotime($item['timestamp']));
            $week_stat[$item_date][] = $item['users_count'];
        });
        array_walk($week_stat, function(&$item) {
            $item = max($item);
        });
        rsort($week_stat);
        foreach ($week_stat as $day => $stat) {
            $prepared_state[$this->days[$day]] = $stat;
        }
        return $prepared_state;
    }
    
    /**
     * Сформирует статистику за последний месяц
     * @param   array       $stat       - Массив с данными из БД
     * @return  array       $year_stat  - Массив сгруппированный по месяцам и со средним значением за каждый день
     */
    protected function getStatByMonth($stat) {
        $month_stat = [];
        array_walk($stat, function($item) use (&$month_stat) {
            $item_date = date("Y-m-d", strtotime($item['timestamp'])); 
            $month_stat[$item_date][] = $item['users_count'];
        });
        array_walk($month_stat, function(&$item) {
            $item       = max($item);
        });
        return $month_stat;
    }
    
    /**
     * Сформирует статистику за последний год
     * @param   array       $stat       - Массив с данными из БД
     * @return  array       $year_stat  - Массив сгруппированный по месяцам и со средним значением за каждый месяц
     */
    protected function getStatByYear($stat) {
        $year_stat = [];
        array_walk($stat, function($item) use (&$year_stat) {
            $item_date = date("Y F", strtotime($item['timestamp']));
            $year_stat[$item_date][] = $item['users_count'];
        }); 
        array_walk($year_stat, function(&$item) {
            $item = max($item);
             
        });
        return $year_stat;
    }
    
    /**
     * Вернет массив со статистикой для канала
     * @param   int         $channel_id     - Ид канала в сервисе
     * @param   string      $period         - Период выборки
     * @return  array       $stat           - Массив со статистикой
     */
    public function getChannelStat($channel_id, $period) {
        $stat_period    = (in_array($period, $this->periods)) ? $period : 'day';
        $channel_stat   = $this->channelStatModel->getChannelStatById($channel_id, $stat_period);
        if ('week' == $period) {
            $stat = $this->getStatByWeek($channel_stat);
        } elseif('month' == $period) {
            $stat = $this->getStatByMonth($channel_stat);
        } elseif('year' == $period) {
            $stat = $this->getStatByYear($channel_stat);
        } else {
            $stat = array_column($channel_stat, "users_count", "timestamp");
        }
        return $stat;
    }

}