<?php

    class ChatUser {
        private $user_id;
        private $user_name;
        private $user_username;
        private $user_password;
        private $user_profile;
        private $user_gender;
        private $user_date_of_birth;
        private $user_status;
        private $user_created_on;
        private $user_token;
        private $user_connection_id;

        public $connect;

        public function __construct() {
            require_once "DBConnection.php";

            date_default_timezone_set("Asia/Ho_Chi_Minh");

            $connectDB = new DBConnection();
            $this->connect = $connectDB->connect("localhost", "root", "", "chat_app");
        }

        public function setUserID($user_id) {
            $this->user_id = $user_id;
        }

        public function getUserID() {
            return $this->user_id;
        }

        public function setUserName($user_name) {
            $this->user_name = $user_name;
        }

        public function getUserName() {
            return $this->user_name;
        }

        public function setUserUsername($user_username) {
            $this->user_username = $user_username;
        }

        public function getUserUsername() {
            return $this->user_username;
        }

        public function setUserPassword($user_password) {
            $this->user_password = $user_password;
        }

        public function getUserPassword() {
            return $this->user_password;
        }

        public function setUserProfile($user_profile) {
            $this->user_profile = $user_profile;
        }

        public function getUserProfile() {
            return $this->user_profile;
        }

        public function setUserGender($user_gender) {
            $this->user_gender = $user_gender;
        }

        public function getUserGender() {
            return $this->user_gender;
        }

        public function setUserDateOfBirth($user_date_of_birth) {
            $this->user_date_of_birth = $user_date_of_birth;
        }

        public function getUserDateOfBirth() {
            return $this->user_date_of_birth;
        }

        public function setUserStatus($user_status) {
            $this->user_status = $user_status;
        }

        public function getUserStatus() {
            return $this->user_status;
        }

        public function setUserCreatedOn($user_created_on) {
            $this->user_created_on = $user_created_on;
        }

        public function getUserCreatedOn() {
            return $this->user_created_on;
        }

        public function get_user_data_by_username() {
            $query = "SELECT * FROM `user` WHERE `user_username` = ?";
            
            $stmt = $this->connect->prepare($query);
            $stmt->bind_param("s", $this->user_username);
            $stmt->execute();
            $records = $stmt->get_result();

            $user_data = null;
            if ($records->num_rows > 0) {
                $user_data = $records->fetch_assoc();
            }
            $stmt->close();

            return $user_data;
        }   
        public function setUserToken($user_token) {
            $this->user_token = $user_token;
        }

        public function getUserToken() {
            return $this->user_token;
        }

        public function setUserConnectionID($user_connection_id) {
            $this->user_connection_id = $user_connection_id;
        }

        public function getUserConnectionID() {
            return $this->user_connection_id;
        }
        
        public function save_data() {
            $query = "INSERT INTO `user`(`user_name`, `user_username`, `user_password`, `user_profile`,
            `user_gender`, `user_date_of_birth`, `user_status`, `user_created_on`)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->connect->prepare($query);

            $stmt->bind_param("ssssisis", $this->user_name, $this->user_username, $this->user_password,
            $this->user_profile, $this->user_gender, $this->user_date_of_birth,
            $this->user_status, $this->user_created_on);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }

            $stmt->close();
        }

        public function updateUserStatus() {
            $query = "UPDATE `user` SET `user_status` = ?, `user_token` = ? WHERE `user_id` = ?";

            $stmt = $this->connect->prepare($query);
            $stmt->bind_param("isi", $this->user_status, $this->user_token, $this->user_id);

            if ($stmt->execute()) {
                return true;
            }
            return false;
        }

        public function updateUserConnectionID() {
            $query = 'UPDATE `user` SET `user_connection_id` = ? WHERE `user_token` = ?';
            
            $stmt = $this->connect->prepare($query);
            $stmt->bind_param('is', $this->user_connection_id, $this->user_token);
            return $stmt->execute();
        }

        public function updateUserInfo() {
            $query = "UPDATE `user` SET `user_name` = ?, `user_gender` = ?,
            `user_profile` = ?, `user_date_of_birth` = ?
            WHERE `user_id` = ?";

            $stmt = $this->connect->prepare($query);
            $stmt->bind_param("sissi", $this->user_name, $this->user_gender, $this->user_profile, $this->user_date_of_birth, $this->user_id);
            $stmt->execute();
            $stmt->close();
        }

        public function saveUserInfoIntoSession() {
            session_start();

            $_SESSION['user-data'] = [
                "user_id" => $this->user_id,
                "user_name" => $this->user_name,
                "user_profile" => $this->user_profile,
                "user_gender" => $this->user_gender,
                "user_date_of_birth" => $this->user_date_of_birth,
                "user_token" => $this->user_token
            ];
        }

        public function get_all_user_data_with_message_status() {
            $query = "SELECT `user_id`, `user_name`, `user_profile`, `user_status`,
            (SELECT COUNT(*) FROM `chat_message` WHERE `to_user_id` = ?
            AND `from_user_id` = `user`.`user_id` AND `status` = 0)
            AS `count_message`
            FROM `user`";

            $stmt = $this->connect->prepare($query);
            $stmt->bind_param('i', $this->user_id);
            $stmt->execute();
            $records = $stmt->get_result();
            return $records->fetch_all(MYSQLI_ASSOC);
        }

        public function get_user_data_by_id() {
            $query = "SELECT * FROM `user` WHERE `user_id` = ?";

            $stmt = $this->connect->prepare($query);
            $stmt->bind_param('i', $this->user_id);
            $stmt->execute();

            return $stmt->get_result()->fetch_assoc();
        }

        public function get_user_id_by_token() {
            $query = "SELECT `user_id` FROM `user` WHERE `user_token` = ?";

            $stmt = $this->connect->prepare($query);
            $stmt->bind_param('s', $this->user_token);
            $stmt->execute();

            return $stmt->get_result()->fetch_assoc()['user_id'];
        }
    }
?>