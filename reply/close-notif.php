<?php

$message_id = $bot->getCallbackMessageId();

if ($message_id) {
    $bot->deleteMessage($chat_id, $message_id);
}

?>
