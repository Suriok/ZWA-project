<?php
session_start(); // Zahájení relace pro uložení chyb a starých hodnot z formuláře

$errors = [];    // Pole pro chyby
$oldValues = []; // Pole pro staré hodnoty z formuláře

// ============================
// Kontrola metody požadavku
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Získání polí z $_POST
    $name            = trim($_POST['name'] ?? '');
    $surname         = trim($_POST['surname'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $date_birth      = trim($_POST['date_birth'] ?? '');
    $gender          = trim($_POST['gender'] ?? '');
    $bio             = trim($_POST['bio'] ?? '');

    // Uložení starých hodnot pro opětovné vyplnění
    $oldValues = $_POST;

    // ============================
    // Validace polí
    // ============================

    // Jméno
    if ($name === '') {
        $errors['name'] = "Jméno je povinné.";
    } elseif (!preg_match('/^[a-zA-Z]+$/', $name)) {
        $errors['name'] = "Jméno může obsahovat pouze latinská písmena.";
    }

    // Příjmení
    if ($surname === '') {
        $errors['surname'] = "Příjmení je povinné.";
    } elseif (!preg_match('/^[a-zA-Z]+$/', $surname)) {
        $errors['surname'] = "Příjmení může obsahovat pouze latinská písmena.";
    }

    // Email
    if ($email === '') {
        $errors['email'] = "Email je povinný.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Neplatný formát emailu.";
    }

    // Heslo
    if ($password === '') {
        $errors['password'] = "Heslo je povinné.";
    } elseif (strlen($password) < 4) {
        $errors['password'] = "Heslo musí mít alespoň 4 znaky.";
    }

    // Potvrzení hesla
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = "Hesla se neshodují.";
    }

    // Datum narození
    if ($date_birth === '') {
        $errors['date_birth'] = "Datum narození je povinné.";
    } else {
        $birthDate   = new DateTime($date_birth);
        $currentDate = new DateTime();
        $age         = $currentDate->diff($birthDate)->y;
        if ($age < 18) {
            $errors['date_birth'] = "Musíte být starší 18 let.";
        }
    }

    // Pohlaví
    if ($gender === '') {
        $errors['gender'] = "Pohlaví je povinné.";
    }

    // Profilová fotografie
    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        $errors['profile_photo'] = "Profilová fotografie je povinná.";
    } else {
        if (!file_exists($_FILES['profile_photo']['tmp_name'])) {
            $errors['profile_photo'] = "Neplatný soubor.";
        } else {
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $photoMimeType    = mime_content_type($_FILES['profile_photo']['tmp_name']);
            if ($photoMimeType === false) {
                $errors['profile_photo'] = "Neplatný soubor.";
            } elseif (!in_array($photoMimeType, $allowedMimeTypes)) {
                $errors['profile_photo'] = "Jsou povoleny pouze soubory JPG, PNG nebo GIF.";
            }
        }
    }

    // ============================
    // Pokud zatím nejsou chyby, kontrola,
    // zda heslo a email už neexistují
    // ============================
    if (empty($errors)) {
        // Cesta k souboru s uživateli
        $filePath = 'user.json';
        $users    = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];
        if (!is_array($users)) {
            $users = [];
        }

        // Hash emailu a hesla (SHA-256)
        $hashedEmail    = hash('sha256', $email);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // 1) Kontrola, zda email (v SHA-256) už v databázi neexistuje
        foreach ($users as $existingUser) {
            if (isset($existingUser['email']) && $existingUser['email'] === $hashedEmail) {
                $errors['email'] = "Tento email už existuje. Zvolte jiný email.";
                break;
            }
        }

        // 2) Kontrola, zda heslo (v SHA-256) už v databázi neexistuje
        if (empty($errors)) {
            foreach ($users as $existingUser) {
                if (isset($existingUser['password']) && $existingUser['password'] === $hashedPassword) {
                    $errors['password'] = "Tento heslový hash již existuje. Zvolte jiné heslo.";
                    break;
                }
            }
        }
    }

    // ============================
    // Uložení / nebo vrácení s chybami
    // ============================
    if (empty($errors)) {
        // Pokud nejsou chyby, uložíme uživatele
        $photoData     = file_get_contents($_FILES['profile_photo']['tmp_name']);
        $photoBase64   = base64_encode($photoData);
        $photoMimeType = mime_content_type($_FILES['profile_photo']['tmp_name']);

        $newUser = [
            'name'        => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'surname'     => htmlspecialchars($surname, ENT_QUOTES, 'UTF-8'),
            'email'       => $hashedEmail,      // hash emailu
            'password'    => $hashedPassword,   // hash hesla (SHA-256)
            'gender'      => htmlspecialchars($gender, ENT_QUOTES, 'UTF-8'),
            'bio'         => $bio,
            'is_admin'    => false,
            'date_birth'  => $date_birth,
            'photo'       => $photoBase64,
            'photo_mime'  => $photoMimeType
        ];

        // Přidáme do pole a uložíme do user.json
        $users[] = $newUser;
        file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT));

        // Přesměrování na log_in.php
        header('Location: log_in.php');
        exit();
    }

    // Pokud se vyskytly chyby, uložíme je do SESSION a vrátíme se
    $_SESSION['errors']    = $errors;
    $_SESSION['oldValues'] = $oldValues;
    header('Location: register.php');
    exit();
}
?>
