<?php
session_start();
require 'session_check.php'; // Pokud máte kontrolu sezení
$isLoggedIn = isset($_SESSION['current_user_email']); // Zda je uživatel přihlášen

$loggedInUserHashedEmail = null; // Hash přihlášeného emailu
$isAdmin = false; // Příznak, zda je uživatel administrátor
if ($isLoggedIn && isset($_SESSION['user']['email'])) {
    $loggedInUserEmail = $_SESSION['user']['email']; // Email aktuálně přihlášeného uživatele
    $loggedInUserHashedEmail = hash('sha256', $loggedInUserEmail); // Hash emailu
    $isAdmin = $_SESSION['user']['is_admin'] ?? false; // Zda je uživatel admin
}

// Kontrola, zda je uživatel přihlášen
if (!isset($_SESSION['user'])) {
    header("Location: log_in.php");
    exit();
}

$user = $_SESSION['user'];

// Původní (plain) email
$plainEmail  = $user['email'] ?? '';
// Hash emailu pro vyhledání v user.json
$hashedEmail = hash('sha256', $plainEmail);

// Cesta k souboru
$usersFile = 'user.json';

/**
 * Načítá všechny uživatele ze souboru JSON.
 *
 * Tato funkce kontroluje, zda soubor existuje, a poté načte
 * obsah souboru jako asociativní pole.
 *
 * @param string $file Cesta k souboru JSON.
 * @return array Pole uživatelů na úspěch nebo prázdné pole při chybě.
 */
function loadUsers($file) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }
    return [];
}

/**
 * Ukládá uživatelská data do souboru JSON.
 *
 * Tato funkce zapisuje asociativní pole uživatelů do JSON souboru.
 * Data jsou zapsána ve formátu s pěkným odsazením pro čitelnost.
 *
 * @param string $file Cesta k souboru JSON.
 * @param array $users Pole uživatelů, které se má uložit.
 * @return bool `True` při úspěšném uložení, `False` při chybě.
 */
function saveUsers($file, $users) {
    return file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT)) !== false;
}

// Načteme všechny uživatele z JSON
$users = loadUsers($usersFile);

/**
 * Aktualizuje bio přihlášeného uživatele.
 *
 * Tato funkce ověřuje požadavek na změnu bio, validuje vstup,
 * kontroluje maximální délku a chrání proti XSS. Následně aktualizuje bio
 * v uživatelském souboru a v aktuální session.
 *
 * @param array $users Pole všech uživatelů načtených ze souboru.
 * @param string $hashedEmail Hash e-mailu aktuálně přihlášeného uživatele.
 * @param string $newBio Nové bio, které uživatel zadal.
 * @return string JSON odpověď s výsledkem operace.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents('php://input');
    $input = json_decode($rawData, true);

    if (isset($input['action']) && $input['action'] === 'update_bio') {
        if (!isset($input['bio'])) {
            echo json_encode(['message' => 'Bio is missing.']);
            exit();
        }

        $newBio = trim($input['bio']);
        if (strlen($newBio) > 120) {
            echo json_encode(['message' => 'Bio must not exceed 120 characters.']);
            exit();
        }

        $sanitizedBio = $newBio; // XSS ochrana
        $found = false;

        foreach ($users as &$existingUser) {
            if (isset($existingUser['email']) && $existingUser['email'] === $hashedEmail) {
                $existingUser['bio'] = $sanitizedBio;
                $found = true;
                break;
            }
        }
        unset($existingUser);

        if ($found) {
            if (saveUsers($usersFile, $users)) {
                $_SESSION['user']['bio'] = $sanitizedBio;
                echo json_encode(['message' => 'Bio successfully updated.', 'bio' => $sanitizedBio]);
            } else {
                echo json_encode(['message' => 'Failed to save data to file.']);
            }
        } else {
            echo json_encode(['message' => 'User not found.']);
        }
        exit();
    }
}

/**
 * Odhlásí aktuálně přihlášeného uživatele.
 *
 * Tato funkce vymaže všechny údaje o uživateli ze session,
 * ukončí relaci a přesměruje na hlavní stránku.
 *
 * @return void
 */
if (isset($_POST['log_out'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
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

    <!-- Hlavicka obsahujici logo a menu -->
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

<section class="main_part">
    <!-- Informace o profilu uzivatele -->
    <div class="profil_information">
        <div class="profil_main_text">
            <h1 class="profil_main_text">Profile Information</h1>
        </div>
        <div class="foto_name_surname">
            <div class="user_account_img">
                <img class="img_user" alt="account_user"
                     src="data:<?php echo htmlspecialchars($user['photo_mime'], ENT_QUOTES, 'UTF-8'); ?>;base64,<?php echo htmlspecialchars($user['photo'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <form class="account_form">
                <label for="name" class="information_text">Name</label>
                <input class="input_inf" readonly
                       id="name"
                       type="text"
                       name="name"
                       value="<?php echo htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                <label for="surname" class="information_text">Surname</label>
                <input class="input_inf" readonly
                       id="surname"
                       type="text"
                       name="surname"
                       value="<?php echo htmlspecialchars($user['surname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                <label for="email" class="information_text">Email</label>
                <input class="input_inf" readonly
                       id="email"
                       type="email"
                       name="email"
                       value="<?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </form>
        </div>
        <div class="div_bio">
            <form class="account_form" id="bioForm">
                <label for="bio" class="bio_text">Bio</label>
                <textarea class="input_inf_bio" id="bio" name="bio" maxlength="120"><?php echo trim(htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8')); ?></textarea>
                <div id="charCount" class="char_count">0/120</div>
                <button class="edit_profil" type="submit">Save Bio</button>
                <div id="bioMessage"></div>
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
    <!-- Paticka stranky -->
    <div class="swip">
        <a class="swip" href="index.php">
            <h5 class="footer_text">Swipe</h5>
        </a>
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
