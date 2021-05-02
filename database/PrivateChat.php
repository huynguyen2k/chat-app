<?php

    class PrivateChat {
        private $chat_message_id;
        private $to_user_id;
        private $from_user_id;
        private $chat_message;
        private $timestamp;
        private $status;
        public $connect;

        public function __construct()
        {
            require_once 'DBConnection.php';

            $connectDB = new DBConnection();
            $this->connect = $connectDB->connect('localhost', 'root', '', 'chat_app');
        }

        public function setChatMessageID($chat_message_id) {
            $this->chat_message_id = $chat_message_id;
        }

        public function getChatMessageID() {
            return $this->chat_message_id;
        }

        public function setToUserID($to_user_id) {
            $this->to_user_id = $to_user_id;
        }

        public function getToUserID() {
            return $this->to_user_id;
        }

        public function setFromUserID($from_user_id) {
            $this->from_user_id = $from_user_id;
        }

        public function getFromUserID() {
            return $this->from_user_id;
        }

        public function setChatMessage($chat_message) {
            $this->chat_message = $chat_message;
        }

        public function getChatMessage() {
            return $this->chat_message;
        }

        public function setTimestamp($timestamp) {
            $this->timestamp = $timestamp;
        }

        public function getTimestamp() {
            return $this->timestamp;
        }

        public function setStatus($status) {
            $this->status = $status;
        }

        public function getStatus() {
            return $this->status;
        }

        public function getAllChatMessages() {
            $query = 'SELECT `from_user_id`, `to_user_id`, `chat_message`,
            DATE_FORMAT(`time`, "%d-%m-%Y %H:%i:%s") AS `time`, `status`
            FROM `chat_message`
            WHERE `from_user_id` = ? AND `to_user_id` = ?
            OR `from_user_id` = ? AND `to_user_id` = ?';

            $stmt = $this->connect->prepare($query);
            $stmt->bind_param('iiii', $this->from_user_id, $this->to_user_id, $this->to_user_id, $this->from_user_id);
            $stmt->execute();

            $records = $stmt->get_result();
            return $records->fetch_all(MYSQLI_ASSOC);
        }

        public function save_data() {
            $query = "INSERT INTO `chat_message`(`from_user_id`, `to_user_id`, `chat_message`, `time`, `status`)
            VALUES(?, ?, ?, ?, ?)";

            $stmt = $this->connect->prepare($query);
            $stmt->bind_param("iissi", $this->from_user_id, $this->to_user_id, $this->chat_message, $this->timestamp, $this->status);
            $stmt->execute();

            return $this->connect->insert_id;
        }

        public function update_chat_status_by_message_id() {
            $query = "UPDATE `chat_message` SET `status` = ? WHERE `chat_message_id` = ?";

            $stmt = $this->connect->prepare($query);
            $stmt->bind_param('ii', $this->status, $this->chat_message_id);
            $stmt->execute();
        }

        public function update_chat_status_by_user_id() {
            $query = "UPDATE `chat_message` SET `status` = ?
            WHERE  `status` = '0' AND `from_user_id` = ? AND `to_user_id` = ?";

            $stmt = $this->connect->prepare($query);
            $stmt->bind_param('iii', $this->status, $this->to_user_id, $this->from_user_id);
            $stmt->execute();
        }
    }

?>