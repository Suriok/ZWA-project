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

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список пользователей</title>
</head>
<body>
<h1>Все пользователи</h1>

<!--
  Блок, где мы выводим всех пользователей (предположим,
  что в user.json есть поля "name" и "email")
-->
<?php foreach ($users as $user): ?>
    <div class="user-card" style="margin-bottom: 10px;">
        <p>Имя: <?= htmlspecialchars($user['name']); ?></p>
        <p>Email: <?= htmlspecialchars($user['email']); ?></p>
        <button class="button_border"
                data-email="<?= htmlspecialchars($user['email']); ?>">
            Поставить лайк
        </button>
    </div>
<?php endforeach; ?>

<hr>

<h2>Ваши лайки (динамически подгруженные)</h2>
<div id="likedUsersContainer">Загрузка...</div>

<!-- Подключаем наш JS -->
<script src="card.js"></script>
<script>
    // Простая функция, которая запросит список лайков и отобразит их
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

            // Выводим список лайкнутых email
            likedList.forEach(email => {
                const p = document.createElement('p');
                p.textContent = email;
                container.appendChild(p);
            });
        } catch (e) {
            console.error(e);
        }
    }

    // Загрузим список лайков при загрузке страницы
    loadLikedUsers();
</script>
</body>
</html>
