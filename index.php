<?php
error_reporting(0);
require_once __DIR__ . '/cabinet/controllers/subscriptionsController.php';
//-------------------------------------------------------//
$subscriptions = new SubscriptionsController();
$subscriptions_list = $subscriptions->getSubscriptionsListForMain();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
        <link rel="stylesheet" href="/cabinet/views/css/main_page.css">	
        <link rel="stylesheet" href="/cabinet/views/css/jquery.bxslider.css">
        <link rel="stylesheet" href="/cabinet/views/css/animate.min.css">	

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta charset="UTF-8">
        <title>Telegram Auto Servise - Сервис автоматизации телеграм каналов!</title>
    </head>
    <body>
        <div class="row-fluid main_header">
            <div class="container main_header_menu">
                <div class="col-sm-4">
                    T2R2
                </div>
                <div class="col-sm-4"></div>
                <div class="col-sm-4 text-right buttons_rounded">
                    <a href="#registration">регистрация </a>				
                    <a href="/cabinet/home.php?template=dashboard&view=list">Вход </a>
                </div>
            </div>

            <div class="container main_header_slider">
                <div class="col-sm-12">
                    <div class="col-sm-6">
                        <h3>Автоматизируй задачи в Телеграм! </h3>
                        <br>
                        <div>
                            <p>Удобный редактор для ваших статей, инструменты импорта данных из ваших медиа ресурсов,
                                инструмент планирования задач для телеграм, простой и удобный интерфейс и многое другое.</p>
                            <br>
                            <p>Попробуйте бесплатно и получайте больше профита от ваших телеграм каналов!</p>
                            <div class="text-center">
                                <a href="#registration" class="a-btn-reg">Попробовать бесплатно</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <img src="images/img_h_1.png" alt="" class="animate__animated animate__fadeInRight" width="100%">
                    </div>
                </div>
            </div>
        </div>

        <div class="row-fluid">
            <div class="container light_item_wraper">
                <div class="col-sm-4 light_item">
                    <img src="/images/clock.png" width="70">
                    <h3>Планировщик </h3>
                    <hr>
                    <p>
                        Инструмент планирования задач
                        для вашего канала.
                        Запланированные действия с сообщениями вашего канала, удобное управление задачами, система отслеживания выполнения задач.
                    </p>
                </div>
                <div class="col-sm-4 light_item">
                    <img src="/images/627558.svg" width="70">
                    <h3>Импорт VK</h3><hr>
                    <p>
                        Импорт записей вашего сообщества VK
                        в ваш телеграм канала. Шибкая система правил, позволит вам перенести ваш контент в телеграм.
                    </p>
                </div>
                <div class="col-sm-4 light_item">
                    <img src="/images/1467816.svg" width="70">
                    <h3>Импорт RSS</h3><hr>
                    <p>
                        Гибкая система правил импорта
                        RSS ленты вашего ресурса, которую вы можете настроить для ваших нужд.
                    </p>
                </div>
            </div>

            <div class="container items_wraper">
                <div class="text-center">
                    <h3>Преимущества</h3>
                </div>
                <div class="slider"> 
                    <div class="col-sm-12 item_slide">
                        <div class="col-sm-6 item_slide_image">
                            <img src="images/promo/perspective/5.png" alt="" width="100%">
                        </div>
                        <div class="col-sm-6">
                            <h3>Все под рукой!</h3>
                            <p>
                                Набор инструментов для автоматизации ведения телеграм каналов всегда под рукой. 
                                Вы можете создавать не ограниченное количество записей с отложенным постингом, удалением по расписанию, з
                                управление опросами.
                            </p>
                        </div>
                    </div>

                    <div class="col-sm-12 item_slide">
                        <div class="col-sm-6">
                            <h3>Импорт VK!</h3>
                            <p>
                                Наши инструменты импорта данных из VK помогут вам перенести и актуализировать ваш контент в социальной сети 
                                ваш телеграм. Не нужно больше следить самому за актуальностью информации - просто автоматизируй!
                            </p>
                        </div>
                        <div class="col-sm-6 item_slide_image">
                            <img src="images/promo/perspective/4.png" alt="" width="100%">
                        </div>
                    </div>

                    <div class="col-sm-12 item_slide">
                        <div class="col-sm-6 item_slide_image">
                            <img src="images/promo/perspective/7.png" alt="" width="100%">
                        </div>
                        <div class="col-sm-6">
                            <h3>Импорт RSS!</h3>
                            <p>
                                Подключите RSS ленту вашего медиа ресурса и автоматизируйте актуальность информации на вашем телеграм канале! 
                                Гибкая система правил поможет вам настроить импорт данных из RSS лент максимально удобно именно для вас!
                            </p>
                        </div>
                    </div>

                    <div class="col-sm-12 item_slide">
                        <div class="col-sm-6">
                            <h3>Расписание заданий!</h3>
                            <p>
                                Менеджер расписаний задач поможет вам автоматизировать задачи по работе с вашим телеграм каналом и управлять ими.
                                Планируйте публикацию статей, удаление статей или открытие опросов. 
                            </p>
                        </div>
                        <div class="col-sm-6 item_slide_image">
                            <img src="images/promo/perspective/2.png" alt="" width="100%">
                        </div>
                    </div> 
                </div>

            </div>

            <div class="container light_item_wraper tarifs_wraper" >
                <div class="text-center">
                    <h3>Тарифы</h3>
                </div>
                <div class="t_slider">
                    <?php foreach ($subscriptions_list as $subscription): ?>
                        <div>
                            <div class="light_item">
                                <h3><?= $subscription->name ?></h3>
                                <div class="light_item_price">
                                    <div class="amount">
                                        <?= $subscription->cost ?> USD
                                    </div>
                                    <div class="period">
                                        / месяц
                                    </div>
                                </div>
                                <hr>
                                <p>
                                    <?= $subscription->description ?>
                                </p>
                            </div> 
                        </div>
                    <?php endforeach; ?> 
                </div>

                <div class="col-sm-12 text-center grey_text">
                    * пользование сервисом предаставляется бесплатно для нового пользователя на ограниченный срок
                </div>
            </div> 

            <div class="container items_wraper tarifs_wraper slider_wraper" id="registration">
                <div class="text-center">
                    <h3>Попробуйте бесплатно!</h3>
                </div>
                <div class="col-sm-6">
                    <div>
                        Попробуйте наш сервис абсолютно бесплатно!<br><br>
                        <p>
                            Зарегистрируйтесь и получите 10 дней бесплатного теста сервиса со всеми возможностями!
                        </p>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="reg_from">
                        <h4 class="text-center">Регистрация</h4>
                        <hr>
                        <div class="messages"></div>
                        <form class="register-form register-form-wrap">
                            <div>
                                <div class="input-group">
                                    <input name="u_login" type="text" class="form-control" placeholder="Ваша почта" aria-describedby="basic-addon1">
                                </div>
                            </div>

                            <div>
                                <div class="input-group">
                                    <input name="u_password" type="password" class="form-control" placeholder="Придумайте пароль" aria-describedby="basic-addon2">
                                </div>
                            </div>

                            <div>
                                <div class="input-group">
                                    <input name="u_password_retry" type="password" class="form-control" placeholder="Повторите пароль" aria-describedby="basic-addon2">
                                </div>
                            </div>
                            <div class="col-sm-12 text-center grey_text">
                                Регистрируясь в сервисе вы соглашаетесь с <a data-toggle="modal" data-target="#rulesModal">пользовательским соглашением</a>
                            </div>
                            <div class="btn btn-reg" >Регистрация</div>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row-fluid main_footer">
            <div class="container">
                <div class="col-sm-2">
                    <?= date("Y"); ?> год
                </div>
                <div class="col-sm-3"></div>
                <div class="col-sm-3 text-right">
                    <a data-toggle="modal" data-target="#rulesModal">правила пользования сервисом</a>
                </div>
                <div class="col-sm-4 text-right">
                    <a data-toggle="modal" data-target="#rulesPropsModal">отказ от ответсвенности</a>
                </div>
            </div>
        </div>

        
        <div class="modal fade" id="rulesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Пользовательское соглашение</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                ...
              </div> 
            </div>
          </div>
        </div>
        
        <div class="modal fade" id="rulesPropsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Отказ от ответсвенности</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                ...
              </div> 
            </div>
          </div>
        </div>
        
        
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <!-- <script src="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.min.js"></script> -->
        <script src="/cabinet/views/js/bxslider-4-master/jquery.bxslider-rahisified.js"></script>
        <script src="/cabinet/views/js/front.js"></script>
    </body>
</html>





