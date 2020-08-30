<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/vkgroupsController.php';
require_once __DIR__ . '/../../controllers/channelController.php';
require_once __DIR__ . '/../../models/vkaccountModel.php';
require_once __DIR__.'/../../controllers/vkgroupsImportController.php';
//-------------------------------------------------------//

$vk_import_rules    = new VKGroupsImportController();
$vkgroups           = new VKGroupsController();
$channel            = new ChannelController();
$vkModel            = new VKAccount();
$vk_accounts        = $vkModel->getUserVKAccountsList((int) $_SESSION['uid']);
$channel_list       = $channel->getApprovedUserTelegramChannelsList();
//-------------------------------------------------------//
?>
<?php if(true == $vk_import_rules->isUserVKRulesLimitExceeded($user->vk_rule_count)):?>
    <script>
        window.location.href = "/cabinet/home.php?template=vkgroupsimport&view=list&message=LimitError";
    </script>
<?php endif;?>
<div class="col-md-12">
    <h2>Добавить правило импорта:</h2>
    <hr>
    <div class="col-sm-6"> 
        <form class="register-form" method="post" action="/cabinet/helpers/vkgroupsimport.php?action=add">
            <div class="input-group col-md-12">
                <?php include 'sheduler.php'; ?>
            </div>
            <div class="input-group col-md-12">
                <label for="basic-url">Название правила:</label>
                <input type="text" name="rule_name" required class="form-control" placeholder="Название правила:" value="">
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Выберите канал:</label>
                <select name="user_chanel_id" class="form-control">
                    <?php foreach ($channel_list as $user_channel): ?>	
                        <option value="<?= $user_channel->id; ?>"><?= $user_channel->channel_name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Выберите пользователя Вконтакте:</label>
                <select name="vk_user_id" class="form-control">
                    <?php foreach ($vk_accounts as $vk_account): ?>	
                        <option value="<?= $vk_account->vk_user_id; ?>"><?= $vk_account->first_name; ?> <?= $vk_account->last_name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Выберите сообщество Вконтакте:</label>
                <select name="vk_group_id" class="form-control">
                    <?php foreach ($vkgroups->getGroupsListAll() as $group): ?>	
                        <option value="<?= $group->group_id; ?>"><?= $group->group_name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Выберите режим публикации:</label>
                <select name="mode" class="form-control">			  		
                    <option value="text_only">Только текст</option>
                    <option value="image_only">Только изображение</option>
                    <option value="text_and_image">Текст  и изображение</option>
                    <option value="albom_with_caption">Текст и изображения (альбом)</option>
                    <option value="albom">Только изображения (альбом)</option>
                    <option value="animation">Анимация(gif) без текста</option>
                    <option value="animation_with_caption">Анимация(gif) вместе с текстом</option>
                </select>
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Выберите режим публикации текста:</label>
                <select name="text_mode" class="form-control">			  		
                    <option value="cut">Обрезать текст</option>
                    <option value="all">Публиковать текст полность (несколько сообщений)</option>
                </select>
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Порядок импорта записей:</label>
                <select name="order" class="form-control">
                    <option value="DESC">Все записи начиная с первой</option>
                    <option value="ASC">Последние записи</option>
                </select>
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Обработка ссылок:</label>
                <select name="url_mode" class="form-control">
                    <option value="skipp">Игнорировать сообщения в которых есть ссылка</option>
                    <option value="cut">Вырезать ссылки из текста</option>
                    <option value="ignore">Ничего не делать</option>
                </select>
            </div>
            
            <div class="input-group col-md-12">
                <label for="basic-url">Количество постов за сессию:</label>
                <input type="number" name="limit" required class="form-control" placeholder="Лимит записей:" min="1" max="10" value="">
            </div>
            
            <div class="input-group col-md-12">
                <label for="basic-url">Список стоп-слов:</label>
                <textarea name="stop_words" class="form-control" cols="15" rows="5">[club, [CLUB, [Club, Купить, купить, Жанр, жанр</textarea>
            </div>
            
            <div class="input-group col-md-12">
                <label for="basic-url">Включить правило:</label>
                <select name="state" class="form-control">			  		
                    <option value="on">Да</option>
                    <option value="off">Нет</option>
                </select>
            </div>  
            <div class="clearfix"></div>
            <br>
            <div class="pull-right">
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#myModal">Расписание запуска правила</button>
                <input class="btn btn-success btn-send" type="submit" value="Добавить правило">
            </div>
        </form>
    </div>

    <div class="col-sm-6">
        <div class="alert alert-warning">
            <h4>Информация:</h4>
            <p>В данном разделе можно создать правила импорта публикаций сообщест Вконтакте в Телеграм.</p>
            <p>Вы можете создавать сколько угодно правил импорта с разными условиями для одной группы или канала.</p>
            <hr>
            <h4>Описание параметров:</h4>

            <div class="rule_info">
                <b>Название правила</b> - название правила импорта. Будет отображено в списке правил импорта
            </div>

            <div class="rule_info">
                <b>Выберите канал</b> - укажите канал в телеграмм для импорта сообщества
            </div>

            <div class="rule_info">
                <b>Выберите пользователя Вконтакте</b> - подключенный в сервиса аккаунт Вконтаке. Будет использован
                для доступа к записям  сообщества
            </div>

            <div class="rule_info">
                <b>Выберите режим публикации</b> - укажите режим обработки записей сообществ Вконтакте. Есть несколько режимов:
                <div class="rule_sub_info">
                    <b>Только текст</b> - будут опубликованы только текстовые записи из сообщества. Изображения будут проигнорированны.
                </div>
                <div class="rule_sub_info">
                    <b>Только изображение</b> - будут опубликованы только изображения из сообщества. Текст будет проигнорирован.
                    Каждое изображение будет опубликованно в отдельном сообщении телеграмм.
                </div>
                <div class="rule_sub_info">
                    <b>Текст и изображение</b> - будут опубликованы изображения из сообщества с подписями к ним (если они есть).
                    Каждое изображение будет опубликованно в отдельном сообщении телеграмм.
                </div>
                <div class="rule_sub_info">
                    <b>Текст и изображения (альбом)</b> - будут опубликованы изображения из сообщества Вконтакте с подписями как альбом.
                </div>
                <div class="rule_sub_info">
                    <b>Только изображения (альбом)</b> - будут опубликованы только изображения из сообщества Вконтакте. Текст будет проигнорирован.
                </div>
                <div class="rule_sub_info">
                    <b>ТАнимация(gif) без текста</b> - будут опубликованы анимации без текста. Отлично подходит если у вас много анимаций.
                </div>
                <div class="rule_sub_info">
                    <b>Анимация(gif) вместе с текстом</b> - будут опубликованы анимации и текст. Текст будет проигнорирован.
                </div>
            </div>

            <div class="card-header" id="headingThree">
                <div class="colapse_button_wraper">
                    <div class="btn btn-link collapsed colapse_button"
                         data-toggle="collapse"
                         data-target="#collapseThree"
                         aria-expanded="false"
                         aria-controls="collapseThree"
                         id="collapse">
                        Показать полный список
                    </div>
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                    <div class="rule_info">
                        <b>Выберите режим публикации текста</b> - указывает параметр обработки текста в сообщениях. Существует несколько режимов:
                        <div class="rule_sub_info">
                            <b>Только изображения (альбом)</b> - будут опубликованы только изображения из сообщества Вконтакте. Текст будет проигнорирован.
                        </div>
                        <div class="rule_sub_info">
                            <b>Обрезать текст</b> - текст с длиной больше 4096 символов будет обрезан.
                        </div>
                        <div class="rule_sub_info">
                            <b>Публиковать текст полностью (несколько сообщений)</b> - текст с длиной больше 4096 символов будет разбит на несколько сообщений.
                            Каждое сообщение будет опубликованно в телеграмм.
                        </div>
                    </div>

                    <div class="rule_info">
                        <b>Порядок импорта записей</b> - укажите режим публикации записей. Есть несколько режимов:
                        <div class="rule_sub_info">
                            <b>Все записи начиная с первой</b> - будут публиковатьсяя записи из сообщества Вконтакте начиная с самой первой записи.
                        </div>
                        <div class="rule_sub_info">
                            <b>Последние записи</b> - будут опубликованы записи начиная с момента подключения сообщества к сервису.
                            Более ранние записи будут пропущенны.
                        </div>
                    </div>

                    <div class="rule_info">
                        <b>Обработка ссылок</b> - укажите режим обработки ссылок в сообщениях. Есть несколько режимов:
                        <div class="rule_sub_info">
                            <b>Игнорировать сообщения в которых есть ссылка</b> - сообщения с ссылкой будут проигнорированны в процесе импорта.
                        </div>
                        <div class="rule_sub_info">
                            <b>Вырезать ссылки из текста</b> - ссылка будет вырезена из текста.
                        </div>
                        <div class="rule_sub_info">
                            <b>Ничего не делать</b> - сообщение будет отправлено вместе с ссылкой.
                        </div>
                    </div>

                    <div class="rule_info">
                        <b>Список стоп-слов</b> - укажите список стоп-слов через запятую.
                        Сообщения, в которых есть слова из списка стоп-слов не будут испортированны в телеграмм.
                    </div>
                </div>
            </div>

            <hr>
            <h4>Важно:</h4>
            <p>Параметр "Выберите режим публикации текста" не совместим при выборе параметра "Выберите режим публикации" отличным от "только текст".</p>
            <p>Параметр обработки текста будет проигнорирован при выборе параметра "Выберите режим публикации" отличным от "только текст".</p>
        </div>
    </div>
</div> 
<script src="/cabinet/views/vkgroupsimport/js/vkgroups.js"></script>

<script>
    $('.colapse_button').click(function(){ //you can give id or class name here for $('button')
    $(this).text(function(i,old){
    return old=='Свернуть' ?  'Показать полный список' : 'Свернуть';
    });
    });
</script>
