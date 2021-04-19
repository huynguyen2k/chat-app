<?php
session_start();

if (!isset($_SESSION['user-data'])) {
    header("location: index.php");
}

require_once 'database/ChatUser.php';

$user_object = new ChatUser();
$user_object->setUserID($_SESSION['user-data']['user_id']);
$login_user_id = $user_object->getUserID();
$user_token = $_SESSION['user-data']['user_token'];
$all_user_data = $user_object->get_all_user_data_with_message_status();

$userList = '';
foreach ($all_user_data as $key => $value) {

    if ($value['user_id'] != $login_user_id) {
        $user_status = '<span class="user__status"></span>';
        $count_message = '';

        if ($value['user_status'] == 1) {
            $user_status = '<span class="user__status user__status--active"></span>';
        }
        if ($value['count_message'] > 0) {
            $count_message = '<span class="user__unread-message">' . $value['count_message'] . '</span>';
        }

        $user = '<div class="user" user_id="' . $value['user_id'] . '">
                        <div class="user__image">
                            <img src="' . $value['user_profile'] . '" alt="User image">
                        </div>
                        <span class="user__name">' . $value['user_name'] . '</span>
                        ' . $count_message . '
                        ' . $user_status . '
                    </div>';
        $userList .= $user;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat app</title>

    <!-- link css -->
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/chatroom.css">

    <!-- link font-awesome -->
    <script src="https://kit.fontawesome.com/15e239e756.js" crossorigin="anonymous"></script>

</head>

<body>

    <div class="container">
        <div class="chat-room">
            <div class="chat-room__side-bar">
                <div class="profile">
                    <div class="profile__row">
                        <div class="profile__image">
                            <img src="<?php echo $_SESSION['user-data']['user_profile'] ?>" alt="User icon">
                        </div>
                    </div>
                    <div class="profile__row">
                        <p class="profile__text">
                            <?php echo $_SESSION['user-data']['user_name'] ?>
                        </p>
                    </div>
                    <div class="profile__row">
                        <p class="profile__text">Giới tính:
                            <?php
                            $user_gender = $_SESSION['user-data']['user_gender'];
                            if ($user_gender) {
                                $user_gender = "Nam";
                            } else {
                                $user_gender = "Nữ";
                            }
                            echo $user_gender;
                            ?>
                        </p>
                    </div>
                    <div class="profile__row">
                        <p class="profile__text">Ngày sinh:
                            <?php
                            $date = new DateTime($_SESSION['user-data']['user_date_of_birth']);
                            echo $date->format('d-m-Y');
                            ?>
                        </p>
                    </div>
                    <div class="profile__row">
                        <a href="edit.php" class="button button--small button--green">Chỉnh sửa</a>
                        <a href="logout.php" class="button button--small button--red">Đăng xuất</a>
                    </div>
                </div>
                <div class="user-list">
                    <?php echo $userList ?>
                </div>
            </div>
            <div class="chat-room__main" id='chat-room-body'>
                <h1 class="title">Chat App</h1>
            </div>
        </div>
    </div>

    <script>
        var conn = new WebSocket('ws://localhost:8080?token=<?php echo $user_token; ?>');

        conn.onopen = function(e) {
            console.log("Connection established!");
        };

        conn.onmessage = function(e) {
            console.log(e.data);
        };

        conn.onclose = function(e) {
            console.log("Connection closed");
        }

        const users = document.querySelectorAll('.user');
        const chatRoomBody = document.getElementById('chat-room-body');

        function createChatArea(userImg, userName) {
            const element = document.createElement('div');
            element.classList.add('chat-area');
            
            let html = `
                <div class="chat-area__header">
                    <img src="${userImg}" alt="User icon">
                    <span>${userName}</span>
                    <span class="close-btn"><i class="far fa-times-circle"></i></span>
                </div>
                <div class="chat-area__body"></div>
                <form id="chat-form" class="chat-form" method="post">
                    <input type="text" name="chat_message" id="chat-message" class="chat-message" placeholder="Nhập tin nhắn ở đây" required>
                    <button class="submit-btn" type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            `;
            element.innerHTML = html;
            return element;
        }

        function clearChatArea() {
            chatArea = document.querySelectorAll('.chat-area');
            
            chatArea.forEach(item => {
                item.remove();
            })
        }

        function clearActiveUser() {
            users.forEach(item => item.classList.remove('user--active'));
        }

        users.forEach(item => {
            item.addEventListener('click', function() {
                let userName = this.querySelector('.user__name').innerHTML;
                let userImg = this.querySelector('img').getAttribute('src');
                const chatAreaElement = createChatArea(userImg, userName);
                
                clearChatArea();
                clearActiveUser();
                this.classList.add('user--active');
                chatRoomBody.appendChild(chatAreaElement);
                let closeBtn = chatAreaElement.querySelector('.close-btn');

                closeBtn.addEventListener('click', function() {
                    clearActiveUser();
                    this.closest('.chat-area').remove();
                })
            });
        });

    </script>
</body>

</html>