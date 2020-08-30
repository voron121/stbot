<?php
    require_once __DIR__.'/../init.php';
    require_once __DIR__.'/../controllers/postController.php';
    //-------------------------------------------------------//
    $post               = new PostController();
    $channel_id 	= (isset($_POST['user_chanel']))    ? (int)$_POST['user_chanel']    : null;
    $title 		= (isset($_POST['title']))          ?  strip_tags($_POST['title'])  : null;
    $text 		= (isset($_POST['text']))           ? $_POST['text']                : null;
    $notification 	= (isset($_POST['notification']))   ? $_POST['notification']        : "No";
    $buttons            = (isset($_POST['buttons']) && "" != trim($_POST['buttons'])) ? $_POST['buttons']             : "{}";
     
    $files 		= (isset($_FILES['files']['size']) && array_sum($_FILES['files']['size']) > 0) ? $_FILES['files'] : [];
    if (isset($_POST['post_id'])) {
        $post_id = (int)($_POST['post_id']);
    } elseif(isset($_GET['post_id'])) {
        $post_id = (int)($_GET['post_id']);
    } else {
        $post_id = null;
    }
    
    //-------------------------------------------------------//
    if ('addPost' == $_GET['action']) {
        $post->addPost($channel_id, $title, $text, $buttons, $files, $notification);
    } if ('editPost' == $_GET['action']) {
        $post->updatePost($post_id, $channel_id, $title, $text, $buttons, $files, $notification);
    } elseif ('deletePost' == $_GET['action']) {
        $post->deletePost($post_id);
    } elseif ('sendPost' == $_GET['action']) {
        $post->publishPost($post_id);
    }

?>