<?php
session_start();

// Извлечение ошибок и старых значений из сессии
$errors = $_SESSION['errors'] ?? [];
$oldValues = $_SESSION['oldValues'] ?? [];
unset($_SESSION['errors'], $_SESSION['oldValues']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log In</title>
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
        <img class="profil_image" alt="profil foto" src="foto/profile.png">
    </div>
    <hr class="line_header">
</header>
<div class="create_account">
    <div class="upper_part">
        <div class="main_text">
            <h1 class="create_account_text">Log In</h1>
        </div>
    </div>
    <div class="down_part">
        <form class="account_form" method="POST" action="log.php">
            <div class="form">
                <label for="email" class="information_text">Email</label>
                <input class="input_inf" id="email" type="email" name="email" placeholder="johndoe@gmail.com" value="<?php echo htmlspecialchars($oldValues['email'] ?? ''); ?>">
                <span class="error_message"><?php echo htmlspecialchars($errors['email'] ?? ''); ?></span>
            </div>
            <div class="form">
                <label for="password" class="information_text">Password</label>
                <input class="input_inf" id="password" type="password" name="password">
                <span class="error_message"><?php echo htmlspecialchars($errors['password'] ?? ''); ?></span>
            </div>
            <div class="information_button">
                <button class="create_account_button" name="log_in" type="submit">Log In</button>
                <a href="forget_password.php">
                    <h5 class="forget_password_text">Forget Password?</h5>
                </a>
                <a href="register.php">
                    <h5 class="forget_password_text">Do not have account yet?</h5>
                </a>
            </div>
        </form>
    </div>
</div>
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
                <path d="M16.03,18.616l5.294-4.853a1,1,0,0,1,1.352,1.474l-6,5.5a1,1,0,0,1-1.383-.03l-3-3a1,1,0,0,1,1.414-1.414ZM1,20a9.01,9.01,0,0,1,5.623-8.337A4.981,4.981,0,1,1,10,13a7.011,7.011,0,0,0-6.929,6H10a1,1,0,0,1,0,2H2A1,1,0,0,1,1,20ZM7,8a3,3,0,1,0,3-3A3,3,0,0,0,7,8Z"></path>
            </svg>
            <h5 class="footer_text">People</h5>
        </a>
    </div>
</footer>
</body>
</html>
