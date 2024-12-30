<?php
session_start();
$filePath = 'user.json';
if (file_exists($filePath)) {
    $users = json_decode(file_get_contents($filePath), true);
    if (!is_array($users)) {
        $users = [];
    }
} else {
    $users = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? null; // Используем email для генерации hash_id

    if ($email && isset($_SESSION['current_user_email'])) {
        $currentUserEmail = $_SESSION['current_user_email']; // Email вошедшего пользователя
        $currentUserHash = hash('sha256', $currentUserEmail); // Генерируем hash_id текущего пользователя

        // Генерируем hash_id для понравившегося пользователя
        $likedUserHash = hash('sha256', $email);

        // Читаем файл JSON с избранными
        $file = 'liked_users.json';
        $likedUsers = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

        // Если для пользователя еще нет записи, создаем её
        if (!isset($likedUsers[$currentUserHash])) {
            $likedUsers[$currentUserHash] = [];
        }

        // Добавляем hash_id понравившегося пользователя
        if (!in_array($likedUserHash, $likedUsers[$currentUserHash])) {
            $likedUsers[$currentUserHash][] = $likedUserHash;
        }

        // Сохраняем изменения в файл
        file_put_contents($file, json_encode($likedUsers, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>