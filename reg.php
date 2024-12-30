<?php
    session_start();
    $errors = [];
    $oldValues = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $date_birth = trim($_POST['date_birth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    $oldValues = $_POST;

    // Валидация данных
    if (empty($name)) {
        $errors['name'] = "Name is required.";
    } elseif (!preg_match('/^[a-zA-Z]+$/', $name)) {
        $errors['name'] = "Name can only contain Latin letters.";
    }

    if (empty($surname)) {
        $errors['surname'] = "Surname is required.";
    } elseif (!preg_match('/^[a-zA-Z]+$/', $surname)) {
        $errors['surname'] = "Surname can only contain Latin letters.";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 4) {
        $errors['password'] = "Password must be at least 4 characters long.";
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($date_birth)) {
        $errors['date_birth'] = "Date of birth is required.";
    } else {
        $birthDate = new DateTime($date_birth);
        $currentDate = new DateTime();
        $age = $currentDate->diff($birthDate)->y;
        if ($age < 18) {
            $errors['date_birth'] = "You must be at least 18 years old.";
        }
    }

    if (empty($gender)) {
        $errors['gender'] = "Gender is required.";
    }

    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        $errors['profile_photo'] = "Profile photo is required.";
    } else {
        // Дополнительные проверки
        if (!file_exists($_FILES['profile_photo']['tmp_name'])) {
            $errors['profile_photo'] = "Invalid file upload.";
        } else {
            // Разрешённые MIME-типы
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

            // Определяем MIME
            $photoMimeType = mime_content_type($_FILES['profile_photo']['tmp_name']);
            if ($photoMimeType === false) {
                $errors['profile_photo'] = "Invalid file upload.";
            } elseif (!in_array($photoMimeType, $allowedMimeTypes)) {
                $errors['profile_photo'] = "Only JPG, PNG, and GIF files are allowed.";
            }
        }
    }


    // Проверка, что форма не пустая
    if (empty(array_filter($errors))) {
        $filePath = 'user.json';

        $users = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];
        if (!is_array($users)) {
            $users = [];
        }


        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $hashedEmail = hash('sha256', $email);
            $photoData = file_get_contents($_FILES['profile_photo']['tmp_name']);
            $photoBase64 = base64_encode($photoData);

            $newUser = [
                'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                'surname' => htmlspecialchars($surname, ENT_QUOTES, 'UTF-8'),
                'email' => $hashedEmail,
                'password' => $hashedPassword,
                'gender' => htmlspecialchars($gender, ENT_QUOTES, 'UTF-8'),
                'bio' => htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'),
                'date_birth' => $date_birth,
                'photo'      => $photoBase64,      // Строка Base64
                'photo_mime' => $photoMimeType     // MIME-тип (image/jpeg, image/png или image/gif)
            ];

            $users[] = $newUser;
            file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT));

            header('Location: log_in.php');
            exit;
        }
    }

    $_SESSION['errors'] = $errors;
    $_SESSION['oldValues'] = $oldValues;

    header('Location: register.php');
    exit;
}
?>


