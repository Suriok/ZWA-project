<?php
session_start(); // Zahájení relace pro načtení chyb a starých hodnot z předchozího odeslání formuláře

// Načtení chyb z relace, pokud existují
$errors = $_SESSION['errors'] ?? []; // Chyby při validaci
$oldValues = $_SESSION['oldValues'] ?? []; // Staré hodnoty zadané uživatelem

// Vymazání chyb a starých hodnot z relace, aby se neobjevily při dalším načtení stránky
unset($_SESSION['errors'], $_SESSION['oldValues']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Hlavicka stranky obsahujici logo a menu -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <!-- Připojení externího CSS stylu -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Nastavení ikony stránky -->
    <link rel="icon" type="image/png" href="foto/favicon.svg" sizes="48x48">
    <!-- Připojení JavaScriptu -->
    <script src="js/card.js" defer></script>
</head>
<body>
<header>
    <!-- Hlavička stránky -->
    <div class="header-container">
        <!-- Logo aplikace -->
        <div class="header-logo">
            <a class="header-logo" href="index.php">
                <img class="logo_image" alt="logo-hearte" src="foto/logo-hearte.png">
                <div class="logo_text">
                    <div class="logo_text_big">FlAME</div>
                    <div class="logo_text_small">FIND YOUR LOVE HERE</div>
                </div>
            </a>
        </div>
        <!-- Profilová fotografie -->
        <img class="profil_image" alt="profile photo" src="foto/profile.png">
        <!-- Navigační menu -->
        <div class="burger-menu" id="burgerMenu">
            <a href="log_in.php">Log In</a>
            <a href="register.php">Registration</a>
        </div>
    </div>
    <hr class="line_header">
</header>

<!-- Sekce vytvoření účtu -->
<div class="create_account">
    <div class="upper_part">
        <div class="main_text">
            <h1 class="create_account_text">Create Account</h1> <!-- Hlavní nadpis -->
        </div>
    </div>
    <div class="down_part">
        <!-- Formulář pro registraci -->
        <form class="account_form" method="POST" action="reg.php" enctype="multipart/form-data">
            <!-- Pole pro nahrání profilové fotografie -->
            <div class="form_photo">
                <div id="photo_container">
                    <img id="photo_preview" src="#" alt="Profile Photo Preview" style="display: none;"> <!-- Náhled fotografie -->
                </div>
                <label for="profile_photo" class="information_text">Upload Profile Photo</label>
                <input id="profile_photo" type="file" name="profile_photo" accept="image/*" onchange="previewPhoto(event)"> <!-- Výběr fotografie -->
                <span class="error_message"><?php echo $errors['profile_photo'] ?? ''; ?></span> <!-- Zobrazení chyby pro fotografii -->
            </div>

            <!-- Pole pro zadání jména a příjmení -->
            <div class="firs_last_name">
                <div class="form">
                    <label for="name" class="information_text">Name</label>
                    <input class="input_inf" id="name" type="text" name="name" placeholder="John" value="<?php echo htmlspecialchars($oldValues['name'] ?? ''); ?>"> <!-- Jméno -->
                    <span class="error_message"><?php echo $errors['name'] ?? ''; ?></span> <!-- Chyba pro jméno -->
                </div>
                <div class="form">
                    <label for="surname" class="information_text">Surname</label>
                    <input class="input_inf" id="surname" type="text" name="surname" placeholder="Doe" value="<?php echo htmlspecialchars($oldValues['surname'] ?? ''); ?>"> <!-- Příjmení -->
                    <span class="error_message"><?php echo $errors['surname'] ?? ''; ?></span> <!-- Chyba pro příjmení -->
                </div>
            </div>

            <!-- Pole pro zadání emailu -->
            <div class="form">
                <label for="email" class="information_text">Email</label>
                <input class="input_inf" id="email" type="email" name="email" placeholder="johndoe@gmail.com" value="<?php echo htmlspecialchars($oldValues['email'] ?? ''); ?>"> <!-- Email -->
                <span class="error_message"><?php echo $errors['email'] ?? ''; ?></span> <!-- Chyba pro email -->
            </div>

            <!-- Pole pro zadání hesla a potvrzení hesla -->
            <div class="form">
                <label for="password" class="information_text">Password</label>
                <input class="input_inf" id="password" type="password" name="password"> <!-- Heslo -->
                <span class="error_message"><?php echo $errors['password'] ?? ''; ?></span> <!-- Chyba pro heslo -->
            </div>
            <div class="form">
                <label for="confirm_password" class="information_text">Confirm Password</label>
                <input class="input_inf" id="confirm_password" type="password" name="confirm_password"> <!-- Potvrzení hesla -->
                <span class="error_message"><?php echo $errors['confirm_password'] ?? ''; ?></span> <!-- Chyba pro potvrzení hesla -->
            </div>

            <!-- Pole pro zadání data narození -->
            <div class="form">
                <label for="date_birth" class="information_text">Birth Date</label>
                <input class="input_inf" id="date_birth" type="date" name="date_birth" value="<?php echo htmlspecialchars($oldValues['date_birth'] ?? ''); ?>"> <!-- Datum narození -->
                <span class="error_message"><?php echo $errors['date_birth'] ?? ''; ?></span> <!-- Chyba pro datum narození -->
            </div>

            <!-- Výběr pohlaví -->
            <div class="form">
                <label for="gender" class="information_text">Gender</label>
                <select class="input_inf" name="gender" id="gender">
                    <option value="" <?php echo empty($oldValues['gender']) ? 'selected' : ''; ?>>Select your gender</option>
                    <option value="woman" <?php echo ($oldValues['gender'] ?? '') === 'woman' ? 'selected' : ''; ?>>Woman</option>
                    <option value="man" <?php echo ($oldValues['gender'] ?? '') === 'man' ? 'selected' : ''; ?>>Man</option>
                    <option value="non_binary" <?php echo ($oldValues['gender'] ?? '') === 'non_binary' ? 'selected' : ''; ?>>Non-binary</option>
                    <option value="other_option" <?php echo ($oldValues['gender'] ?? '') === 'other_option' ? 'selected' : ''; ?>>Other</option>
                </select>
                <span class="error_message"><?php echo $errors['gender'] ?? ''; ?></span> <!-- Chyba pro pohlaví -->
            </div>

            <!-- Pole pro zadání biografie -->
            <div class="div_bio">
                <div class="form">
                    <label for="bio" class="information_text">Bio</label>
                    <textarea id="bio" class="input_inf_bio" name="bio" placeholder="Passionate about creating user-friendly applications." rows="4" cols="50" maxlength="120"><?php echo htmlspecialchars($oldValues['bio'] ?? ''); ?></textarea> <!-- Biografie -->
                    <div id="charCount" class="char_count">0/120</div> <!-- Počítadlo znaků -->
                </div>
            </div>

            <!-- Tlačítko pro odeslání formuláře -->
            <div class="information_button">
                <button class="create_account_button" name="create_account_button" type="submit">Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Patička stránky -->
<footer>
    <div class="swip">
        <a class="swip" href="index.php">
            <h5 class="footer_text">Swipe</h5>
        </a>
    </div>
    <div class="people">
        <a class="people" href="people.php">
            <!-- SVG ikona -->
            <svg fill="#ffffff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff" class="footer-icon">
                <path d="M16.03,18.616l5.294-4.853a1,1,0,0,1,1.352,1.474l-6,5.5a1,1,0,0,1-1.383-.03l-3-3a1,1,0,0,1,1.414-1.414ZM1,20a9.01,9.01,0,0,1,5.623-8.337A4.981,4.981,0,1,1,10,13a7.011,7.011,0,0,0-6.929,6H10a1,1,0,0,1,0,2H2A1,1,0,0,1,1,20ZM7,8a3,3,0,1,0,3-3A3,3,0,0,0,7,8Z"></path>
            </svg>
            <h5 class="footer_text">People</h5>
        </a>
    </div>
</footer>
</body>
</html>
