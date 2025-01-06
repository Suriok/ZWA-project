<?php
session_start();
$isLoggedIn = isset($_SESSION['current_user_email']);

// 1) Проверка авторизации
if (!isset($_SESSION['current_user_email'])) {
    header('Location: log_in.php');
    exit();
}

// 2) Пути к файлам
$userFilePath = 'user.json';
$likedFile    = 'liked_users.json';

$loggedInUserHashedEmail = null; // Hash uzivatelskeho emailu
$isAdmin = false; // Priznak, zda je uzivatel admin
if ($isLoggedIn && isset($_SESSION['user']['email'])) {
    $loggedInUserEmail = $_SESSION['user']['email']; // Nacteni emailu prihlaseneho uzivatele
    $loggedInUserHashedEmail = hash('sha256', $loggedInUserEmail); // Hashovani emailu
    $isAdmin = $_SESSION['user']['is_admin'] ?? false; // Kontrola, zda je uzivatel admin
}
// 3) Загрузка всех пользователей (из user.json)
$users = [];
if (file_exists($userFilePath)) {
    $dataFromFile = json_decode(file_get_contents($userFilePath), true);
    if (is_array($dataFromFile)) {
        $users = $dataFromFile;
    }
}

// 4) Загрузка списка «лайкнутых» пользователей (из liked_users.json)
$likedUsers = [];
if (file_exists($likedFile)) {
    $likedFromFile = json_decode(file_get_contents($likedFile), true);
    if (is_array($likedFromFile)) {
        $likedUsers = $likedFromFile;
    }
}

// 5) Текущий (plain) email пользователя
$currentUserEmail = $_SESSION['current_user_email'] ?? '';

// ---------------------
// Если POST — значит пришёл запрос на лайк (email)
// ---------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents('php://input');
    $data    = json_decode($rawData, true);

    if (!empty($data['email'])) {
        $likedEmail = htmlspecialchars($data['email']);

        // Инициализируем массив, если нет
        if (!isset($likedUsers[$currentUserEmail])) {
            $likedUsers[$currentUserEmail] = [];
        }

        // Добавляем email в список, если его там ещё нет
        if (!in_array($likedEmail, $likedUsers[$currentUserEmail], true)) {
            $likedUsers[$currentUserEmail][] = $likedEmail;
        }

        // Сохраняем
        file_put_contents($likedFile, json_encode($likedUsers, JSON_PRETTY_PRINT));

        echo json_encode([
            'success' => true,
            'message' => "Пользователь с email {$likedEmail} добавлен в лайки."
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Email для лайка не передан.'
        ]);
    }
    exit();
}

// ---------------------
// Если GET c ?action=get_liked — отдаём ЛАЙКНУТЫХ c ПАГИНАЦИЕЙ
// ---------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_liked') {
    // Массив хэшей email, которых лайкнул текущий пользователь
    $allLikedList = $likedUsers[$currentUserEmail] ?? [];

    // ПАГИНАЦИЯ: 3 элемента на страницу
    $itemsPerPage = 3;
    $totalLiked   = count($allLikedList);
    $totalPages   = ($totalLiked > 0) ? ceil($totalLiked / $itemsPerPage) : 1;

    // Какая страница запрошена
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    if ($page > $totalPages) $page = $totalPages;

    // Индекс начала
    $startIndex = ($page - 1) * $itemsPerPage;
    // Выбираем ровно 3 (или меньше на последней)
    $likedListPage = array_slice($allLikedList, $startIndex, $itemsPerPage);

    // Отправляем JSON
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flame - Your Liked People</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="foto/favicon.svg" sizes="48x48"/>
    <!-- Подключаем внешний JS-файл со всем кодом, который раньше был в <script> -->
    <script>
        // Передаём всех пользователей (из user.json) в window.users
        window.users = <?= json_encode($users, JSON_HEX_TAG); ?>;
    </script>
    <script src="js/loadLikedUsers.js" defer></script>
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

<h1 class="text_liked">Your Liked People</h1>

<!-- Контейнер для карточек -->
<div id="likedUsersContainer">Загрузка...</div>
<!-- Контейнер для пагинации (ссылок на страницы) -->
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
