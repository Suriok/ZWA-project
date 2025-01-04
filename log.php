<?php
session_start();

$maxInactiveTime = 15 * 60; // 15 минут

// Проверка активности пользователя
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $maxInactiveTime) {
    session_unset();
    session_destroy();
    header("Location: log_in.php");
    exit();
}

// Обновляем время последней активности
$_SESSION['last_activity'] = time();

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: log_in.php');
    exit;
}

// Инициализация ошибок
$errors = [];

// Получаем значения полей из формы
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Проверка email
if (empty($email)) {
    $errors['email'] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format.";
}

// Проверка пароля
if (empty($password)) {
    $errors['password'] = "Password is required.";
}

if (empty($errors)) {
    $filePath = 'user.json';

    // Проверяем существование файла
    if (file_exists($filePath)) {
        $users = json_decode(file_get_contents($filePath), true);

        // Проверяем, что файл содержит массив
        if (is_array($users)) {
            $userFound = false;

            // Хэшируем email для поиска
            $hashedEmail = hash('sha256', $email);

            foreach ($users as $user) {
                // Сравнение хэшированного email
                if (isset($user['email']) && $user['email'] === $hashedEmail) {
                    $userFound = true;

                    // Проверка пароля
                    if (isset($user['password']) && password_verify($password, $user['password'])) {
                        // Успешный вход
                        $_SESSION['current_user_email'] = $email;
                        $_SESSION['user'] = [
                            'name'       => $user['name'],
                            'surname'    => $user['surname'],
                            'email'       => $email,
                            'bio'        => $user['bio'] ?? '',
                            'photo'      => $user['photo'] ?? '',
                            'photo_mime' => $user['photo_mime'] ?? '',
                        ];
                        header('Location: account.php');
                        exit;
                    } else {
                        $errors['password'] = "Incorrect email or password.";
                        break;
                    }
                }
            }

            if (!$userFound) {
                $errors['email'] = "Incorrect email or password.";
            }
        } else {
            $errors['general'] = "The user file is corrupted.";
        }
    } else {
        $errors['general'] = "The user file does not exist.";
    }
}

// Сохраняем ошибки и старые данные
$_SESSION['errors'] = $errors;
$_SESSION['oldValues'] = $_POST;

// Перенаправление обратно на страницу входа
header('Location: log_in.php');
exit;
