<?php
    require_once 'database/ChatUser.php';

    function random_string($number) {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $size = strlen( $chars );
        $str="";

        for( $i = 0; $i < $number; $i++ ) {
            $str .= $chars[ rand( 0, $size - 1 ) ];
        }
        
        return $str;
    }

    if (!empty($_POST)) {
        $username = $_POST['username'];
        $phoneNumber = $_POST['phone-number'];

        $user_object = new ChatUser();
        $user_object->setUserUsername($username);

        $data = $user_object->get_user_data_by_username();

        if ($data != null) {
            if ($phoneNumber === $data['phone_number']) {

                $password = random_string(10);

                $user_object->setUserPassword($password);
                $user_object->updatePasswordByUsername();

                $error_message = '<p class="form-control__message form-control__message--success">New password is: '. $password .'</p>';
            
            } else {
                $error_message = '<p class="form-control__message form-control__message--error">Phone number is not valid</p>';
            }

        } else {
            $error_message = '<p class="form-control__message form-control__message--error">Username is not valid</p>';
        }
    }

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>

    <!-- link css -->
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/register.css">


</head>

<body>

    <div class="container">
        <div class="form-wrapper" style="width: 800px">
            <!-- Start Login Form -->
            <form action="" method="post" enctype="multipart/form-data" class="form-control form-control--white" id="forgot-password-form">
                <div class="form-control__image">
                    <img src="assets/images/register-img.jpg" alt="Image">
                </div>
                <div class="form-control__content">
                    <?php
                    if (isset($error_message)) {
                        echo $error_message;
                    }
                    ?>
                    <h3 class="form-control__title" id="register-form-title">Get password</h3>
                    <div class="form-control__row">
                        <input class="form-control__input-data" type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-control__row">
                        <input class="form-control__input-data" type="text" id="phone-number" name="phone-number" placeholder="Phone number" required>
                    </div>
                    <div class="form-control__row text-center">
                        <input class="button button--green" type="submit" value="Confirm">
                        <a class="button button--red" href="index.php">Cancel</a>
                    </div>
                </div>
            </form>
            <!-- End Register Form -->
        </div>
    </div>

</body>

</html>