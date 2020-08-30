<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/channelStatController.php';
require_once __DIR__ . '/../../controllers/channelController.php';
//-------------------------------------------------------//
$channel_id     = (int)$_GET['channel_id'];
$stat_period    = (isset($_GET['stat_period'])) ? (string)$_GET['stat_period'] : "day";
$channel        = new ChannelController();
$channel_data   = $channel->getChannelById($channel_id); 
$stat           = new ChannelStatController();
$channel_stat   = $stat->getChannelStat($channel_id, $stat_period);
$stats_values   = array_values($channel_stat);
$max_scale      = max($stats_values) + round((max($stats_values) / 100 * 25)) ; 
?>

<script src="/cabinet/views/channel_stat/js/Chart.min.js"></script>
<h2>Статистика канала: <?=(isset($channel_data->channel_title)) ? $channel_data->channel_title : $channel_data->url ;?></h2>

<div class="clearfix"></div>
<div class="stat_period_sel">
    <form class="register-form" method="get" action="/cabinet/helpers/channel_stat.php">
        <input type="hidden" name="channel_id" value="<?=$channel_id;?>">
        <select name="stat_period" class="form-control">
            <option value="day"     <?=("day" == $stat_period)      ? "selected"    : "";?> >За сегодня</option>
            <option value="week"    <?=("week" == $stat_period)     ? "selected"    : "";?> >За 7 дней</option>
            <option value="month"   <?=("month" == $stat_period)    ? "selected"    : "";?> >За 30 дней</option>
            <option value="year"    <?=("year" == $stat_period)     ? "selected"    : "";?> >За 360 дней</option>
        </select>
        <input class="btn btn-default btn-send" type="submit" value="Показать">
    </form>
</div>
<canvas id="myChart" width="200" height="50"></canvas>
<script>
    function renderGraf(labels, stat) {
    console.log(labels);
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Пользователей на канале',
                    data: stat,
                    backgroundColor: [
                        'rgba(98, 218, 90, 0)',
                    ],
                    borderColor: [
                        'rgba(98, 218, 90, 1)',
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            max: <?=$max_scale;?>
                        }
                    }]
                }
            }
        });
    }
    renderGraf(<?=json_encode(array_keys($channel_stat));?>, <?=json_encode(array_values($channel_stat));?>);
</script>
