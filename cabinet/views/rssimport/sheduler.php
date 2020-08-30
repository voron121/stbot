<?php 
    require_once __DIR__ . '/../../controllers/rssImportController.php';
    $rssController      = new RSSImportController();
    $sheduler           = null;
    if (isset($_GET['rule_id'])) {
        $rss_rule           = $rssController->getRSSImportRuleById((int)$_GET['rule_id']);
        $sheduler           = isset($rss_rule->sheduler) ? $rss_rule->sheduler : null;
    }
    $calendar           = $rssController->shedulerCalendarConstruct($sheduler);
?>


<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Расписание запуска правила</h4>
      </div>
      <div class="modal-body">
        <div class="input-group col-md-12">
            <h3>Выберите расписание</h3>
            <div class="sheduler_wraper">
                <?php foreach($calendar as $day => $item):?>
                    <div class="sheduler_day" data-daynumber="<?=$day;?>">
                        <div class="sheduler_day_text">
                            <?=$item['day'];?>
                            <input type="checkbox" class="dayb_checkbox" data-dayb="<?=$day;?>">
                        </div>
                        <?php foreach($item['time'] as $key => $time):?> 
                            <div class="sheduler_time <?=(1 == $time['time_value']) ? "sheduler_selected_time" : "";?>" data-value="<?=$time['time_value'];?>" >
                                <span><?=(1 == $time['time_value']) ? "+" : "-";?></span>
                                <input  
                                    type="hidden" 
                                    name="dt[<?=$day;?>][time][<?=$key;?>]"
                                    data-day="<?=$day;?>"
                                    data-time="<?=$key;?>"
                                    value="<?=$time['time_value'];?>"
                                    class="time_checkbox"
                                />
                            </div>
                        <?php endforeach;?>
                    </div>
                <?php endforeach;?>
                <div class="sheduler_time_text_wraper">
                    <?php foreach ($rssController->time_dictionary as $key => $time):?> 
                        <div class="sheduler_time_text">
                            <input type="checkbox" class="timeb_checkbox" data-timeb="<?=$key;?>">
                            <span><?=$time;?></span>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>  
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-dismiss="modal">Ok</button>
      </div>
    </div>
  </div>
</div>




<style>
    .sheduler_timeb_text { 
        position: relative;
         
    } 
    .sheduler_day {
        display: block;
        float: left;
        clear: both;
    }
    .sheduler_day_text {
        width: 50px;
        height: 20px;
        float: left;
        text-align: center;  
    }
    .sheduler_day_text input {
        float: right;
    }
    .sheduler_time {
        width: 24px;
        height: 20px;
        float: left;
        text-align: center;
        cursor: pointer;
    }
    .sheduler_selected_time {
        background: #fcf8e3;
    }
    .sheduler_time_text { 
        width: 24px;
        float: left;
        margin: -10px 0px 5px 0px;
    }
    .sheduler_time_text span {
        -moz-transform: rotate(-90deg);
        -webkit-transform: rotate(-90deg);
        -o-transform: rotate(-90deg);
        display: block;
        margin: 6px 0px 10px -13px;
    }
    .sheduler_time_text_wraper {
        display: block;
        float: left;
        clear: both;
        padding: 15px 0px 0px 56px;
    }
    .sheduler_time span::selection, .sheduler_day_text::selection, .sheduler_time_text::selection {
        background: rgba(255, 255, 255, 0); /* Цвет фона */
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    
</style>