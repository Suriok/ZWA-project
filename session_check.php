<?php
// Проверка, была ли сессия уже запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$maxInactiveTime = 15 * 60; // 15 минут

// Проверка активности сессии
if (isset($_SESSION['last_activity'])) {
    $inactiveTime = time() - $_SESSION['last_activity'];
    if ($inactiveTime > $maxInactiveTime) {
        session_unset();
        session_destroy();
        header("Location: log_in.php");
        exit();
    }
}

// Обновление времени последней активности
$_SESSION['last_activity'] = time();
?>