<?php 
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../core/libs/telegram/telegramTools.php";

//----------------------------------------------------------------------------//
/**
 * 
 * @param type $button
 * @return type
 */
function getButtonText($button) { 
    preg_match("/(\D{1,})/u", $button, $matched);
    return trim($matched[0])." ";
}

/**
 * 
 * @param type $button
 * @return type
 */
function getButtonCount($button) { 
    preg_match("(\d+)", $button, $matched);
    return isset($matched[0]) ? (int)$matched[0] : 0;
}
//----------------------------------------------------------------------------//

$telegram   = new TelegramTools(TELEGRAM_BOT_TOKEN);
$request    = $telegram->getCallBackData();
//----------------------------------------------------------------------------//

if ( empty($request) 
    || !isset($request['from']['id'])
    || !isset($request['message']['chat']['id'])
    || !isset($request['message']['message_id'])
    || !isset($request['message']['reply_markup'])
    || !isset($request['data'])
) {
    die('Missing param');
}
 
$user_id        = $request['from']['id'];
$chat_id        = $request['message']['chat']['id'];
$message_id     = $request['message']['message_id'];
$buttons        = $request['message']['reply_markup'];
$action         = $request['data'];
//----------------------------------------------------------------------------//

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'']);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

$query = 'SELECT * FROM users_callback_meta
            WHERE user_id       = :user_id 
                AND chat_id     = :chat_id 
                AND message_id  = :message_id';
$stmt = $db->prepare($query);
$stmt->execute([
    ':user_id'      => $user_id,
    ':chat_id'      => $chat_id,
    ':message_id'   => $message_id
]);
$user_callback_meta = $stmt->fetch();
//----------------------------------------------------------------------------//

if (isset($request['message']['text'])) {
    $message = $request['message']['text'];
} elseif (isset($request['message']['caption'])) {
    $message = $request['message']['caption'];
}

$message_type = 'text';
if (isset($request['message']['caption'])) {
    $message_type = 'caption';
}

foreach ($buttons['inline_keyboard'] as &$row) {
    foreach ($row as &$button) {
        $button_text    = getButtonText($button['text']);
        $button_count   = getButtonCount($button['text']); 
        if (false == $user_callback_meta) { 
            if ($action == $button['callback_data']) {
                $button_count   = $button_count + 1;
                $button['text'] = $button_text . $button_count;
                break;    
            }
        } else {
            // Если кнопка не имеет колбеков
            if (!isset($button['callback_data'])) {
                continue;
            }
            // Если кнопка имеет колбек
            if ($user_callback_meta['callback'] == $button['callback_data'] && $action != $button['callback_data']) {
                $button_count   = (($button_count - 1) > 0) ? $button_count - 1 : 0; 
                $button['text'] = $button_text.$button_count;
            } else if ($user_callback_meta['callback'] != $button['callback_data'] && $action == $button['callback_data']) {
                $button_count   = $button_count + 1;
                $button['text'] = $button_text.$button_count;
            } else {
                continue;
            }
        }  
    }  
}
 
if ("text" == $message_type) {
    $response = $telegram->updateTextMessage($chat_id, $message_id, $message, ["reply_markup" => json_encode($buttons)]);
} elseif ("caption" == $message_type) {
    $response = $telegram->updateCaptionMessage($chat_id, $message_id, $message, ["reply_markup" => json_encode($buttons)]);
}
$response = json_decode($response, true);
//----------------------------------------------------------------------------//

if (true == $response["ok"]) {
    if (false == $user_callback_meta) {
        $query = 'INSERT INTO users_callback_meta 
                    SET user_id     = :user_id, 
                        chat_id     = :chat_id, 
                        message_id  = :message_id, 
                        callback    = :callback';
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':user_id'      => $user_id,
            ':chat_id'      => $chat_id,
            ':message_id'   => $message_id,
            ':callback'     => $action
        ]);
    } else {
        $query = 'UPDATE users_callback_meta 
                    SET callback        = :callback
                    WHERE user_id       = :user_id
                        AND chat_id     = :chat_id 
                        AND message_id  = :message_id';
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':user_id'      => $user_id,
            ':chat_id'      => $chat_id,
            ':message_id'   => $message_id,
            ':callback'     => $action
        ]);
    } 
}
exit;
?>