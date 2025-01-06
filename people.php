<?php
session_start();
// ====================
// Kontrola autorizace
// ====================
$isLoggedIn = isset($_SESSION['current_user_email']); // Zda je uživatel přihlášen

// Pokud uživatel není přihlášen, přesměrujeme ho na přihlašovací stránku
if (!isset($_SESSION['current_user_email'])) {
    header('Location: log_in.php');
    exit();
}

// ====================
// Cesty k souborům
// ====================
$userFilePath = 'user.json'; // Cesta k souboru s uživateli
$likedFile    = 'liked_users.json'; // Cesta k souboru s "lajknutými" uživateli

$loggedInUserHashedEmail = null; // Hash přihlášeného emailu
$isAdmin = false; // Příznak, zda je uživatel administrátor
if ($isLoggedIn && isset($_SESSION['user']['email'])) {
    $loggedInUserEmail = $_SESSION['user']['email']; // Email aktuálně přihlášeného uživatele
    $loggedInUserHashedEmail = hash('sha256', $loggedInUserEmail); // Hash emailu
    $isAdmin = $_SESSION['user']['is_admin'] ?? false; // Zda je uživatel admin
}

// ====================
// Načtení uživatelů z JSON souboru
// ====================
$users = [];
if (file_exists($userFilePath)) {
    $dataFromFile = json_decode(file_get_contents($userFilePath), true); // Načtení dat
    if (is_array($dataFromFile)) {
        $users = $dataFromFile;
    }
}

// ====================
// Načtení "lajknutých" uživatelů
// ====================
$likedUsers = [];
if (file_exists($likedFile)) {
    $likedFromFile = json_decode(file_get_contents($likedFile), true); // Načtení dat
    if (is_array($likedFromFile)) {
        $likedUsers = $likedFromFile;
    }
}

// ====================
// Email aktuálního uživatele
// ====================
$currentUserEmail = $_SESSION['current_user_email'] ?? '';

// ---------------------
// POST: Přidání uživatele do seznamu "lajků"
// ---------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents('php://input'); // Získání dat z těla požadavku
    $data    = json_decode($rawData, true);

    if (!empty($data['email'])) { // Kontrola, zda byl odeslán email
        $likedEmail = htmlspecialchars($data['email']);

        // Pokud pro uživatele neexistuje seznam lajků, inicializujeme ho
        if (!isset($likedUsers[$currentUserEmail])) {
            $likedUsers[$currentUserEmail] = [];
        }

        // Přidání uživatele do seznamu, pokud ještě neexistuje
        if (!in_array($likedEmail, $likedUsers[$currentUserEmail], true)) {
            $likedUsers[$currentUserEmail][] = $likedEmail;
        }

        // Uložení aktualizovaných dat do souboru
        file_put_contents($likedFile, json_encode($likedUsers, JSON_PRETTY_PRINT));

        echo json_encode([
            'success' => true,
            'message' => "Uživatel s emailem {$likedEmail} byl přidán do lajků."
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Email pro lajknutí nebyl odeslán.'
        ]);
    }
    exit();
}

// ---------------------
// GET: Načtení lajknutých uživatelů s podporou stránkování
// ---------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_liked') {
    $allLikedList = $likedUsers[$currentUserEmail] ?? []; // Získání všech lajknutých uživatelů

    // Nastavení stránkování: počet uživatelů na stránku
    $itemsPerPage = 3;
    $totalLiked   = count($allLikedList); // Celkový počet lajků
    $totalPages   = ($totalLiked > 0) ? ceil($totalLiked / $itemsPerPage) : 1;

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Aktuální stránka
    if ($page < 1) $page = 1;
    if ($page > $totalPages) $page = $totalPages;

    $startIndex = ($page - 1) * $itemsPerPage; // Výpočet začátku
    $likedListPage = array_slice($allLikedList, $startIndex, $itemsPerPage); // Výběr dat pro aktuální stránku

    echo json_encode([
        'page'       => $page,
        'totalPages' => $totalPages,
        'likedList'  => $likedListPage
    ]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <!-- Metadata stránky -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flame - Your Liked People</title>
    <!-- Odkaz na externí CSS styl -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Nastavení ikony stránky -->
    <link rel="icon" type="image/png" href="foto/favicon.svg" sizes="48x48"/>
    <!-- Skripty pro dynamické načítání lajknutých uživatelů -->
    <script>
        // Přenos uživatelů (z PHP do JavaScriptu)
        window.users = <?= json_encode($users, JSON_HEX_TAG); ?>;
    </script>
    <script src="js/loadLikedUsers.js" defer></script>
    <script src="js/card.js" defer></script>
</head>
<body>
<header>

    <!-- Hlavička stránky -->
    <div class="header-container">
        <!-- Logo aplikace -->
        <div class="header-logo">
            <a class="header-logo" href="index.php">
                <img class="logo_image" alt="logo-hearte" src="foto/logo-hearte.png">
                <div class="logo_text">
                    <div class="logo_text_big">FlAME</div>
                    <div class="logo_text_small">FIND YOUR LOVE HERE</div>
                </div>
            </a>
        </div>
        <!-- Profilová fotografie -->
        <img class="profil_image" alt="profile photo" src="foto/profile.png">
        <!-- Navigační menu -->
        <div class="burger-menu" id="burgerMenu">
            <!-- Odkazy v menu pro přihlášené i nepřihlášené uživatele -->
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

<!-- Hlavní nadpis stránky -->
<h1 class="text_liked">Your Liked People</h1>

<!-- Kontejner pro zobrazení lajknutých uživatelů -->
<div id="likedUsersContainer">Download...</div>
<!-- Kontejner pro stránkování -->
<div id="paginationContainer"></div>

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
