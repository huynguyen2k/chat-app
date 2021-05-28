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
                <div class="profile" login-id="<?php echo $_SESSION['user-data']['user_id'] ?>">
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
                        <p class="profile__text">Gender:
                            <?php
                            $user_gender = $_SESSION['user-data']['user_gender'];
                            if ($user_gender) {
                                $user_gender = "Male";
                            } else {
                                $user_gender = "Female";
                            }
                            echo $user_gender;
                            ?>
                        </p>
                    </div>
                    <div class="profile__row">
                        <p class="profile__text">Date of birth:
                            <?php
                            $date = new DateTime($_SESSION['user-data']['user_date_of_birth']);
                            echo $date->format('d-m-Y');
                            ?>
                        </p>
                    </div>
                    <div class="profile__row">
                        <a href="edit.php" class="button button--small button--green">Edit profile</a>
                        <a href="logout.php" id="logout-btn" class="button button--small button--red">Sign out</a>
                    </div>
                </div>
                <div class="user-list">
                    <div class="group-chat" id="group-chat">
                        <div class="group-chat__image">
                            <img src="assets/images/group-icon.png" alt="Group icon">
                        </div>
                        <span class="group-chat__name">Group chat</span>
                    </div>
                    <?php echo $userList ?>
                </div>
            </div>
            <div class="chat-room__main" id='chat-room-body'>
                <h1 class="title">Chat App</h1>
            </div>
        </div>
    </div>

    <script>
        const logoutBtn = document.getElementById('logout-btn');
        const users = document.querySelectorAll('.user');
        const chatRoomBody = document.getElementById('chat-room-body');
        const fromUserID = document.querySelector('.profile').getAttribute('login-id');
        const groupChat = document.getElementById('group-chat');


        const requestObject = {
            from_user_id: fromUserID
        };

        var conn = new WebSocket('ws://localhost:8080?token=<?php echo $user_token; ?>');

        conn.onopen = function(e) {
            console.log("Connection established!");
        };

        conn.onmessage = function(e) {
            const data = JSON.parse(e.data);

            if (data.type === 'update-user-status') {

                const userLogout = document.querySelector(`.user[user_id="${data.user_id}"]`);

                if (userLogout === null) {
                    return;
                }

                if (data.user_status) {
                    userLogout.querySelector('.user__status').classList.add('user__status--active');
                } else {
                    userLogout.querySelector('.user__status').classList.remove('user__status--active');
                }


            } else if (data.type === 'private-chat') {
                const chatArea = document.querySelector('.chat-area');
                const loginID = document.querySelector('.profile').getAttribute('login-id');

                if (chatArea != null) {
                    if (loginID == data.fromUserID) {
                        const messageArray = [{
                            from_user_id: data.fromUserID,
                            to_user_id: data.toUserID,
                            chat_message: data.message,
                            time: data.timestamp
                        }];
                        showMessages(messageArray);
                    } else if (loginID == data.toUserID &&
                        chatArea.getAttribute('user-id') == data.fromUserID) {
                        const messageArray = [{
                            from_user_id: data.fromUserID,
                            to_user_id: data.toUserID,
                            chat_message: data.message,
                            time: data.timestamp
                        }];
                        showMessages(messageArray);
                        updateMessageAlreadyRead(data);
                    } else {
                        const user = document.querySelector(`.user[user_id="${data.fromUserID}"]`);
                        let unreadMessage = user.querySelector('.user__unread-message');

                        if (unreadMessage != null) {
                            unreadMessage.innerHTML = parseInt(unreadMessage.innerHTML) + 1;
                        } else {
                            const unreadElement = document.createElement('div');
                            unreadElement.classList.add('user__unread-message');
                            unreadElement.innerHTML = '1';
                            user.appendChild(unreadElement);
                        }
                    }
                } else {
                    const user = document.querySelector(`.user[user_id="${data.fromUserID}"]`);
                    let unreadMessage = user.querySelector('.user__unread-message');

                    if (unreadMessage != null) {
                        unreadMessage.innerHTML = parseInt(unreadMessage.innerHTML) + 1;
                    } else {
                        const unreadElement = document.createElement('div');
                        unreadElement.classList.add('user__unread-message');
                        unreadElement.innerHTML = '1';
                        user.appendChild(unreadElement);
                    }
                }
            } else if (data.type === 'group-chat') {
                const chatArea = document.querySelector('.chat-area');
                const loginID = document.querySelector('.profile').getAttribute('login-id');

                if (chatArea != null) {
                    const message = [data];

                    if (loginID == data.user_id || chatArea.getAttribute('user-id') == 'null') {
                        showGroupChatMessages(message);
                    } else {
                        const groupChat = document.getElementById('group-chat');
                        let unreadMessage = groupChat.querySelector('.group-chat__unread-message');

                        if (unreadMessage != null) {
                            unreadMessage.innerHTML = parseInt(unreadMessage.innerHTML) + 1;
                        } else {
                            const unreadElement = document.createElement('div');
                            unreadElement.classList.add('group-chat__unread-message');
                            unreadElement.innerHTML = '1';
                            groupChat.appendChild(unreadElement);
                        }
                    }
                } else {
                    const groupChat = document.getElementById('group-chat');
                    let unreadMessage = groupChat.querySelector('.group-chat__unread-message');

                    if (unreadMessage != null) {
                        unreadMessage.innerHTML = parseInt(unreadMessage.innerHTML) + 1;
                    } else {
                        const unreadElement = document.createElement('div');
                        unreadElement.classList.add('group-chat__unread-message');
                        unreadElement.innerHTML = '1';
                        groupChat.appendChild(unreadElement);
                    }
                }
            }

        };

        conn.onclose = function(e) {
            console.log("Connection closed");
        }

        function updateMessageAlreadyRead(requestObject) {
            const httpRequest = new XMLHttpRequest();

            httpRequest.open('POST', 'action.php', true);
            httpRequest.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            httpRequest.send('update-message=' + JSON.stringify(requestObject));
        }

        function createChatArea(userID, userImg, userName) {
            const element = document.createElement('div');
            element.classList.add('chat-area');

            element.setAttribute('user-id', userID);

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
            });
        }

        function clearActiveUser() {
            users.forEach(item => item.classList.remove('user--active'));
        }

        function clearActiveGroupChat() {
            groupChat.classList.remove('group-chat--active');
        }

        function showPrivateChatArea() {
            let toUserID = this.getAttribute('user_id');
            let userName = this.querySelector('.user__name').innerHTML;
            let userImg = this.querySelector('img').getAttribute('src');
            let userID = this.getAttribute('user_id');

            const chatAreaElement = createChatArea(userID, userImg, userName);

            requestObject.to_user_id = toUserID;

            clearChatArea();
            clearActiveUser();
            clearActiveGroupChat();

            this.classList.add('user--active');
            chatRoomBody.appendChild(chatAreaElement);
            let closeBtn = chatAreaElement.querySelector('.close-btn');

            closeBtn.addEventListener('click', function() {
                clearActiveUser();
                this.closest('.chat-area').remove();
            })

            sendAjaxRequest(showMessages, 'action.php', 'fetch-messages');
            const unreadMessage = this.querySelector('.user__unread-message');
            if (unreadMessage != null) {
                unreadMessage.remove();
            }

            const chatForm = document.getElementById('chat-form');

            chatForm.onsubmit = function(e) {
                e.preventDefault();
                const data = {
                    fromUserID: requestObject.from_user_id,
                    toUserID: requestObject.to_user_id,
                    message: document.getElementById('chat-message').value,
                    type: 'private-chat'
                };
                conn.send(JSON.stringify(data));
                this.querySelector('#chat-message').value = '';
            }
        }

        function showGroupChatArea() {
            let name = this.querySelector('.group-chat__name').innerHTML;
            let image = this.querySelector('img').getAttribute('src');

            const chatAreaElement = createChatArea(null, image, name);

            clearChatArea();
            clearActiveUser();

            this.classList.add('group-chat--active');
            chatRoomBody.appendChild(chatAreaElement);

            let closeBtn = chatAreaElement.querySelector('.close-btn');

            closeBtn.addEventListener('click', function() {
                clearActiveGroupChat();
                this.closest('.chat-area').remove();
            });

            sendAjaxRequest(showGroupChatMessages, 'action.php', 'fetch-group-chat-messages');

            const unreadMessage = this.querySelector('.group-chat__unread-message');
            if (unreadMessage != null) {
                unreadMessage.remove();
            }

            const chatForm = document.getElementById('chat-form');

            chatForm.onsubmit = function(e) {
                e.preventDefault();
                const data = {
                    user_id: requestObject.from_user_id,
                    message: document.getElementById('chat-message').value,
                    type: 'group-chat'
                };
                conn.send(JSON.stringify(data));
                this.querySelector('#chat-message').value = '';
            }
        }

        groupChat.onclick = showGroupChatArea;

        users.forEach(item => {
            item.addEventListener('click', showPrivateChatArea);
        });


        function createMessage(messageObject) {
            const messageElement = document.createElement('div');
            messageElement.setAttribute('class', 'message');

            if (requestObject.from_user_id == messageObject.from_user_id) {
                messageElement.classList.add('from-message');
            } else {
                messageElement.classList.add('to-message');
            }

            messageElement.innerHTML = `
                <p class="message-wrap">
                    <span class="message-content">${messageObject.chat_message}</span>
                    <span class="message-time">${messageObject.time}</span>
                </p>
            `;
            return messageElement;
        }

        function showMessages(messagesArray) {
            const chatBodyElement = document.querySelector('.chat-area__body');
            for (let message of messagesArray) {
                chatBodyElement.appendChild(createMessage(message));
            }
            chatBodyElement.scrollTo(0, 100000);
        }

        function createGroupChatMessage(message) {
            let username = '';
            const messageElement = document.createElement('div');
            messageElement.setAttribute('class', 'message');

            if (message.user_id == requestObject.from_user_id) {
                messageElement.classList.add('from-message');

                messageElement.innerHTML = `
                <p class="message-wrap">
                    <span class="message-content">${message.message}</span>
                    <span class="message-time">${message.date}</span>
                </p>`;
                
            } else {
                username = document.querySelector(`.user[user_id="${message.user_id}"] .user__name`)
                .innerHTML;

                messageElement.classList.add('to-message');
                messageElement.innerHTML = `
                <p class="message-wrap">
                    <span class="user-chat">${username}</span>
                    <span class="message-content">${message.message}</span>
                    <span class="message-time">${message.date}</span>
                </p>`;
            }

            return messageElement;
        }

        function showGroupChatMessages(messagesArray) {
            const chatBodyElement = document.querySelector('.chat-area__body');

            for (let message of messagesArray) {
                chatBodyElement.appendChild(createGroupChatMessage(message));
            }
            chatBodyElement.scrollTo(0, 100000);
        }

        function sendAjaxRequest(callback, url, requestName) {
            httpRequest = new XMLHttpRequest();
            httpRequest.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    callback(JSON.parse(this.responseText));
                }
            }
            httpRequest.open('POST', url, true);
            httpRequest.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            httpRequest.send(requestName + '=' + JSON.stringify(requestObject));
        }

        logoutBtn.onclick = function(e) {
            e.preventDefault();

            conn.close();
            setTimeout(function() {
                window.location = 'logout.php';
            }, 200);
        }
    </script>
</body>

</html>