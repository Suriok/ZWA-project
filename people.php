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
</head>
<body>

<h2>your liked people</h2>
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
                        <img src="data:${user.photo_mime};base64,${user.photo}" alt="Фото пользователя" style="width: 100px; height: 100px; border-radius: 50%; margin-bottom: 10px;">
                        <p>Имя: ${user.name}</p>
                        <p>Возраст: ${calculateAge(user.date_birth)}</p>
                        <p>Био: ${user.bio}</p>
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
</body>
</html>

