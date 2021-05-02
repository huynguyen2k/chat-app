<?php

namespace MyApp;

use PrivateChat;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once dirname(__DIR__) . '/database/ChatUser.php';
require_once dirname(__DIR__) . '/database/PrivateChat.php';

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $query_string = $conn->httpRequest->getUri()->getQuery();
        parse_str($query_string, $query_array);

        $user_object = new \ChatUser();
        $user_object->setUserToken($query_array['token']);
        $user_object->setUserConnectionID($conn->resourceId);
        $user_object->updateUserConnectionID();

        $user_id = $user_object->get_user_id_by_token();
        $data = Array (
            'user_id' => $user_id,
            'user_status' => 1
        );

        foreach ($this->clients as $client) {
            $client->send(json_encode($data));
        }
        
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $data = json_decode($msg, true);
        if ($data['type'] === 'private-chat') {
            $private_chat = new \PrivateChat();

            $private_chat->setFromUserID($data['fromUserID']);
            $private_chat->setToUserID($data['toUserID']);
            $private_chat->setChatMessage($data['message']);

            $timestamp = date('Y-m-d H:i:s');
            $data['timestamp'] = date('d-m-Y H:i:s', strtotime($timestamp));
            $private_chat->setTimestamp($timestamp);
            $private_chat->setStatus(0);
            $chat_message_id = $private_chat->save_data();
            $data['chat_message_id'] = $chat_message_id;

            $user_object = new \ChatUser();
            $user_object->setUserID($data['fromUserID']);
            $from_user_data = $user_object->get_user_data_by_id();
            $user_object->setUserID($data['toUserID']);
            $to_user_data = $user_object->get_user_data_by_id();

            foreach ($this->clients as $client) {
                if ($from == $client || $client->resourceId == $to_user_data['user_connection_id']) {
                    $client->send(json_encode($data));
                }
            }
        }

        // foreach ($this->clients as $client) {
        //     if ($from !== $client) {
        //         // The sender is not the receiver, send to each client connected
        //         $client->send($msg);
        //     }
        // }
    }

    public function onClose(ConnectionInterface $conn) {
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryArray);

        $user_object = new \ChatUser();
        $user_object->setUserToken($queryArray['token']);
        $user_id = $user_object->get_user_id_by_token();
        
        $data = Array (
            'user_id' => $user_id,
            'user_status' => 0
        );

        foreach ($this->clients as $client) {
            $client->send(json_encode($data));
        }

        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
?>