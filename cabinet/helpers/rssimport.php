<?php
    require_once __DIR__.'/../init.php';
    require_once __DIR__.'/../controllers/rssImportController.php';
    //-------------------------------------------------------//

    $rssimportcontroller = new RSSImportController();
    //-------------------------------------------------------//

    $action                     = (isset($_GET['action']))                  ? $_GET['action']               : null;
    $rss_import_rule_id         = (isset($_GET['rss_import_rule_id']))      ? $_GET['rss_import_rule_id']   : null; // КОСТЫЛЬ
    $rss_rule_id                = (isset($_POST['rss_rule_id']))            ? $_POST['rss_rule_id']         : null; // КОСТЫЛЬ
    $chanel_id                  = (isset($_POST['user_chanel_id']))         ? $_POST['user_chanel_id']      : null;
    $state 	                = (isset($_POST['state']))                  ? $_POST['state']               : 'on';
    $publish_image              = (isset($_POST['publish_image']))          ? $_POST['publish_image']       : 'no';
    $image_tag                  = (isset($_POST['image_tag']))              ? $_POST['image_tag']           : 'url';
    $image_tag_mode             = (isset($_POST['image_tag_mode']))         ? $_POST['image_tag_mode']      : 'attr';
    $publish_url                = (isset($_POST['publish_url']))            ? $_POST['publish_url']         : 'no';
    $read_more_text             = (isset($_POST['read_more_text']))         ? $_POST['read_more_text']      : 'Подробнее...';
    $stop_words                 = (isset($_POST['stop_words']))             ? $_POST['stop_words']          : '';
    $rss_id                     = (isset($_POST['rss_id']))                 ? $_POST['rss_id']              : null;
    $name 	                = (isset($_POST['name']))                   ? $_POST['name']                : 'Правило импорта '.date('Y-m-d');
    $limit                      = (isset($_POST['limit']))                  ? (int)$_POST['limit']          : 1;
    $sheduler                   = (isset($_POST['dt']))                     ? json_encode($_POST['dt'])     : '';
    //-------------------------------------------------------//

    $rule_set = [
        'rss_rule_id'       => $rss_rule_id,
        'rss_id'            => $rss_id,
        'chanel_id'         => $chanel_id,
        'state'             => $state,
        'name'              => $name,
        'publish_image'     => $publish_image,
        'image_tag_mode'    => $image_tag_mode,
        'image_tag'         => $image_tag,
        'publish_url'       => $publish_url,
        'read_more_text'    => $read_more_text,
        'stop_words'        => $stop_words,
        'limit'             => $limit,
        'sheduler'          => $sheduler
    ];

    if (isset($action) && 'add' == $action) {
        $rssimportcontroller->addRSSImportRule($rule_set);
    } elseif (isset($action) && 'edit' == $action) {
        $rssimportcontroller->editRSSImportRule($rule_set);
    } elseif (isset($action) && 'delete' == $action) {
        $rssimportcontroller->deleteImportRuleRSS($rss_import_rule_id);
    } else {
        die('some error');
    }
?>