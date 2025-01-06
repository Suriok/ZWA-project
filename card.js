// ==================== Pop-up okno při nepřihlášeném uživateli ====================
document.addEventListener('DOMContentLoaded', function () {
    const isLoggedIn = window.isLoggedIn; // Získáme proměnnou z PHP

    if (!isLoggedIn) {
        const popup = document.getElementById('popup'); // Pop-up okno
        const overlay = document.getElementById('popup-overlay'); // Překrytí
        const loginButton = document.querySelector('.login-button'); // Tlačítko "Přihlásit se"
        const registerButton = document.querySelector('.register-button'); // Tlačítko "Registrovat se"

        // Zobrazení pop-up okna a překrytí
        popup.style.display = 'block';
        overlay.style.display = 'block';

        // Zpracování tlačítka "Přihlásit se"
        if (loginButton) {
            loginButton.addEventListener('click', () => {
                window.location.href = 'log_in.php';
            });
        }

        // Zpracování tlačítka "Registrovat se"
        if (registerButton) {
            registerButton.addEventListener('click', () => {
                window.location.href = 'register.php';
            });
        }
    }
});

// ==================== Burger menu ====================
document.addEventListener('DOMContentLoaded', () => {
    const profileImage = document.querySelector('.profil_image'); // Tlačítko pro otevření menu
    const burgerMenu = document.getElementById('burgerMenu'); // Samotné menu

    if (profileImage && burgerMenu) {
        // Přepínání viditelnosti menu
        profileImage.addEventListener('click', () => {
            burgerMenu.style.display = burgerMenu.style.display === 'flex' ? 'none' : 'flex';
        });

        // Zavření menu při kliknutí mimo jeho oblast
        document.addEventListener('click', (event) => {
            if (!burgerMenu.contains(event.target) && event.target !== profileImage) {
                burgerMenu.style.display = 'none';
            }
        });
    }
});

// ==================== Náhled fotografie (Photo Preview) ====================
document.addEventListener('DOMContentLoaded', () => {
    const inputFile = document.getElementById('profile_photo'); // Vstupní pole pro nahrání souboru
    const previewImg = document.getElementById('photo_preview'); // Element <img> pro náhled

    if (inputFile && previewImg) {
        inputFile.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (!file) {
                previewImg.style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }
});

// ==================== Počítadlo znaků pro pole "Bio" ====================
document.addEventListener("DOMContentLoaded", function () {
    const bio = document.getElementById("bio"); // Textarea pro bio
    const charCount = document.getElementById("charCount"); // Počítadlo znaků

    bio.addEventListener("input", function () {
        const currentLength = bio.value.length;
        charCount.textContent = `${currentLength}/120`; // Zobrazení aktuální délky
    });
});

// ==================== Validace formuláře ====================
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector(".account_form"); // Formulář
    const requiredFields = document.querySelectorAll("input[required], select[required], textarea[required]");

    // Validace jednotlivých polí
    function validateField(field) {
        if (!field.value.trim()) {
            field.classList.add("error"); // Přidání třídy "error" pro červené zvýraznění
        } else {
            field.classList.remove("error");
        }
    }

    // Před odesláním formuláře
    form.addEventListener("submit", function (event) {
        let isValid = true;

        requiredFields.forEach((field) => {
            validateField(field);
            if (!field.value.trim()) {
                isValid = false;
            }
        });

        if (!isValid) {
            event.preventDefault(); // Zastavení odeslání formuláře při chybách
        }
    });

    // Dynamická validace při změně vstupu
    requiredFields.forEach((field) => {
        field.addEventListener("input", function () {
            validateField(field);
        });
    });

    // Validace shody hesel
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");
    const passwordError = document.getElementById("passwordError");

    function validatePasswords() {
        if (password.value && confirmPassword.value && password.value !== confirmPassword.value) {
            password.classList.add("error");
            confirmPassword.classList.add("error");
            if (passwordError) passwordError.style.display = "block"; // Zobrazení chyby
            return false;
        } else {
            password.classList.remove("error");
            confirmPassword.classList.remove("error");
            if (passwordError) passwordError.style.display = "none"; // Skrytí chyby
            return true;
        }
    }

    password.addEventListener("input", validatePasswords);
    confirmPassword.addEventListener("input", validatePasswords);
});
