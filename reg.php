<?php
session_start(); // Zahájení relace pro uložení chyb a starých hodnot z formuláře

$errors = [];    // Pole pro chyby
$oldValues = []; // Pole pro staré hodnoty z formuláře

// ============================
// Kontrola metody požadavku
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     *
     * Získání polí z formuláře.
     *
     * Zpracovává vstupní hodnoty odeslané metodou POST a odstraňuje přebytečné mezery.
     *
     * @param array $_POST Data odeslaná formulářem.
     * @return void
     *
     */
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

    /**
     *
     * Validace jména.
     *
     * Kontroluje, zda jméno není prázdné a obsahuje pouze latinská písmena.
     *
     * @param string $name Jméno zadané uživatelem.
     * @return void
     *
     */
    if ($name === '') {
        $errors['name'] = "Jméno je povinné.";
    } elseif (!preg_match('/^[a-zA-Z]+$/', $name)) {
        $errors['name'] = "Jméno může obsahovat pouze latinská písmena.";
    }

    /**
     *
     * Validace příjmení.
     *
     * Kontroluje, zda příjmení není prázdné a obsahuje pouze latinská písmena.
     *
     * @param string $surname Příjmení zadané uživatelem.
     * @return void
     *
     */
    if ($surname === '') {
        $errors['surname'] = "Příjmení je povinné.";
    } elseif (!preg_match('/^[a-zA-Z]+$/', $surname)) {
        $errors['surname'] = "Příjmení může obsahovat pouze latinská písmena.";
    }

    /**
     *
     * Validace emailu.
     *
     * Ověřuje, zda je email vyplněn a má platný formát.
     *
     * @param string $email Emailová adresa zadaná uživatelem.
     * @return void
     *
     */
    if ($email === '') {
        $errors['email'] = "Email je povinný.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Neplatný formát emailu.";
    }

    /**
     *
     * Validace hesla.
     *
     * Kontroluje, zda heslo není prázdné a splňuje minimální délku.
     *
     * @param string $password Heslo zadané uživatelem.
     * @return void
     *
     */
    if ($password === '') {
        $errors['password'] = "Heslo je povinné.";
    } elseif (strlen($password) < 4) {
        $errors['password'] = "Heslo musí mít alespoň 4 znaky.";
    }

    /**
     *
     * Kontrola potvrzení hesla.
     *
     * Ověřuje, zda zadané heslo odpovídá potvrzenému heslu.
     *
     * @param string $password Heslo zadané uživatelem.
     * @param string $confirmPassword Potvrzené heslo.
     * @return void
     *
     */
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = "Hesla se neshodují.";
    }

    /**
     *
     * Validace data narození.
     *
     * Ověřuje, zda je datum narození zadáno a uživatel je starší 18 let.
     *
     * @param string $date_birth Datum narození zadané uživatelem.
     * @return void
     *
     */
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

    /**
     *
     * Validace pohlaví.
     *
     * Kontroluje, zda uživatel vybral pohlaví.
     *
     * @param string $gender Pohlaví zadané uživatelem.
     * @return void
     *
     */
    if ($gender === '') {
        $errors['gender'] = "Pohlaví je povinné.";
    }

    /**
     *
     * Validace profilové fotografie.
     *
     * Ověřuje, zda byla nahrána fotografie a má povolený formát.
     *
     * @param array $_FILES Nahraná fotografie uživatele.
     * @return void
     *
     */
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
    /**
     *
     * Kontrola existujících uživatelů.
     *
     * Ověřuje, zda email nebo heslo již neexistují v databázi.
     *
     * @param string $email Emailová adresa uživatele.
     * @param string $password Heslo uživatele.
     * @return void
     *
     */
    if (empty($errors)) {
        $filePath = 'user.json';
        $users    = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];
        if (!is_array($users)) {
            $users = [];
        }

        $hashedEmail    = hash('sha256', $email);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        foreach ($users as $existingUser) {
            if (isset($existingUser['email']) && $existingUser['email'] === $hashedEmail) {
                $errors['email'] = "Tento email už existuje. Zvolte jiný email.";
                break;
            }
        }

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
    /**
     *
     * Uložení nového uživatele.
     *
     * Pokud nejsou chyby, uživatel je přidán do databáze a přesměrován na přihlašovací stránku.
     *
     * @param array $newUser Data nového uživatele.
     * @return void
     *
     */
    if (empty($errors)) {
        $photoData     = file_get_contents($_FILES['profile_photo']['tmp_name']);
        $photoBase64   = base64_encode($photoData);
        $photoMimeType = mime_content_type($_FILES['profile_photo']['tmp_name']);

        $newUser = [
            'name'        => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'surname'     => htmlspecialchars($surname, ENT_QUOTES, 'UTF-8'),
            'email'       => $hashedEmail,
            'password'    => $hashedPassword,
            'gender'      => htmlspecialchars($gender, ENT_QUOTES, 'UTF-8'),
            'bio'         => $bio,
            'is_admin'    => false,
            'date_birth'  => $date_birth,
            'photo'       => $photoBase64,
            'photo_mime'  => $photoMimeType
        ];

        $users[] = $newUser;
        file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT));

        header('Location: log_in.php');
        exit();
    }

    $_SESSION['errors']    = $errors;
    $_SESSION['oldValues'] = $oldValues;
    header('Location: register.php');
    exit();
}
?>

