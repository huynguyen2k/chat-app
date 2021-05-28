<?php
    session_start();

    if (isset($_SESSION["user-data"])) {
        header("location: chatroom.php");
    }

    if (!empty($_POST)) {

        require_once "database/ChatUser.php";

        $user_object = new ChatUser;

        $user_object->setUserName($_POST["userName"]);
        $user_object->setUserUsername($_POST["userUsername"]);
        $user_object->setUserPassword(md5($_POST["userPassword"]));
        $user_object->setUserStatus(0);
        $user_object->setPhoneNumber($_POST['phoneNumber']);
        $user_object->setUserProfile("assets/images/user.png");
        $user_object->setUserCreatedOn(date("Y-m-d H:i:s"));
        $user_object->setUserDateOfBirth($_POST['userDateOfBirth']);
        $user_object->setUserGender($_POST['userGender']);

        $user_data = $user_object->get_user_data_by_username();

        if ($user_data != null) {
            $error_message = '<p class="form-control__message form-control__message--error">Tài khoản này đã được đăng ký</p>';
        } else {
            if ($user_object->save_data()) {
                $success_message = '<p class="form-control__message form-control__message--success">Bạn đã đăng ký thành công</p>';
            } else {
                $error_message = '<p class="form-control__message form-control__message--error">Xảy ra lỗi trong quá trình đăng ký</p>';
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <!-- link css -->
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/register.css">
</head>

<body>

    <div class="container">
        <div class="form-wrapper">
            <!-- Start Register Form -->
            <form action="" method="post" enctype="multipart/form-data" class="form-control form-control--white" id="register-form">
                <div class="form-control__image">
                    <img src="assets/images/register-img.jpg" alt="Image">
                </div>
                <div class="form-control__content">
                    <?php
                        if (isset($error_message)) {
                            echo $error_message;
                        }
                        if (isset($success_message)) {
                            echo $success_message;
                        }
                    ?>

                    <h3 class="form-control__title" id="register-form-title">Sign up for Account</h3>
                    <div class="form-control__row">
                        <input class="form-control__input-data" type="text" name="userName" placeholder="Your full name" required>
                    </div>
                    <div class="form-control__row">
                        <input class="form-control__input-data" type="text" name="userUsername" placeholder="Enter username" required>
                    </div>
                    <div class="form-control__row">
                        <input class="form-control__input-data" type="password" id="userPassword" name="userPassword" placeholder="Enter password" required>
                    </div>
                    <div class="form-control__row">
                        <input class="form-control__input-data" type="password" id="userConfirmPassword" placeholder="Confirm password" required>
                    </div>
                    <div class="form-control__row">
                        <input class="form-control__input-data" type="text" id="phoneNumber" name="phoneNumber" placeholder="Enter phone number" required>
                    </div>
                    <div class="form-control__row">
                        <label class="form-control__label">Choose gender</label>
                        <div class="form-control__wrap-data">
                            <label for="male">Male</label>
                            <input type="radio" name="userGender" value="1" id="male" required>
                            <label for="female">Female</label>
                            <input type="radio" name="userGender" value="0" id="female" required>
                        </div>
                    </div>
                    <div class="form-control__row">
                        <label class="form-control__label" for="userDateOfBirth">Date of birth</label>
                        <input class="form-control__input-data" type="date" name="userDateOfBirth" id="userDateOfBirth" required>
                    </div>
                    <div class="form-control__row text-center">
                        <input class="button button--green" type="submit" value="Register">
                        <a class="button button--red" href="index.php">Login</a>
                    </div>
                </div>
            </form>
            <!-- End Register Form -->
        </div>
    </div>
    
    <!-- link js -->
    <script src="assets/js/form_validate.js"></script>
</body>

</html>