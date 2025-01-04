<?php
session_start();

$isLoggedIn = isset($_SESSION['current_user_email']); // Проверка авторизации

function calculateAge($dateOfBirth) {
    try {
        $dob = new DateTime($dateOfBirth);
        $now = new DateTime();
        return $now->diff($dob)->y;
    } catch (Exception $e) {
        return null; // Если дата некорректна
    }
}

$users = [];
$userFile = 'user.json';
if (file_exists($userFile)) {
    $fileData = file_get_contents($userFile);
    $users = json_decode($fileData, true);
    if (!is_array($users)) {
        $users = []; // Если содержимое файла некорректно, используем пустой массив
    }
}

$loggedInUserHashedEmail = null;
if ($isLoggedIn && isset($_SESSION['current_user_email'])) {
    $loggedInUserEmail = $_SESSION['current_user_email'];
    $loggedInUserHashedEmail = hash('sha256', $loggedInUserEmail);
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

$isLoggedIn = isset($_SESSION['current_user_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Flame</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="foto/favicon.svg" sizes="48x48"/>
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
        <img class="profil_image" alt="profile photo" src="foto/profile.png">
        <div class="burger-menu" id="burgerMenu">
            <?php if (isset($_SESSION['current_user_email'])): ?>
                <a href="account.php">Account</a>
                <a href="index.php?logout=true">Log Out</a>
            <?php else: ?>
                <a href="log_in.php">Log In</a>
                <a href="register.php">Registration</a>
            <?php endif; ?>
        </div>
    </div>
    <hr class="line_header">
</header>

    <?php if (!$isLoggedIn): ?>
        <div id="popup-overlay"></div>
        <div id="popup">
            <h2>You are not authorized</h2>
            <p>Please log in or register to continue using the website.</p>
            <button class="create_account_button" onclick="window.location.href='log_in.php'">Log In</button>
            <button class="create_account_button" onclick="window.location.href='register.php'">Register</button>
        </div>
    <?php endif; ?>
<section class="main_part">
    <!-- Контейнер с карточками -->
<div id="swiper">
    <?php
    foreach (array_reverse($users) as $user):
        // Пропускаем карточку, если пользователь авторизован и email совпадает
        if (
            $isLoggedIn
            && isset($user['email']) // Проверяем, что поле "email" существует
            && $user['email'] === $loggedInUserHashedEmail // Сравниваем с хэшированным email
        ) {
            continue;
        }
        ?>
        <div class="card">

            <img class="user_foto"
                 alt="user foto"
                 src="data:<?php echo htmlspecialchars($user['photo_mime'], ENT_QUOTES, 'UTF-8'); ?>;base64,<?php echo htmlspecialchars($user['photo'], ENT_QUOTES, 'UTF-8'); ?>"
            >

            <div class="main_part_text">
                <div class="name_and_age">
                    <?php echo htmlspecialchars($user['name']); ?>,
                    <?php echo calculateAge($user['date_birth']); ?>
                </div>
                <div class="inerests">
                    <?php echo htmlspecialchars($user['bio']); ?>
                </div>
            </div>

            <!-- ВАЖНО: кнопки внутри каждой карточки -->
            <div class="buttons_yes_no">
                <div class="button_border">
                    <img class="button_foto_cross" alt="cross" src="foto/cross.png">
                </div>
                <div class="button_border"
                     data-email="<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>">
                    <img class="button_foto_heart" alt="heart" src="foto/heart.png">
                </div>
            </div>

        </div>
    <?php endforeach; ?>
</div>

</section>
    <footer>
        <div class="swip">
            <h5 class="footer_text">Swipe</h5>
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
