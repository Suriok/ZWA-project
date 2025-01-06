<?php
session_start(); // Zahájení nové nebo obnovení existující relace

// =========================
// Nastavení maximální doby nečinnosti
// =========================
$maxInactiveTime = 15 * 60; // Maximální doba nečinnosti nastavena na 15 minut

// =========================
// Kontrola nečinnosti uživatele
// =========================
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $maxInactiveTime) {
    session_unset(); // Vymazání všech dat uložených v relaci
    session_destroy(); // Ukončení relace
    header("Location: log_in.php"); // Přesměrování na přihlašovací stránku
    exit(); // Ukončení skriptu
}

// Aktualizace času poslední aktivity uživatele
$_SESSION['last_activity'] = time();

// =========================
// Kontrola metody požadavku
// =========================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Ověření, že požadavek je metodou POST
    header('Location: log_in.php'); // Přesměrování na přihlašovací stránku
    exit(); // Ukončení skriptu
}

// =========================
// Validace vstupních dat
// =========================

// Inicializace pole pro chybové zprávy
$errors = [];

// Získání a vyčištění vstupních údajů z formuláře
$email = trim($_POST['email'] ?? ''); // Email uživatele
$password = trim($_POST['password'] ?? ''); // Heslo uživatele

// Validace emailu
if (empty($email)) { // Kontrola, zda bylo zadáno pole email
    $errors['email'] = "Email je povinný.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Ověření formátu emailu
    $errors['email'] = "Neplatný formát emailu.";
}

// Validace hesla
if (empty($password)) { // Kontrola, zda bylo zadáno pole heslo
    $errors['password'] = "Heslo je povinné.";
}

// =========================
// Přesměrování při chybách validace
// =========================
if (!empty($errors)) { // Pokud jsou nalezeny chyby
    $_SESSION['errors'] = $errors; // Uložení chyb do relace
    $_SESSION['oldValues'] = ['email' => $email]; // Uložení starých hodnot pro opětovné vyplnění formuláře
    header('Location: log_in.php'); // Přesměrování na přihlašovací stránku
    exit(); // Ukončení skriptu
}

// =========================
// Načtení uživatelských dat
// =========================
$filePath = 'user.json'; // Cesta k souboru uživatelů

// Kontrola, zda soubor uživatelů existuje
if (file_exists($filePath)) {
    $users = json_decode(file_get_contents($filePath), true); // Načtení dat ze souboru a jejich dekódování

    // Kontrola, zda je obsah souboru pole
    if (is_array($users)) {
        $userFound = false; // Příznak, zda byl uživatel nalezen
        $hashedEmail = hash('sha256', $email); // Vytvoření hash emailu pro porovnání

        // =========================
        // Hledání uživatele podle emailu
        // =========================
        foreach ($users as $user) {
            if (isset($user['email']) && $user['email'] === $hashedEmail) { // Kontrola, zda email odpovídá
                $userFound = true;

                // =========================
                // Ověření hesla
                // =========================
                if (isset($user['password']) && password_verify($password, $user['password'])) { // Porovnání hesla
                    session_regenerate_id(true); // Regenerace ID relace pro zvýšení bezpečnosti

                    // Uložení informací o uživateli do relace
                    $_SESSION['current_user_email'] = $email;
                    $_SESSION['user'] = [
                        'name'        => $user['name'], // Jméno uživatele
                        'surname'     => $user['surname'], // Příjmení uživatele
                        'email'       => $email, // Email uživatele
                        'bio'         => $user['bio'] ?? '', // Bio uživatele
                        'photo'       => $user['photo'] ?? '', // Fotografie uživatele
                        'photo_mime'  => $user['photo_mime'] ?? '', // MIME typ fotografie
                        'is_admin'    => $user['is_admin'] ?? false, // Příznak administrátora
                    ];

                    // Přesměrování na odpovídající stránku podle role uživatele
                    if ($_SESSION['user']['is_admin']) {
                        header('Location: admin.php'); // Administrátor
                    } else {
                        header('Location: account.php'); // Běžný uživatel
                    }
                    exit();
                } else {
                    $errors['password'] = "Nesprávný email nebo heslo."; // Nesprávné heslo
                    break;
                }
            }
        }

        if (!$userFound) { // Pokud nebyl uživatel nalezen
            $errors['email'] = "Nesprávný email nebo heslo.";
        }
    } else {
        $errors['general'] = "Soubor s uživateli je poškozen."; // Chyba v souboru uživatelů
    }
} else {
    $errors['general'] = "Soubor s uživateli neexistuje."; // Soubor neexistuje
}

// =========================
// Přesměrování při chybách přihlášení
// =========================
$_SESSION['errors'] = $errors; // Uložení chyb do relace
$_SESSION['oldValues'] = ['email' => $email]; // Uložení starých hodnot formuláře
header('Location: log_in.php'); // Přesměrování na přihlašovací stránku
exit(); // Ukončení skriptu
?>
