<?php
session_start(); // Zahájení nové nebo obnovení existující relace

/**
 * Nastavení maximální doby nečinnosti.
 *
 * Pokud uživatel překročí maximální dobu nečinnosti, jeho relace bude ukončena.
 *
 * @var int $maxInactiveTime Maximální doba nečinnosti v sekundách (15 minut).
 */
$maxInactiveTime = 15 * 60;

/**
 * Kontrola nečinnosti uživatele.
 *
 * Tato funkce kontroluje, zda uživatel byl nečinný déle než povolený čas.
 * Pokud ano, relace bude ukončena a uživatel bude přesměrován na přihlašovací stránku.
 */
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $maxInactiveTime) {
    session_unset(); // Vymazání všech dat uložených v relaci
    session_destroy(); // Ukončení relace
    header("Location: log_in.php"); // Přesměrování na přihlašovací stránku
    exit(); // Ukončení skriptu
}

/**
 * Aktualizace času poslední aktivity.
 *
 * Tato funkce ukládá aktuální čas jako čas poslední aktivity uživatele.
 */
$_SESSION['last_activity'] = time();

/**
 * Kontrola metody požadavku.
 *
 * Zajišťuje, že požadavek na server je odesílán metodou POST.
 * Pokud tomu tak není, uživatel bude přesměrován na přihlašovací stránku.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: log_in.php'); // Přesměrování na přihlašovací stránku
    exit(); // Ukončení skriptu
}

/**
 * Validace vstupních dat.
 *
 * Získá a validuje vstupy z formuláře, jako jsou email a heslo.
 * Pokud data nejsou validní, chyby jsou uloženy do relace a uživatel je přesměrován zpět na přihlašovací stránku.
 *
 * @param string $email Email uživatele.
 * @param string $password Heslo uživatele.
 */
$errors = [];
$email = trim($_POST['email'] ?? ''); // Email uživatele
$password = trim($_POST['password'] ?? ''); // Heslo uživatele

if (empty($email)) {
    $errors['email'] = "Email je povinný.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Neplatný formát emailu.";
}

if (empty($password)) {
    $errors['password'] = "Heslo je povinné.";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors; // Uložení chyb do relace
    $_SESSION['oldValues'] = ['email' => $email]; // Uložení starých hodnot pro opětovné vyplnění formuláře
    header('Location: log_in.php'); // Přesměrování na přihlašovací stránku
    exit();
}

/**
 * Načtení uživatelských dat.
 *
 * Tato funkce čte data ze souboru `user.json` a hledá uživatele podle emailu.
 * Pokud je uživatel nalezen, ověří se jeho heslo.
 *
 * @param string $filePath Cesta k souboru s uživateli.
 * @param string $email Email uživatele.
 * @param string $password Heslo uživatele.
 * @return void Pokud je ověření úspěšné, uživatel je přesměrován na odpovídající stránku.
 */
$filePath = 'user.json';
if (file_exists($filePath)) {
    $users = json_decode(file_get_contents($filePath), true);
    if (is_array($users)) {
        $userFound = false;
        $hashedEmail = hash('sha256', $email);
        foreach ($users as $user) {
            if (isset($user['email']) && $user['email'] === $hashedEmail) {
                $userFound = true;
                if (isset($user['password']) && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['current_user_email'] = $email;
                    $_SESSION['user'] = [
                        'name'        => $user['name'],
                        'surname'     => $user['surname'],
                        'email'       => $email,
                        'bio'         => $user['bio'] ?? '',
                        'photo'       => $user['photo'] ?? '',
                        'photo_mime'  => $user['photo_mime'] ?? '',
                        'is_admin'    => $user['is_admin'] ?? false,
                    ];
                    if ($_SESSION['user']['is_admin']) {
                        header('Location: admin.php');
                    } else {
                        header('Location: account.php');
                    }
                    exit();
                } else {
                    $errors['password'] = "Nesprávný email nebo heslo.";
                    break;
                }
            }
        }
        if (!$userFound) {
            $errors['email'] = "Nesprávný email nebo heslo.";
        }
    } else {
        $errors['general'] = "Soubor s uživateli je poškozen.";
    }
} else {
    $errors['general'] = "Soubor s uživateli neexistuje.";
}

/**
 * Přesměrování při chybách přihlášení.
 *
 * Tato funkce uloží chyby do relace a přesměrovává uživatele zpět na přihlašovací stránku.
 */
$_SESSION['errors'] = $errors;
$_SESSION['oldValues'] = ['email' => $email];
header('Location: log_in.php');
exit();
?>