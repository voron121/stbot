<?php
/**
 * Аякс хелпер для разных рутинных задач
 * TODO: унифицировать методы обработки действий
 */
require_once __DIR__.'/../../init.php';
include __DIR__ . '/../../controllers/vkgroupsImportController.php';
include __DIR__ . '/../../controllers/vkgroupsController.php';
include __DIR__ . '/../../controllers/channelController.php';
include __DIR__ . '/../../controllers/schedulerController.php';
include __DIR__ . '/../../controllers/rssImportController.php';
include __DIR__ . '/../../controllers/rssController.php';
include __DIR__ . '/../../controllers/postController.php';
include __DIR__ . '/../../controllers/pollController.php';

$id         = (int)$_POST['item_id'];
$item       = strip_tags($_POST['item']);
$file       = (isset($_POST['file'])) ? $_POST['file'] : null ; 
$action     = strip_tags($_POST['action']);
$user_id    = (int)$_SESSION['uid']; 
$message    = []; 

//----------------------------------------------------------------------------//

// Обработка удаления для групп ВК
if ("vkgroup" == $item) { // Удаление паблика ВК
    $vkgroup    = new VKGroupsController();
    if ("delete" == $action) {
        $group      = $vkgroup->getGroupById($id);
        if ($group->rules_count > 0) {
            $message = [
                'message'   => 'Для данного паблика создано '
                                . '<a href="/cabinet/home.php?template=vkgroupsimport&view=list&group_id='.$id.'" '
                                . 'target="blank"><b>'.$group->rules_count.' правил импорта (показать). '
                                . '</b></a><br> Они будут удалены вместе с пабликом.<br> Хотите продолжить? ',
                'itmid'     => $id,
                'itm'       => $item,
                'state'     => 'success',
                'close'     => 'hide',
            ];
        } else {
            $message = [
                'message'   => 'Вы действительно хотите удалить паблик? <br>'
                                . 'Вместе с пабликом будут также удалены данные об импорте в системме.',
                'itmid'     => $id,
                'itm'       => $item,
                'state'     => 'success',
                'close'     => 'hide',
            ];
        }
    } elseif ("delete_accept" == $action) {
        $vkgroup->deleteVKGroup($id);
        $message = [
            'message'   => 'Паблик и смежные с ним данные успешно удалены! ',
            'state'     => 'success',
            'close'     => 'show',
        ];
    }
} elseif ("channel" == $item) { // Удаление канала
    $channel = new ChannelController();
    if ("delete" == $action) {
        $channel_info   = $channel->getChannelById($id);
        if ($channel_info->rules_count > 0) {
            $message = [
                'message'   => 'Для данного канала создано '
                                . '<a href="/cabinet/home.php?template=vkgroupsimport&view=list&channel_id='.$id.'" '
                                . 'target="blank"><b>'.$channel_info->rules_count.' правил импорта (показать). '
                                . '</b></a><br> Они будут удалены вместе с каналом.<br> Хотите продолжить? ',
                'itmid'     => $id,
                'itm'       => $item,
                'state'     => 'success',
                'close'     => 'hide',
            ];
        } else {
            $channel->deleteChannel($id);
            $message = [
                'message'   => 'Вы действительно хотите удалить канал? <br>'
                                . 'Вместе с каналом будут также удалены связанные с ним данные.',
                'itmid'     => $id,
                'itm'       => $item,
                'state'     => 'success',
                'close'     => 'hide',
            ];
        }
    } elseif ("delete_accept" == $action) {
        $channel->deleteChannel($id);
        $message = [
            'message'   => 'Канал и смежные с ним данные успешно удалены! ',
            'state'     => 'success',
            'close'     => 'show',
        ];
    }
} elseif ("vkimportrule" == $item) { // Удаление правила импорта
    if ("delete" == $action) {
        $message = [
            'message'   => 'Вы действительно хотите удалить правило импорта? <br>'
                            . 'Вместе с правилом импорта будут также удалены данные об импорте в системме.',
            'itmid'     => $id,
            'itm'       => $item,
            'state'     => 'success',
            'close'     => 'hide',
        ];
    } elseif ("delete_accept" == $action) {
        $vkimportrule = new VKGroupsImportController();
        $vkimportrule->deleteVKImportRule($id);
        $message = [
            'message'   => 'Правило импорта удалено!',
            'state'     => 'success',
            'close'     => 'show',
        ];
    }
} elseif ("scheduler" == $item) { // Удаление задачи планировщика
    if ("delete" == $action) {
        $message = [
            'message'   => 'Вы действительно хотите удалить запланированную задачу?',
            'itmid'     => $id,
            'itm'       => $item,
            'state'     => 'success',
            'close'     => 'hide',
        ];
    } elseif ("delete_accept" == $action) {
        $scheduler  = new schedulerController();
        $scheduler->deleteTask($id);
        $message = [
            'message'   => 'Задача удалена!',
            'state'     => 'success',
            'close'     => 'show',
        ];
    }
} elseif ("rssimportrule" == $item) { // Удаление правила импорта RSS
    if ("delete" == $action) {
        $message = [
            'message'   => 'Вы действительно хотите удалить правило импорта для RSS ?',
            'itmid'     => $id,
            'itm'       => $item,
            'state'     => 'success',
            'close'     => 'hide',
        ];
    } elseif ("delete_accept" == $action) {
        $rss_import = new RSSImportController();
        $rss_import->deleteImportRuleRSS($id);
        $message = [
            'message'   => 'Правило импорта успешно удалено!',
            'state'     => 'success',
            'close'     => 'show',
        ];
    }
} elseif ("rss" == $item) { // Удаление правила импорта RSS
    if ("delete" == $action) {
        $message = [
            'message'   => 'Вы действительно хотите удалить ленту RSS ?'
                            . '<br>Вместе с ней будут удалены правила импорта для данной ленты RSS',
            'itmid'     => $id,
            'itm'       => $item,
            'state'     => 'success',
            'close'     => 'hide',
        ];
    } elseif ("delete_accept" == $action) {
        $rss = new RSSController();
        $rss->deleteRSS($id);
        $message = [
            'message'   => 'RSS лента успешно удалена!',
            'state'     => 'success',
            'close'     => 'show',
        ];
    }
} elseif ("post" == $item) { // Удаление публикации
    if ("delete" == $action) {
        $message = [
            'message'   => 'Вы действительно хотите удалить публикацию?'
                            . '<br> Если публикация была опубликована, она будет удалена с канала',
            'itmid'     => $id,
            'itm'       => $item,
            'state'     => 'success',
            'close'     => 'hide',
        ];
    } elseif ("delete_accept" == $action) {
        $post = new PostController();
        $post->deletePost($id);
        $message = [
            'message'   => 'Публикация успешно удалена!',
            'state'     => 'success',
            'close'     => 'show',
        ];
    }
} elseif ("poll" == $item) { // Удаление опроса 
    if ("delete" == $action) {
        $message = [
            'message'   => 'Вы действительно хотите удалить опрос?'
                            . '<br> Если опрос был опубликован, он будет удален с канала',
            'itmid'     => $id,
            'itm'       => $item,
            'state'     => 'success',
            'close'     => 'hide',
        ];
    } elseif ("delete_accept" == $action) {
        $poll = new PollController();
        $poll->deletePoll($id);
        $message = [
            'message'   => 'Публикация успешно удалена!',
            'state'     => 'success',
            'close'     => 'show',
        ];
    }
} elseif ("post_attachment" == $item) { // Удаление файла 
    if ("delete" == $action) {
        $message = [
            'message'   => 'Вы действительно хотите удалить фаил?',
            'itmid'     => $id,
            'itm'       => $item,
            'file'      => $file,
            'state'     => 'success',
            'close'     => 'hide',
        ];
    } elseif ("delete_accept" == $action) {
        $post = new PostController();
        $post->ajaxDeletePostAttachment($file);
        $message = [
            'message'   => 'Фаил удален!',
            'state'     => 'success',
            'close'     => 'show',
        ];
    }
}


echo json_encode($message);