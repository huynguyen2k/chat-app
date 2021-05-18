<?php

    class ChatRoom {
        private $chat_id;
        private $user_id;
        private $message;
        private $created_on;

        public $connect;

        public function __construct()
        {
            require_once "DBConnection.php";

            date_default_timezone_set("Asia/Ho_Chi_Minh");

            $connectDB = new DBConnection();
            $this->connect = $connectDB->connect("localhost", "root", "", "chat_app");
        }

        public function setChatId($chat_id) {
            $this->chat_id = $chat_id;
        }

        public function getChatId() {
            return $this->chat_id;
        }

        public function setUserId($user_id) {
            $this->user_id = $user_id;
        }

        public function getUserId() {
            return $this->user_id;
        }

        public function setMessage($message) {
            $this->message = $message;
        }

        public function getMessage() {
            return $this->message;
        }

        public function setCreatedOn($created_on) {
            $this->created_on = $created_on;
        }

        public function getCreatedOn() {
            return $this->created_on;
        }

        public function save_data() {
            $query = "INSERT INTO `chat_room`(`user_id`,`message`,`created_on`) VALUES(?, ?, ?)";

            $stmt = $this->connect->prepare($query);
            $stmt->bind_param('iss', $this->user_id, $this->message, $this->created_on);
            return $stmt->execute();
        }

        public function get_all_messages() {
            $query = "SELECT `id`, `user_id`, `message`, DATE_FORMAT(`created_on`, '%d-%m-%Y %H:%i:%s') AS `date` FROM `chat_room`";

            $records = $this->connect->query($query);
            return $records->fetch_all(MYSQLI_ASSOC);
        }
    }

?>