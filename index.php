<?php
    session_start();

    if (isset($_SESSION["user-data"])) {
        header("location: chatroom.php");
    }

    if (!empty($_POST)) {
        require_once "database/ChatUser.php";

        $user_object = new ChatUser();

        $user_object->setUserUsername($_POST["userUsername"]);
        $user_data = $user_object->get_user_data_by_username();

        if ($user_data != null) {
            if ($user_data["user_password"] == md5($_POST["userPassword"])) {

                $user_object->setUserID($user_data["user_id"]);
                $user_object->setUserStatus(1);

                $user_token = md5(uniqid());
                $user_object->setUserToken($user_token);

                if ($user_object->updateUserStatus()) {
                    $_SESSION['user-data'] = [
                        "user_id" => $user_data["user_id"],
                        "user_name" => $user_data["user_name"],
                        "user_profile" => $user_data["user_profile"],
                        "user_gender" => $user_data["user_gender"],
                        "user_date_of_birth" => $user_data["user_date_of_birth"],
                        "user_token" => $user_token
                    ];
                    header("location: chatroom.php");
                }

            } else {
                $error_message = '<p class="form-control__message form-control__message--error">Mật khẩu không hợp lệ</p>';
            }
        } else {
            $error_message = '<p class="form-control__message form-control__message--error">Tài khoản không hợp lệ</p>';
        }
    }

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- link css -->
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/register.css">
</head>

<body>

    <div class="container login-bg">
        <div class="form-wrapper" style="width: 800px">
            <div class="header">
                <p>Welcome to our site</p>
                <p>Come freely - Go safely - And leave with  </p>
                <p>happiness you have with friends.</p>
            </div>

            <!-- Start Login Form -->
            <form action="" method="post" enctype="multipart/form-data" class="form-control form-control--white" id="login-form">
                <div class="form-control__image">
                    <img src="assets/images/login.jpg" alt="Image">
                </div>
                <div class="form-control__content">
                    <?php
                        if (isset($error_message)) {
                            echo $error_message;
                        }
                    ?>
                    <h3 class="form-control__title" id="register-form-title">Login</h3>
                    <div class="form-control__row">
                        <input class="form-control__input-data" type="text" name="userUsername" placeholder="Username" required>
                    </div>
                    <div class="form-control__row">
                        <input class="form-control__input-data" type="password" id="userPassword" name="userPassword" placeholder="Password" required>
                    </div>
                    <div class="form-control__row text-center">
                        <input class="button button--green" type="submit" value="Login">
                        <a class="button button--red" href="register.php">Sign up</a>
                    </div>
                </div>
            </form>
            <!-- End Register Form -->
        </div>
    </div>
</body>

</html>