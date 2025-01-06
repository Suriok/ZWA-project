<?php
session_start(); // Zahájení nové nebo obnovení existující relace

// ============================
// Získání chyb a starých hodnot
// ============================

// Získání chybových zpráv z relace (pokud existují)
$errors = $_SESSION['errors'] ?? []; // Chyby validace přihlášení
$oldValues = $_SESSION['oldValues'] ?? []; // Staré hodnoty zadané do formuláře
unset($_SESSION['errors'], $_SESSION['oldValues']); // Vymazání chyb a starých hodnot z relace
?>

<!DOCTYPE html>
<html  lang="en">
<!-- Hlavicka stranky obsahujici logo a menu -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flame</title>
    <!-- Připojení externího CSS souboru -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Nastavení ikony stránky -->
    <link rel="icon" type="image/png" href="foto/favicon.svg" sizes="48x48">
    <!-- Připojení JavaScript souboru -->
    <script src="js/card.js" defer></script>
</head>
<body>
<header>
    <div class="header-container">
        <!-- Logo a název aplikace -->
        <div class="header-logo">
            <a class="header-logo" href="index.php">
                <img class="logo_image" alt="logo-hearte" src="foto/logo-hearte.png">
                <div class="logo_text">
                    <div class="logo_text_big">FlAME</div>
                    <div class="logo_text_small">FIND YOUR LOVE HERE</div>
                </div>
            </a>
        </div>
        <!-- Profilová ikona -->
        <img class="profil_image" alt="profil foto" src="foto/profile.png">
    </div>
    <hr class="line_header">
</header>

<!-- Hlavní sekce pro přihlášení uživatele -->
<div class="create_account">
    <div class="upper_part">
        <!-- Nadpis -->
        <div class="main_text">
            <h1 class="create_account_text">Log In</h1>
        </div>
    </div>
    <div class="down_part">
        <!-- Formulář pro přihlášení -->
        <form class="account_form" method="POST" action="log.php">
            <div class="form">
                <!-- Pole pro zadání emailu -->
                <label for="email" class="information_text">Email</label>
                <input class="input_inf" id="email" type="email" name="email" placeholder="johndoe@gmail.com"
                       value="<?php echo htmlspecialchars($oldValues['email'] ?? ''); ?>"> <!-- Předvyplnění staré hodnoty emailu -->
                <span class="error_message"><?php echo htmlspecialchars($errors['email'] ?? ''); ?></span> <!-- Zobrazení chybové zprávy pro email -->
            </div>
            <div class="form">
                <!-- Pole pro zadání hesla -->
                <label for="password" class="information_text">Password</label>
                <input class="input_inf" id="password" type="password" name="password">
                <span class="error_message"><?php echo htmlspecialchars($errors['password'] ?? ''); ?></span> <!-- Zobrazení chybové zprávy pro heslo -->
            </div>
            <div class="information_button">
                <!-- Tlačítko pro odeslání přihlašovacího formuláře -->
                <button class="create_account_button" name="log_in" type="submit">Log In</button>
                <!-- Odkaz na registrační stránku -->
                <a href="register.php">
                    <h5 class="forget_password_text">Do not have account yet?</h5>
                </a>
            </div>
        </form>
    </div>
</div>
<!-- Paticka stranky -->
<footer>
    <!-- Odkaz zpět na hlavní stránku -->
    <div class="swip">
        <a class="swip" href="index.php">
            <h5 class="footer_text">Swipe</h5>
        </a>
    </div>
    <!-- Odkaz na stránku lidí -->
    <div class="people">
        <a class="people" href="people.php">
            <!-- SVG ikona -->
            <svg fill="#ffffff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"
                 class="footer-icon">
                <path d="M16.03,18.616l5.294-4.853a1,1,0,0,1,1.352,1.474l-6,5.5a1,1,0,0,1-1.383-.03l-3-3a1,1,0,0,1,1.414-1.414ZM1,20a9.01,9.01,0,0,1,5.623-8.337A4.981,4.981,0,1,1,10,13a7.011,7.011,0,0,0-6.929,6H10a1,1,0,0,1,0,2H2A1,1,0,0,1,1,20ZM7,8a3,3,0,1,0,3-3A3,3,0,0,0,7,8Z"></path>
            </svg>
            <h5 class="footer_text">People</h5>
        </a>
    </div>
</footer>
</body>
</html>
