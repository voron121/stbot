<?php 
require_once __DIR__ . '/../../controllers/schedulerController.php';

$scheduler = new SchedulerController(); 
$actions = [
    "PUBLISH"   => "Опубликовать",
    "UNPUBLISH" => "Удалить публикацию",
    "CLOSE"     => "Закрыть голосование",
];

?>
<link rel="stylesheet" href="views/scheduler/css/bootstrap-datetimepicker.css">
<script src="views/scheduler/js/moment-with-locales.js"></script>
<script src="views/scheduler/js/bootstrap-datetimepicker.js"></script>
<script src="views/scheduler/js/sheduler.js"></script>

<div class="modal fade" id="scheduler" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="myModalLabel">Задать расписание</h4>
      </div>
      <div class="modal-body">
        <?php if (false == $scheduler->isUserSchedulerLimitExceeded($user->sheduler_task_count)):?>
            <div class="shedulerFormMessage alert alert-block"></div>
            <div class="schedule_plan"></div>
            <div class="shedulerForm">
                <input type='hidden' name="id" class="form-control" value="" />
                <input type='hidden' name="type" class="form-control" value="" />
                <input type='hidden' name="channel_id" class="form-control" value="" />
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="date">Укажите дату:</label>
                    <div class='input-group date' id='date'>
                        <input type='text' name="date" class="form-control" value="<?=date('Y-m-d');?>" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                  </div>
                </div>

                <div class="col-sm-6">
                  <label for="time">Укажите время:</label>
                  <div class="form-group">
                    <div class='input-group date' id='time'>
                        <input type='text' name="time" class="form-control" value="<?=date('HH:mm');?>" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-time"></span>
                        </span>
                    </div>
                  </div>
                </div>

                <div class="col-sm-12">
                  <label for="basic-url">Выберите действие:</label>
                  <div class="input-group col-md-12" id="scheeduler_actions"></div>
                </div>
              <div class="clearfix"></div>
            </div>
        <?php else:?>
            <div class="alert alert-danger">
                <b>Вы превысили лимит заданий по расписанию в день на сегодня.</b>
                <hr>
                Лимит заданий в день для вашего тарифного плана - <?=$user->sheduler_task_count?>.<br>
                Вы можете изменить ваш тарифный план на более высокий, или же создать новые расписания заданий завтра.
            </div>
        <?php endif;?>
      </div>
      <div class="clearfix"></div>
      <div class="modal-footer">
        <?php if (false == $scheduler->isUserSchedulerLimitExceeded($user->sheduler_task_count)):?>
            <div class="modal_actions_open">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary save_shedule">Сохранить</button>
            </div>
        <?php else:?>
            <div class="modal_actions_open">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Ок</button>
            </div>
        <?php endif;?>
        <div class="modal_actions_close">
            <button type="button" class="btn btn-secondary close_sheduler_modal" data-dismiss="modal">Зыкрыть</button>
        </div>
      </div>
    </div>
  </div>
</div>