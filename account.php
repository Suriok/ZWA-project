<?php
session_start();
require 'session_check.php';

$user = $_SESSION['user'];
$photo_mime = $user['photo'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_out'])) {
    session_unset(); // Удаляем все переменные сессии
    session_destroy(); // Завершаем сессию
    header("Location: index.php"); // Перенаправляем на главную страницу
    exit();
}
// Если при обработке POST нужно что-то обновлять/сохранять,
// вы можете это сделать выше (перед выводом HTML).
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="foto/favicon.svg" sizes="48x48" />
    <script src="js/card.js" defer></script>
</head>
<body>
<header>
    <div class="logo_account">
        <div class="logo">
            <a class="logo" href="index.php">
                <img class="logo_image" alt="logo-hearte" src="foto/logo-hearte.png">
                <div class="logo_text">
                    <div class="logo_text_main">FlAME</div>
                    <div class="logo_text_small">FIND YOUR LOVE HERE</div>
                </div>
            </a>
        </div>
        <img class="profil_image" alt="profil foto" src="foto/profile.png">
        <div class="burger-menu" id="burgerMenu">
            <a href="log_in.php">Log In</a>
            <a href="register.php">Registration</a>
        </div>
    </div>
    <hr class="line_header">
</header>
<section class="main_part">
    <div class="profil_information">
        <div class="profil_main_text">
            <h1 class="profil_main_text">Profile Information</h1>
        </div>
        <div class="foto_name_surname">
            <div class="user_account_img">
                <img class="img_user" alt="account_user"
                     src="data:<?php echo htmlspecialchars($user['photo_mime'], ENT_QUOTES, 'UTF-8'); ?>;base64,<?php echo htmlspecialchars($user['photo'], ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>

            <form class="account_form">
                <label for="name" class="information_text">Name</label>
                <input class="input_inf" readonly
                       id="name"
                       type="text"
                       name="name"
                       value="<?php echo htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >

                <label for="surname" class="information_text">Surname</label>
                <input class="input_inf" readonly
                       id="surname"
                       type="text"
                       name="surname"
                       value="<?php echo htmlspecialchars($user['surname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >

                <label for="email" class="information_text">Email</label>
                <input class="input_inf" readonly
                       id="email"
                       type="email"
                       name="email"
                       value="<?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
            </form>
        </div>
        <div class="div_bio">
            <form class="account_form">
                <label for="bio" class="bio_text">Bio</label>
                <textarea class="input_inf_bio" readonly id="bio" name="bio"><?php echo trim(htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8')); ?></textarea>
            </form>
        </div>
        <div class="information_button">
            <form method="POST" action="account.php">
                <button class="edit_profil" type="submit" name="log_out">Log Out</button>
            </form>
        </div>
    </div>
</section>
<footer>
    <div class="swip">
        <a class="swip" href="index.php">
            <h5 class="footer_text">Swipe</h5>
        </a>
    </div>
    <div class="people">
        <a class="people" href="people.php">
            <svg fill="#ffffff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"
                 class="image_people">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <path d="M16.03,18.616l5.294-4.853a1,1,0,0,1,1.352,1.474l-6,5.5a1,1,0,0,1-1.383-.03l-3-3a1,1,0,0,1,1.414-1.414ZM1,20a9.01,9.01,0,0,1,5.623-8.337A4.981,4.981,0,1,1,10,13a7.011,7.011,0,0,0-6.929,6H10a1,1,0,0,1,0,2H2A1,1,0,0,1,1,20ZM7,8a3,3,0,1,0,3-3A3,3,0,0,0,7,8Z"></path>
                </g>
            </svg>
            <h5 class="footer_text">People</h5>
        </a>
    </div>
</footer>
</body>
</html>
