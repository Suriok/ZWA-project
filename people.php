<?php
require 'session_check.php'; // Подключение проверки сессии
$currentUser = $_SESSION['current_user_email'] ?? null;

if (!$currentUser) {
    die('Вы не авторизованы.');
}

$file = 'liked_users.json';
$likedUsers = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
$userFile = 'users.json';
$users = file_exists($userFile) ? json_decode(file_get_contents($userFile), true) : [];

$likedIds = $likedUsers[$currentUser] ?? [];
$likedProfiles = array_filter($users, function ($user) use ($likedIds) {
    return in_array($user['id'], $likedIds);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранные пользователи</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Избранные пользователи</h1>
<div id="swiper">
    <?php foreach ($likedProfiles as $user): ?>
        <div class="card">
            <img class="user_foto" alt="user foto" src="foto/default_user.jpg">
            <div class="main_part_text">
                <div class="name_and_age">
                    <?php echo htmlspecialchars($user['name']); ?>, <?php echo calculateAge($user['date_birth']); ?>
                </div>
                <div class="inerests">
                    Email: <?php echo htmlspecialchars($user['email']); ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
