<?php
    require_once 'database/PrivateChat.php';
    require_once 'database/ChatUser.php';
    require_once 'database/ChatRoom.php';

    session_start();
    if (!isset($_SESSION['user-data'])) {
        header('location: index.php');
    }

    if (!empty($_POST) && isset($_POST['fetch-messages'])) {

        $request_object = json_decode($_POST['fetch-messages'], false);
        $private_chat = new PrivateChat();
        $private_chat->setFromUserID($request_object->from_user_id);
        $private_chat->setToUserID($request_object->to_user_id);
        $private_chat->setStatus(1);
        $private_chat->update_chat_status_by_user_id();

        echo json_encode($private_chat->getAllChatMessages());
    }

    if (!empty($_POST) && isset($_POST['update-message'])) {
        $request_object = json_decode($_POST['update-message'], false);
        $private_chat = new PrivateChat();
        $private_chat->setChatMessageID($request_object->chat_message_id);
        $private_chat->setStatus(1);
        $private_chat->update_chat_status_by_message_id();
    }

    if (!empty($_POST) && isset($_POST['fetch-group-chat-messages'])) {
        $request_object = json_decode($_POST['fetch-group-chat-messages'], false);

        $chat_room = new ChatRoom();
        $messages = $chat_room->get_all_messages();
        
        echo json_encode($messages);
    }
?>