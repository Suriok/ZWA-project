<?php
// ============================
// Inicializace relace
// ============================
session_start(); // Zahájení nové nebo obnovení existující relace

// Načtení souboru pro kontrolu relace
require 'session_check.php'; // Kontrola, zda má uživatel aktivní relaci

// ============================
// Kontrola přihlášení
// ============================
if (!isset($_SESSION['user'])) { // Kontrola, zda je uživatel přihlášen
    header("Location: log_in.php"); // Přesměrování na přihlašovací stránku
    exit(); // Ukončení skriptu
}

// ============================
// Inicializace proměnných pro kontrolu stavu uživatele
// ============================
$isLoggedIn = isset($_SESSION['current_user_email']); // Zda je uživatel přihlášen
$loggedInUserHashedEmail = null; // Hash emailu aktuálního uživatele (výchozí null)
$isAdmin = false; // Výchozí hodnota role administrátora

// Kontrola, zda je uživatel přihlášen a má email uložený v relaci
if ($isLoggedIn && isset($_SESSION['user']['email'])) {
    $loggedInUserEmail = $_SESSION['user']['email']; // Získání emailu aktuálního uživatele
    $loggedInUserHashedEmail = hash('sha256', $loggedInUserEmail); // Vytvoření hash z emailu
    $isAdmin = $_SESSION['user']['is_admin'] ?? false; // Kontrola role administrátora
}

// Uložení aktuálního uživatele do proměnné
$user = $_SESSION['user']; // Data aktuálního uživatele

// Vytvoření hash emailu pro další porovnání
$hashedEmail = hash('sha256', $user['email'] ?? ''); // Hash emailu aktuálního uživatele

// ============================
// Cesta k souboru s uživateli
// ============================
$usersFile = 'user.json'; // Cesta k souboru JSON obsahujícího informace o uživatelích

/**
 * Načítá seznam uživatelů ze souboru.
 *
 * Tato funkce kontroluje, zda existuje soubor s uživateli,
 * a pokud ano, dekóduje jeho obsah jako pole.
 *
 * @param string $usersFile Cesta k souboru JSON obsahujícího uživatele.
 * @return array Pole uživatelů nebo prázdné pole, pokud soubor není validní nebo neexistuje.
 */
function loadUsers($usersFile) {
    if (file_exists($usersFile)) { // Kontrola, zda soubor existuje
        $users = json_decode(file_get_contents($usersFile), true); // Načtení obsahu souboru a dekódování JSON
        if (is_array($users)) { // Kontrola, zda jsou data validní
            return $users; // Vrácení pole uživatelů
        }
    }
    return []; // Pokud soubor neexistuje nebo není validní, vrací prázdné pole
}

/**
 * Ukládá aktualizovaný seznam uživatelů do souboru.
 *
 * Tato funkce zapisuje seznam uživatelů do souboru JSON.
 *
 * @param string $usersFile Cesta k souboru JSON.
 * @param array $users Aktualizovaný seznam uživatelů.
 * @return bool Vrácí `true` při úspěchu nebo `false` při chybě.
 */
function saveUsers($usersFile, $users) {
    return file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT)) !== false; // Uložení dat do souboru
}

// Načtení všech uživatelů
$users = loadUsers($usersFile); // Načtení seznamu uživatelů z JSON souboru

// ============================
// Kontrola administrátorských práv
// ============================
/**
 * Kontroluje, zda je aktuální uživatel administrátor.
 *
 * Pokud není, zobrazí chybovou zprávu a ukončí skript.
 */
if (!$user['is_admin']) { // Pokud aktuální uživatel není administrátor
    echo "Access denied. You do not have sufficient permissions to view this page."; // Zobrazení chybové zprávy
    exit(); // Ukončení skriptu
}

// ============================
// Zpracování odstranění uživatele
// ============================
/**
 * Zpracovává žádost o odstranění uživatele.
 *
 * Pokud je nalezen uživatel s odpovídajícím hash emailu,
 * odstraní jej ze seznamu a uloží aktualizovaný seznam.
 *
 * @param string $deleteEmailHash Hash emailu uživatele k odstranění.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_email'])) {
    $deleteEmailHash = $_POST['delete_email']; // Získání hash emailu uživatele k odstranění

    // Zabránění administrátorovi odstranit vlastní účet
    if ($deleteEmailHash === $hashedEmail) {
        echo "You cannot delete your own account."; // Zobrazení upozornění
        exit();
    }

    // Hledání uživatele v seznamu
    $found = false;
    foreach ($users as $index => $existingUser) {
        if ($existingUser['email'] === $deleteEmailHash) { // Kontrola, zda email odpovídá
            $found = true;
            // Odstranění uživatele ze seznamu
            array_splice($users, $index, 1);
            break;
        }
    }

    if ($found) {
        // Uložení aktualizovaného seznamu uživatelů
        if (saveUsers($usersFile, $users)) {
            header("Location: admin.php"); // Přesměrování na administrátorskou stránku
            exit(); // Ukončení skriptu
        } else {
            echo "Failed to delete the user. Please try again."; // Chyba při ukládání
            exit();
        }
    } else {
        echo "User not found."; // Pokud uživatel nebyl nalezen
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
<section class="main_part">
    <h1 class="text_liked">Admin Panel</h1>
    <!-- Administrátorská sekce -->
    <div class="admin_container">
        <table class="styled-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Surname</th>
                <th>Gender</th>
                <th>Bio</th>
                <th>Date of Birth</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $userItem): ?>
                <?php if ($userItem['email'] === $hashedEmail) continue; // Přeskočení aktuálního admina ?>
                <tr>
                    <td><?php echo htmlspecialchars($userItem['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($userItem['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($userItem['gender'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($userItem['bio'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($userItem['date_birth'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $userItem['is_admin'] ? 'Admin' : 'User'; ?></td>
                    <td>
                        <!-- Formulář pro odstranění uživatele -->
                        <form method="POST" action="admin.php">
                            <input type="hidden" name="delete_email" value="<?php echo htmlspecialchars($userItem['email'], ENT_QUOTES, 'UTF-8'); ?>">
                            <button class="edit_profil" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="8">No users found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<!-- Paticka stranky -->
<footer>
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
