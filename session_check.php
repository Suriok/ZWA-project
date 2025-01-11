<?php
/**
 *
 * Kontrola, zda již byla relace zahájena.
 *
 * Ověřuje stav relace a zahajuje novou, pokud není aktivní.
 *
 * @return void
 *
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Zahájení nové relace, pokud nebyla spuštěna
}

$maxInactiveTime = 15 * 60; // Maximální doba nečinnosti: 15 minut

/**
 *
 * Kontrola nečinnosti relace.
 *
 * Sleduje čas poslední aktivity a ukončuje relaci, pokud doba nečinnosti přesáhne stanovený limit.
 *
 * @return void
 *
 */
if (isset($_SESSION['last_activity'])) { // Kontrola, zda je nastavena poslední aktivita
    $inactiveTime = time() - $_SESSION['last_activity']; // Výpočet času od poslední aktivity
    if ($inactiveTime > $maxInactiveTime) { // Pokud čas přesáhne maximální dobu
        session_unset(); // Vymazání všech dat v relaci
        session_destroy(); // Ukončení relace
        header("Location: log_in.php"); // Přesměrování na přihlašovací stránku
        exit(); // Ukončení skriptu
    }
}

/**
 *
 * Aktualizace poslední aktivity.
 *
 * Aktualizuje čas poslední aktivity na aktuální čas.
 *
 * @return void
 *
 */
$_SESSION['last_activity'] = time(); // Nastavení aktuálního času jako poslední aktivity

/**
 *
 * Funkce pro kontrolu role administrátora.
 *
 * Ověřuje, zda je aktuální uživatel přihlášen a má roli administrátora.
 *
 * @return bool Vrací true, pokud má uživatel roli administrátora, jinak false.
 *
 */
function isUserAdmin() {
    // Vrátí true, pokud je uživatel přihlášen a má roli administrátora
    return isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin'] === true;
}
?>
