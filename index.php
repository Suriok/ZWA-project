<?php
session_start();
// ====================
// Kontrola autorizace
// Kontroluje, zda je uzivatel prihlaseny
// ====================
$isLoggedIn = isset($_SESSION['current_user_email']);

// Funkce pro vypocet veku na zaklade data narozeni
function calculateAge($dateOfBirth) {
    try {
        $dob = new DateTime($dateOfBirth); // Vytvori objekt DateTime
        $now = new DateTime(); // Aktualni datum
        return $now->diff($dob)->y; // Vypocita rozdil ve veku
    } catch (Exception $e) {
        return null; // Pokud je datum neplatne, vrati null
    }
}

// ====================
// Nacteni uzivatelu z JSON souboru
// ====================
$users = [];
$userFile = 'user.json'; // Cesta k souboru
if (file_exists($userFile)) {
    $fileData = file_get_contents($userFile); // Nacteni obsahu souboru
    $users = json_decode($fileData, true); // Dekodovani JSON do pole
    if (!is_array($users)) {
        $users = []; // Pokud obsah neni pole, inicializuje prazdne pole
    }
}

// ====================
// Inicializace uzivatelskych dat
// ====================
$loggedInUserHashedEmail = null; // Hash uzivatelskeho emailu
$isAdmin = false; // Priznak, zda je uzivatel admin
if ($isLoggedIn && isset($_SESSION['user']['email'])) {
    $loggedInUserEmail = $_SESSION['user']['email']; // Nacteni emailu prihlaseneho uzivatele
    $loggedInUserHashedEmail = hash('sha256', $loggedInUserEmail); // Hashovani emailu
    $isAdmin = $_SESSION['user']['is_admin'] ?? false; // Kontrola, zda je uzivatel admin
}

// ====================
// Odhlaseni uzivatele
// ====================
if (isset($_GET['logout'])) {
    session_unset(); // Odstrani vsechny promenne session
    session_destroy(); // Ukonci session
    header("Location: index.php"); // Presmeruje na hlavni stranku
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flame</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="foto/favicon.svg" sizes="48x48">
    <script src="js/card.js" defer></script>
</head>
<body>
<header>
    <!-- Hlavicka stranky obsahujici logo a menu -->
    <div class="header-container">
        <div class="header-logo">
            <a class="header-logo" href="index.php">
                <img class="logo_image" alt="logo-hearte" src="foto/logo-hearte.png">
                <div class="logo_text">
                    <div class="logo_text_big">FlAME</div>
                    <div class="logo_text_small">FIND YOUR LOVE HERE</div>
                </div>
            </a>
        </div>
        <img class="profil_image" alt="profile photo" src="foto/profile.png">
        <div class="burger-menu" id="burgerMenu">
            <?php if ($isLoggedIn): ?>
                <a href="account.php">Account</a>
                <?php if ($isAdmin): ?>
                    <a href="admin.php">Admin Panel</a>
                <?php endif; ?>
                <a href="index.php?logout=true">Log Out</a>
            <?php else: ?>
                <a href="log_in.php">Log In</a>
                <a href="register.php">Registration</a>
            <?php endif; ?>
        </div>
    </div>
    <hr class="line_header">
</header>

<!-- Zobrazeni popup okna pro neprihlasene uzivatele -->
<?php if (!$isLoggedIn): ?>
    <div id="popup-overlay"></div>
    <div id="popup">
        <h2>You are not authorized</h2>
        <p>Please log in or register to continue using the website.</p>
        <button class="create-account-button login-button">Log In</button>
        <button class="create-account-button register-button">Register</button>
    </div>
<?php endif; ?>

<section class="main_part">
    <h1 class="text_liked">Choose your love</h1>
    <!-- Hlavni obsah: karty uzivatelu -->
    <div id="swiper">
        <?php
        foreach (array_reverse($users) as $user):
            // Preskoceni karty pro prihlaseneho uzivatele
            if (
                $isLoggedIn
                && isset($user['email'])
                && $user['email'] === $loggedInUserHashedEmail
            ) {
                continue;
            }
            ?>
            <div class="card">
                <img class="user_foto"
                     alt="user foto"
                     src="data:<?php echo htmlspecialchars($user['photo_mime'], ENT_QUOTES, 'UTF-8'); ?>;base64,<?php echo htmlspecialchars($user['photo'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="main_part_text">
                    <div class="name_and_age">
                        <?php echo htmlspecialchars($user['name']); ?>,
                        <?php echo calculateAge($user['date_birth']); ?>
                    </div>
                    <div class="inerests">
                        <?php echo htmlspecialchars($user['bio']); ?>
                    </div>
                </div>

                <!-- Tlacitka pro interakci s kartou (like/dislike) -->
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
    <!-- Paticka stranky -->
    <div class="swip">
        <h5 class="footer_text">Swipe</h5>
    </div>
    <div class="people">
        <a class="people" href="people.php">
            <svg fill="#ffffff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"
                 class="footer-icon">
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
