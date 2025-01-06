<?php
session_start(); // Zahájení relace pro ukládání dat o chybách a starých hodnotách formuláře

$errors = []; // Inicializace pole pro ukládání chyb při validaci
$oldValues = []; // Inicializace pole pro ukládání starých hodnot z formuláře

// ============================
// Kontrola metody požadavku
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Kontrola, zda byl požadavek odeslán metodou POST
    // Získání a očištění dat z formuláře
    $name = trim($_POST['name'] ?? ''); // Jméno
    $surname = trim($_POST['surname'] ?? ''); // Příjmení
    $email = trim($_POST['email'] ?? ''); // Email
    $password = trim($_POST['password'] ?? ''); // Heslo
    $confirmPassword = trim($_POST['confirm_password'] ?? ''); // Potvrzení hesla
    $date_birth = trim($_POST['date_birth'] ?? ''); // Datum narození
    $gender = trim($_POST['gender'] ?? ''); // Pohlaví
    $bio = trim($_POST['bio'] ?? ''); // Biografie

    $oldValues = $_POST; // Uložení starých hodnot pro opětovné vyplnění formuláře

    // ============================
    // Validace dat z formuláře
    // ============================

    // Validace jména
    if (empty($name)) {
        $errors['name'] = "Jméno je povinné."; // Jméno je povinné
    } elseif (!preg_match('/^[a-zA-Z]+$/', $name)) {
        $errors['name'] = "Jméno může obsahovat pouze latinská písmena."; // Pouze latinská písmena
    }

    // Validace příjmení
    if (empty($surname)) {
        $errors['surname'] = "Příjmení je povinné."; // Příjmení je povinné
    } elseif (!preg_match('/^[a-zA-Z]+$/', $surname)) {
        $errors['surname'] = "Příjmení může obsahovat pouze latinská písmena."; // Pouze latinská písmena
    }

    // Validace emailu
    if (empty($email)) {
        $errors['email'] = "Email je povinný."; // Email je povinný
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Neplatný formát emailu."; // Neplatný formát emailu
    }

    // Validace hesla
    if (empty($password)) {
        $errors['password'] = "Heslo je povinné."; // Heslo je povinné
    } elseif (strlen($password) < 4) {
        $errors['password'] = "Heslo musí mít alespoň 4 znaky."; // Minimální délka hesla je 4 znaky
    }

    // Kontrola potvrzení hesla
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = "Hesla se neshodují."; // Hesla se neshodují
    }

    // Validace data narození
    if (empty($date_birth)) {
        $errors['date_birth'] = "Datum narození je povinné."; // Datum narození je povinné
    } else {
        $birthDate = new DateTime($date_birth); // Převod data narození na objekt DateTime
        $currentDate = new DateTime(); // Získání aktuálního data
        $age = $currentDate->diff($birthDate)->y; // Výpočet věku
        if ($age < 18) {
            $errors['date_birth'] = "Musíte být starší 18 let."; // Minimální věk je 18 let
        }
    }

    // Validace pohlaví
    if (empty($gender)) {
        $errors['gender'] = "Pohlaví je povinné."; // Pohlaví je povinné
    }

    // Validace profilové fotografie
    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        $errors['profile_photo'] = "Profilová fotografie je povinná."; // Profilová fotografie je povinná
    } else {
        // Kontrola nahraného souboru
        if (!file_exists($_FILES['profile_photo']['tmp_name'])) {
            $errors['profile_photo'] = "Neplatný soubor."; // Neplatný soubor
        } else {
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif']; // Povolené typy souborů
            $photoMimeType = mime_content_type($_FILES['profile_photo']['tmp_name']); // Zjištění typu souboru
            if ($photoMimeType === false) {
                $errors['profile_photo'] = "Neplatný soubor."; // Neplatný typ souboru
            } elseif (!in_array($photoMimeType, $allowedMimeTypes)) {
                $errors['profile_photo'] = "Jsou povoleny pouze soubory JPG, PNG nebo GIF."; // Povolené typy
            }
        }
    }

    // ============================
    // Zpracování dat po validaci
    // ============================
    if (empty(array_filter($errors))) { // Pokud nejsou žádné chyby
        $filePath = 'user.json'; // Cesta k souboru uživatelů
        $users = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : []; // Načtení existujících uživatelů
        if (!is_array($users)) {
            $users = []; // Pokud je soubor poškozený, vytvoříme nové pole
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Hashování hesla
        $hashedEmail = hash('sha256', $email); // Hashování emailu
        $photoData = file_get_contents($_FILES['profile_photo']['tmp_name']); // Načtení dat z fotografie
        $photoBase64 = base64_encode($photoData); // Převod dat do Base64

        // Vytvoření nového uživatele
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

        $users[] = $newUser; // Přidání uživatele do pole
        file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT)); // Uložení dat do souboru

        header('Location: log_in.php'); // Přesměrování na přihlašovací stránku
        exit();
    }

    // Uložení chyb a starých hodnot do relace
    $_SESSION['errors'] = $errors;
    $_SESSION['oldValues'] = $oldValues;

    header('Location: register.php'); // Přesměrování zpět na registrační formulář
    exit();
}
?>
