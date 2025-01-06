<?php
// Spustí relaci PHP a zajistí přístup k proměnným relace
session_start();
require 'session_check.php';

// ====================
// Kontrola autorizace uživatele
// ====================
$isLoggedIn = isset($_SESSION['current_user_email']); // Kontrola, zda je uživatel přihlášen

if (!isset($_SESSION['user'])) {
    // Přesměrování nepřihlášeného uživatele na přihlašovací stránku
    header("Location: log_in.php");
    exit();
}

$user = $_SESSION['user']; // Získání dat aktuálního uživatele

$plainEmail = $user['email'] ?? ''; // Původní email uživatele
$hashedEmail = hash('sha256', $plainEmail); // Hash emailu pro bezpečnost

// Cesta k souboru uživatelů
$usersFile = 'user.json';

$loggedInUserHashedEmail = null;
$isAdmin = false; // Kontrola, zda je uživatel admin
if ($isLoggedIn && isset($_SESSION['user']['email'])) {
    $loggedInUserEmail = $_SESSION['user']['email'];
    $loggedInUserHashedEmail = hash('sha256', $loggedInUserEmail);
    $isAdmin = $_SESSION['user']['is_admin'] ?? false;
}

// ====================
// Funkce pro práci s uživateli
// ====================
function loadUsers($usersFile) {
    // Načte uživatele z JSON souboru
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true);
        if (is_array($users)) {
            return $users;
        }
    }
    return [];
}

function saveUsers($usersFile, $users) {
    // Uloží uživatele zpět do JSON souboru
    return file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT)) !== false;
}

// Načtení aktuálních uživatelů
$users = loadUsers($usersFile);

// ====================
// Zpracování POST požadavků
// ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aktualizace jména a příjmení přes AJAX
    if (isset($_POST['action']) && $_POST['action'] === 'update_name_surname') {
        $newName = trim($_POST['name'] ?? '');
        $newSurname = trim($_POST['surname'] ?? '');

        // Validace jména a příjmení
        if (empty($newName) || empty($newSurname)) {
            echo json_encode(['success' => false, 'message' => 'Name and surname cannot be empty.']);
            exit();
        }

        $sanitizedName = htmlspecialchars($newName, ENT_QUOTES, 'UTF-8'); // Ochrana proti XSS
        $sanitizedSurname = htmlspecialchars($newSurname, ENT_QUOTES, 'UTF-8');

        $found = false;
        foreach ($users as &$existingUser) {
            if ($existingUser['email'] === $hashedEmail) {
                // Aktualizace dat uživatele
                $existingUser['name'] = $sanitizedName;
                $existingUser['surname'] = $sanitizedSurname;
                $found = true;
                break;
            }
        }
        unset($existingUser);

        if ($found) {
            if (saveUsers($usersFile, $users)) {
                // Aktualizace dat v relaci
                $_SESSION['user']['name'] = $sanitizedName;
                $_SESSION['user']['surname'] = $sanitizedSurname;
                echo json_encode(['success' => true, 'message' => 'Name and surname updated successfully.']);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save data.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit();
        }
    }

    // Aktualizace bio přes AJAX
    if (isset($_POST['action']) && $_POST['action'] === 'update_bio') {
        $newBio = trim($_POST['bio'] ?? '');

        // Validace délky bio
        if (strlen($newBio) > 500) {
            echo json_encode(['success' => false, 'message' => 'Bio must not exceed 500 characters.']);
            exit();
        }

        $sanitizedBio = htmlspecialchars($newBio, ENT_QUOTES, 'UTF-8'); // Ochrana proti XSS

        $found = false;
        foreach ($users as &$existingUser) {
            if ($existingUser['email'] === $hashedEmail) {
                $existingUser['bio'] = $sanitizedBio;
                $found = true;
                break;
            }
        }
        unset($existingUser);

        if ($found) {
            if (saveUsers($usersFile, $users)) {
                // Aktualizace bio v relaci
                $_SESSION['user']['bio'] = $sanitizedBio;
                echo json_encode(['success' => true, 'message' => 'Bio successfully updated.', 'bio' => $sanitizedBio]);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save data.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit();
        }
    }

    // Odhlášení uživatele
    if (isset($_POST['log_out'])) {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }
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
                <textarea class="input_inf_bio" id="bio" name="bio" maxlength="500"><?php echo trim(htmlspecialchars($user['bio'] ?? '', ENT_QUOTES, 'UTF-8')); ?></textarea>
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
