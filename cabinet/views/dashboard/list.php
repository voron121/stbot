<?php
    // Защита от запуска темплета вне контекста админ панели
    if (TEMPLATE_CHECK != 1) { die('');}
    require_once __DIR__.'/../../controllers/dashboardController.php';
    //-------------------------------------------------------//
    $dashboard  = new DashboardController();
?>

<h2>Сводная информация:</h2>
<hr>
<div class="clearfix"></div>
<div>
    <div class="col-md-2">
        <div class="dashboard_item">
            <div class="text-center dashboard_item_text">
                Подключенно ВК Аккаунтов
            </div>
            <div class="text-center dashboard_item_data">
                <?=$dashboard->getVKAccountCount()?>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="dashboard_item">
            <div class="text-center dashboard_item_text">
                Подключенно групп Вконтакте
            </div>
            <div class="text-center dashboard_item_data">
                <?=$dashboard->getVKImportGroupsCount()?>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="dashboard_item">
            <div class="text-center dashboard_item_text">
                Созданно правил импорта для ВК
            </div>
            <div class="text-center dashboard_item_data">
                <?=$dashboard->getVKImportRulesCount()?>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="dashboard_item">
            <div class="text-center dashboard_item_text">
                Подключенно каналов RSS
            </div>
            <div class="text-center dashboard_item_data">
                <?=$dashboard->getRSSImportCount()?>
            </div>
        </div>
    </div>
    
    <div class="col-md-2">
        <div class="dashboard_item">
            <div class="text-center dashboard_item_text">
                Созданно правил для RSS
            </div>
            <div class="text-center dashboard_item_data">
                <?=$dashboard->getRSSImportRulesCount()?>
            </div>
        </div>
    </div>
    <!--
    <div class="col-md-2">
        <div class="dashboard_item">
            <div class="text-center dashboard_item_text">
                Опубликованно сообщений для RSS
            </div>
            <div class="text-center dashboard_item_data">
                <?=$dashboard->getRSSMessagesPOstedCount()?>
            </div>
        </div>
    </div>
    -->
    <div class="col-md-2">
        <div class="dashboard_item">
            <div class="text-center dashboard_item_text">
                Подключенно каналов Телеграмм
            </div>
            <div class="text-center dashboard_item_data">
                <?=$dashboard->getTelegramChannelsCount()?>
            </div>
        </div>
    </div>
    
    <div class="clearfix"></div>
    <hr>
    <div class="col-md-6">
        <div class="col-md-12">
            <div class="dashboard_item_flat">
                <div class="dashboard_item_text">
                    Сводные данные по запланированным задачам:
                </div>
                <div class="text-center dashboard_item_data_set">
                    <?php $post_sheduler_stat = $dashboard->getTotalShedulerStat();?>
                    <p>Успешные: <span class="label label-success"> <?=$post_sheduler_stat['DONE'];?> </span></p>
                    <p>Запланированные: <span class="label label-info"><?=$post_sheduler_stat['ACTIVE'];?> </span></p>
                    <p>Не удачные: <span class="label label-danger"><?=$post_sheduler_stat['FAIL'];?> </span></p>
                </div>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="dashboard_item_flat">
                <div class="dashboard_item_text">
                    Сводные данные по запланированным задачам: за сегодня
                </div>
                <div class="text-center dashboard_item_data_set">
                    <?php $post_sheduler_stat = $dashboard->getTodayShedulerStat();?>
                    <p>Успешные: <span class="label label-success"> <?=$post_sheduler_stat['DONE'];?> </span></p>
                    <p>Запланированные: <span class="label label-info"><?=$post_sheduler_stat['ACTIVE'];?> </span></p>
                    <p>Не удачные: <span class="label label-danger"><?=$post_sheduler_stat['FAIL'];?> </span></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="dashboard_item_flat">
            <div class="dashboard_item_text">
                Сводные данные по запуску роботов
            </div>
            <div class="text-center dashboard_item_data_set">
                <?php $robots_stat = $dashboard->getTodayRobotsData($user->login);?>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Задача:</th>
                            <th>Выполненно:</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($robots_stat as $robot => $info):?>
                        <tr>
                            <td><?=$robot?></td>
                            <td><?=$info["date"]?></td>
                        </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
     
</div>