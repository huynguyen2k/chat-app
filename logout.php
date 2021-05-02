<?php

    session_start();
    if (isset($_SESSION['user-data'])) {
        require_once 'database/ChatUser.php';

        $user_object = new ChatUser();
        $user_object->setUserID($_SESSION['user-data']['user_id']);
        $user_object->setUserStatus(0);
        $user_object->updateUserStatus();

        unset($_SESSION['user-data']);
    }    
    header('location: index.php');
?>