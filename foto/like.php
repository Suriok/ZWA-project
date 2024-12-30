<?php
session_start();

// Проверка, что пользователь авторизован
if (!isset($_SESSION['current_user_email'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Проверка входящих данных
if (!isset($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email is missing in the request.']);
    exit;
}

$hashedEmail = $data['email']; // Получаем хэшированный email из запроса

// Генерируем hash_id текущего пользователя
$currentUserEmail = $_SESSION['current_user_email'];
$currentUserHash = hash('sha256', $currentUserEmail);

// Загружаем файл JSON с избранными пользователями
$file = 'liked_users.json';
$likedUsers = [];
if (file_exists($file)) {
    $likedUsers = json_decode(file_get_contents($file), true);
    if (!is_array($likedUsers)) {
        $likedUsers = [];
    }
}

// Проверяем, существует ли запись для текущего пользователя
if (!isset($likedUsers[$currentUserHash])) {
    $likedUsers[$currentUserHash] = [];
}

// Проверяем, есть ли уже понравившийся пользователь
if (!in_array($hashedEmail, $likedUsers[$currentUserHash])) {
    $likedUsers[$currentUserHash][] = $hashedEmail;

    // Сохраняем изменения в файл
    if (file_put_contents($file, json_encode($likedUsers, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'User added to liked list.']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save data to liked_users.json.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User already in liked list.']);
    exit;
}
