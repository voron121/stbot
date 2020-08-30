<?php require_once __DIR__ . '/init.php'; ?>
<!DOCTYPE html>
<html lang="en">
    <head>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="/cabinet/views/css/style.css">
        <link rel="stylesheet" href="/cabinet/views/css/trumbowyg.min.css">
        <link rel="stylesheet" href="/cabinet/views/css/trumbowyg.emoji.min.css">

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="/cabinet/views/js/trumbowyg.min.js"></script>
        <script src="/cabinet/views/js/ajaxFormValidator.js"></script>
        <script src="/cabinet/views/js/trumbowyg.emoji.min.js"></script>
        <script src="/cabinet/views/js/jquery.touchSwipe.js"></script>
        <script src="/cabinet/views/js/main.js"></script>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Личный кабинет</title>
    </head>
    <body>
        <div class="admin_cp_wraper">
            <div id="mobile_menu_area"></div>
            <div class="admin_cp_sidebar">
                <div class="mobile_menu_button" id="close_cp_sidebar">
                    <div class="burdger_wraper">
                        X
                    </div>
                </div>
                <div class="admin_cp_logo">
                    <a class="" href="#">
                        <img src="https://telegram.org/img/t_logo.png" alt="" height="40">
                        T2R2
                    </a>
                </div>

                <ul class="nav navbar-nav left_menu">
                    <li id="channel">
                        <a href="?template=dashboard&view=list">Главная</a>
                    </li>
                    <li id="channel">
                        <a href="?template=channel&view=list">Список каналов</a>
                    </li>
                    <li id="posts">
                        <a href="?template=post&view=list">Список публикаций</a>
                    </li>
                    <li id="sheduller">
                        <a href="?template=poll&view=list">Список опросов</a>
                    </li>
                    <li id="vkaccount">
                        <a href="?template=vkaccount&view=list">Аккаунты Вконтакте</a>
                    </li>
                    <li id="vkgroups">
                        <a href="?template=vkgroups&view=list">Сообщества Вконтакте</a>
                    </li>
                    <li id="vkgroupsimport">
                        <a href="?template=vkgroupsimport&view=list">Импорт сообщества Вконтакте</a>
                    </li>
                    <li id="rss">
                        <a href="?template=rss&view=list">Список RSS</a>
                    </li>
                    <li id="rssimport">
                        <a href="?template=rssimport&view=list">Импорт RSS</a>
                    </li>
                    <li id="sheduller">
                        <a href="?template=scheduler&view=list">Расписание заданий</a>
                    </li>
                    <li id="logs">
                        <a href="?template=logs&view=list">Лог работы роботов</a>
                    </li>
                </ul>
            </div>

            <div class="admin_cp_content">
                <div class="admin_cp_header">
                    <div class="col-xs-12 cabinet_header">
                        <div class="mobile_header_button">
                            <div class="burdger_wraper">
                                <div class="burger_item"></div>
                                <div class="burger_item"></div>
                                <div class="burger_item"></div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <ul>
                                <li>
                                    <a href="#">Справка</a>
                                </li>
                                <li>
                                    <a href="#">Поддержка</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-8 text-right">
                            <ul>
                                <li class="dropdown">
                                    <a href="#" id="dLabel" type="button" data-toggle="dropdown" aria-haspopup="true"><?= $user->login; ?></a>
                                    <ul class="user_dropdown-menu dropdown-menu dropdown-menu-right" aria-labelledby="dLabel">
                                         <li>
                                            <a href="/cabinet/home.php?template=tarifs&view=list">Тариф: <?= $user->subscription_name; ?></a>
                                        </li>
                                        <li>
                                            <a href="/cabinet/home.php?template=payment&view=payment">Баланс: <?= $user->balance ?> USD (пополнить)</a>
                                        </li>
                                        <!--
                                        <li>
                                            <a class="dropdown-item" href="/cabinet/home.php?template=settings&view=list">Настройки</a>
                                        </li>
                                        -->
                                        <li role="separator" class="divider"></li>
                                        <li>
                                            <a href="/cabinet/helpers/auth.php?action=logout">Выйти</a>	
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="content">
                    <?php
                    include(__DIR__ . '/views/alert_message/alert_message.php');
                    $active = (isset($_GET['template'])) ? '#' . $_GET['template'] : '#home'; // нужно для указания активного пункта меню
                    if (isset($_GET['template'])) {
                        include __DIR__ . '/views/' . $_GET['template'] . '/' . $_GET['view'] . '.php';
                    } elseif (!isset($_GET['template'])) {
                        include __DIR__ . '/views/channel/list.php';
                    }
                    ?>
                    <div class="clearfix"></div>
                </div>

            </div>
        </div>



        <script type="text/javascript">
            $('<?php echo $active; ?>').addClass('active');
        </script>
    </body>
</html>