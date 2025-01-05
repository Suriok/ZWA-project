<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['current_user_email'])) {
    header('Location: log_in.php');
    exit();
}

// Пути к файлам
$userFilePath = 'user.json';
$likedFile    = 'liked_users.json';

// Загружаем список всех пользователей
$users = [];
if (file_exists($userFilePath)) {
    $dataFromFile = json_decode(file_get_contents($userFilePath), true);
    if (is_array($dataFromFile)) {
        $users = $dataFromFile;
    }
}

// Загружаем список «лайкнутых» пользователей
$likedUsers = [];
if (file_exists($likedFile)) {
    $likedFromFile = json_decode(file_get_contents($likedFile), true);
    if (is_array($likedFromFile)) {
        $likedUsers = $likedFromFile;
    }
}

// Текущий email (кто ставит «лайк»)
$currentUserEmail = $_SESSION['current_user_email'];

// ----- ОБРАБОТКА AJAX-ЗАПРОСОВ -----

// Если GET-запрос с action=get_liked — вернём список «лайков» текущего пользователя
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_liked') {
    // Берём у текущего пользователя список лайков
    $userLikedList = $likedUsers[$currentUserEmail] ?? [];
    echo json_encode($userLikedList);
    exit();
}

// Если POST-запрос — пытаемся сохранить «лайк»
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    // Проверяем, что передан email для лайка
    if (!empty($data['email'])) {
        // Это email пользователя, которого «лайкнули»
        $likedEmail = htmlspecialchars($data['email']);

        // Инициализируем массив лайков для текущего пользователя, если ещё нет
        if (!isset($likedUsers[$currentUserEmail])) {
            $likedUsers[$currentUserEmail] = [];
        }

        // Добавляем в массив, если ещё нет
        if (!in_array($likedEmail, $likedUsers[$currentUserEmail])) {
            $likedUsers[$currentUserEmail][] = $likedEmail;
        }

        // Сохраняем весь массив лайков обратно в файл
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
?>

<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список пользователей</title>
    <link rel="stylesheet" href="css/style.css">
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

<h1 class="text_liked">Your liked people</h1>
<div id="likedUsersContainer">Загрузка...</div>

<!-- Подключаем JS -->
<script>
    // Функция для лайка пользователя
    async function likeUser(email) {
        try {
            const response = await fetch('people.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email })
            });
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                loadLikedUsers(); // Обновляем список лайков
            } else {
                alert(result.message);
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Функция для загрузки лайкнутых пользователей
    async function loadLikedUsers() {
        try {
            const response = await fetch('people.php?action=get_liked');
            const likedList = await response.json();

            const container = document.getElementById('likedUsersContainer');
            container.innerHTML = ''; // Очищаем старый вывод

            if (!Array.isArray(likedList) || likedList.length === 0) {
                container.textContent = 'Пока никто не лайкнут.';
                return;
            }

            // Получаем данные о пользователях по email
            const users = <?= json_encode($users, JSON_HEX_TAG); ?>;

            likedList.forEach(likedEmail => {
                const user = users.find(user => user.email === likedEmail);
                if (user) {
                    const userCard = document.createElement('div');
                    userCard.className = 'user-card';
                    userCard.innerHTML = `
                        <img class="user_foto" src="data:${user.photo_mime};base64,${user.photo}">
                        <p class="name_and_age"> ${user.name},${calculateAge(user.date_birth)}</p>
                        <p class="inerests"> ${user.bio}</p>
                    `;
                    container.appendChild(userCard);

                }
            });
        } catch (e) {
            console.error(e);
        }
    }

    // Функция для вычисления возраста (JavaScript)
    function calculateAge(dateOfBirth) {
        const birthDate = new Date(dateOfBirth);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDifference = today.getMonth() - birthDate.getMonth();
        if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    // Загрузим список лайков при загрузке страницы
    loadLikedUsers();
</script>
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
