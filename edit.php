<?php

    session_start();
    if (!isset($_SESSION['user-data'])) {
        header('location: index.php');
    }

    if (!empty($_POST)) {
        require_once 'database/ChatUser.php';

        $user_object = new ChatUser();

        $user_object->setUserID($_SESSION['user-data']['user_id']);
        $user_object->setUserName($_POST['userName']);
        $user_object->setUserGender($_POST['userGender']);
        $user_object->setUserDateOfBirth($_POST['userDateOfBirth']);
        $user_object->setUserToken($_SESSION['user-data']['user_token']);

        if ($_FILES['userProfile']['name'] != '') {
            $fileExtension = pathinfo($_FILES['userProfile']['name'], PATHINFO_EXTENSION);
            $file = 'assets/images/' . md5(uniqid()) . '.' . $fileExtension;

            move_uploaded_file($_FILES['userProfile']['tmp_name'], $file);
            $user_object->setUserProfile($file);
        } else {
            $user_object->setUserProfile($_SESSION['user-data']['user_profile']);
        }

        $user_object->updateUserInfo();
        $user_object->saveUserInfoIntoSession();

        header('location: chatroom.php');
    }

    $user_id = $_SESSION['user-data']['user_id'];
    $user_profile = $_SESSION['user-data']['user_profile'];
    $user_name = $_SESSION['user-data']['user_name'];
    $user_gender = $_SESSION['user-data']['user_gender'];
    $user_date_of_birth = $_SESSION['user-data']['user_date_of_birth'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa thông tin cá nhân</title>

    <!-- link css -->
    <link rel="stylesheet" href="assets/css/reset.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/register.css">
</head>

<body>

    <div class="container">
        <div class="form-wrapper">
            <!-- Start Register Form -->
            <form action="" method="post" enctype="multipart/form-data" class="form-control form-control--white">
                <div class="form-control__image">
                    <img src="assets/images/register-img.jpg" alt="Image">
                </div>
                <div class="form-control__content">
                    <h3 class="form-control__title">Thông tin cá nhân</h3>
                    <div class="form-control__row">
                        <label class="form-control__label">Ảnh đại diện</label>
                        <div class="form-control__profile-image">
                            <img src="<?php echo $user_profile ?>" alt="User image">
                            <input type="file" name="userProfile">
                        </div>
                    </div>
                    <div class="form-control__row">
                        <label class="form-control__label">Họ tên</label>
                        <input class="form-control__input-data" type="text" name="userName" required value="<?php echo $user_name ?>">
                    </div>
                    <div class="form-control__row">
                        <label class="form-control__label">Giới tính</label>
                        <div class="form-control__wrap-data">
                            <label for="male">Nam</label>
                            <input type="radio" name="userGender" value="1" id="male" required <?php echo ($user_gender == 1 ? "checked" : "") ?>>
                            <label for="female">Nữ</label>
                            <input type="radio" name="userGender" value="0" id="female" required <?php echo ($user_gender == 0 ? "checked" : "") ?>>
                        </div>
                    </div>
                    <div class="form-control__row">
                        <label class="form-control__label" for="userDateOfBirth">Ngày sinh</label>
                        <input class="form-control__input-data" type="date" name="userDateOfBirth" id="userDateOfBirth" value="<?php echo $user_date_of_birth ?>" required>
                    </div>
                    <div class="form-control__row text-center">
                        <input class="button button--green" type="submit" value="Lưu lại">
                        <a class="button button--red" href="chatroom.php">Quay về</a>
                    </div>
                </div>
            </form>
            <!-- End Register Form -->
        </div>
    </div>
</body>

</html>