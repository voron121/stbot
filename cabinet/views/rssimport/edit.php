<?php
// Защита от запуска темплета вне контекста админ панели
if (TEMPLATE_CHECK != 1) {
    die('');
}
require_once __DIR__ . '/../../controllers/rssController.php';
require_once __DIR__ . '/../../controllers/rssImportController.php';
require_once __DIR__ . '/../../controllers/channelController.php';
//-------------------------------------------------------//

$rss            = new RSSController();
$rss_rule       = new RSSImportController();
$channel        = new ChannelController();
$channel_list   = $channel->getApprovedUserTelegramChannelsList();
$rss_rule       = $rss_rule->getRSSImportRuleById((int)$_GET['rule_id']);
$rss_list       = $rss->getRSSListAll();
//-------------------------------------------------------//
?>
<script src="/cabinet/views/rssimport/js/rssimport.js"></script>
<div class="col-md-12">
    <h2>Редактировать правило импорта RSS: <?=$rss_rule->name?></h2>
    <hr>
    <div class="col-sm-6">
        <form class="register-form" method="post" action="/cabinet/helpers/rssimport.php?action=edit">
            <input type="hidden" name="rss_rule_id" value="<?=$rss_rule->id?>">
            <div class="input-group col-md-12">
                <?php include 'sheduler.php'; ?>
            </div>
            <div class="input-group col-md-12">
                <label for="basic-url">Название:</label>
                <input type="text" name="name" required class="form-control" placeholder="Название:" value="<?=$rss_rule->name?>">
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Выберите RSS ленту:</label>
                <select name="rss_id" class="form-control">
                    <?php foreach ($rss_list as $rss): ?>
                        <option value="<?= $rss->id; ?>" <?= ($rss_rule->rss_id == $rss->id) ? 'selected' : ''; ?>><?= $rss->url; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Выберите канал:</label>
                <select name="user_chanel_id" class="form-control">
                    <?php foreach ($channel_list as $user_channel): ?>
                        <option value="<?= $user_channel->id; ?>" <?= ($rss_rule->chanel_id == $user_channel->id) ? 'selected' : ''; ?>><?= $user_channel->channel_name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Публиковать изображение:</label>
                <select name="publish_image" class="form-control">
                    <option value="no" <?= ($rss_rule->publish_image == 'no') ? 'selected' : ''; ?>>Нет</option>
                    <option value="yes" <?= ($rss_rule->publish_image == 'yes') ? 'selected' : ''; ?>>Да</option>
                </select>
            </div>

            <div class="input-group col-md-12 img_url_block">
                <label for="basic-url">Получать изображение по тегу:</label>
                <input type="text" name="image_tag" class="form-control" placeholder="Тег изображения:" value="<?=$rss_rule->image_tag?>">
            </div>

            <div class="input-group col-md-12 img_url_block">
                <label for="basic-url">Источник ссылки на изображение:</label>
                <select name="image_tag_mode" class="form-control">
                    <option value="attr" <?= ($rss_rule->image_tag_mode == 'attr') ? 'selected' : ''; ?>>Атрибут</option>
                    <option value="value" <?= ($rss_rule->image_tag_mode == 'value') ? 'selected' : ''; ?>>Значение тега</option>
                </select>
            </div>

            <div class="input-group col-md-12">
                <label for="basic-url">Публиковать ссылку на статью:</label>
                <select name="publish_url" class="form-control">
                    <option value="yes" <?= ($rss_rule->publish_url == 'yes') ? 'selected' : ''; ?>>Да</option>
                    <option value="no" <?= ($rss_rule->publish_url == 'no') ? 'selected' : ''; ?>>Нет</option>
                </select>
            </div>
            
            <div class="input-group col-md-12 read_more_text">
                <label for="basic-url">Текст ссылки "Подробнее":</label>
                <input type="text" name="read_more_text" class="form-control" placeholder="Текст ссылки:" value="<?=$rss_rule->read_more_text?>">
            </div>
            
            <div class="input-group col-md-12">
                <label for="basic-url">Количество постов за сессию:</label>
                <input type="number" name="limit" required class="form-control" placeholder="Лимит записей:" min="1" max="50" value="<?=$rss_rule->limit?>">
            </div>
            
            <div class="input-group col-md-12">
                <label for="basic-url">Список стоп-слов:</label>
                <textarea name="stop_words" class="form-control" cols="15" rows="5"><?=$rss_rule->stop_words?></textarea>
            </div>
            
            <div class="input-group col-md-12">
                <label for="basic-url">Включить правило:</label>
                <select name="state" class="form-control">
                    <option value="on" <?= ($rss_rule->state == 'on') ? 'selected' : ''; ?>>Да</option>
                    <option value="off" <?= ($rss_rule->state == 'off') ? 'selected' : ''; ?>>Нет</option>
                </select>
            </div>
            
            <div class="clearfix"></div>
            <br>
            <div class="pull-right">
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#myModal">Расписание запуска правила</button>
                <input class="btn btn-success btn-send" type="submit" value="Сохранить">
            </div>
        </form>
    </div>

    <div class="col-sm-6">
        <div class="alert alert-warning">
            <h4>Информация:</h4>
            <p></p>
        </div>
    </div>
</div>
