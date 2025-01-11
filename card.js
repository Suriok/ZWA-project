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
    const burgerMenu = document.getElementById('burgerMenu');     // Samotné menu

    if (profileImage && burgerMenu) {
        // Přepínání viditelnosti menu
        profileImage.addEventListener('click', () => {
            burgerMenu.style.display = (burgerMenu.style.display === 'flex') ? 'none' : 'flex';
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
document.addEventListener('DOMContentLoaded', function() {
    const bioForm  = document.getElementById('bioForm');
    const bioInput = document.getElementById('bio');
    const charCount= document.getElementById('charCount');
    const msg      = document.getElementById('bioMessage');

    // Nastavíme rovnou počáteční stav
    charCount.textContent = bioInput.value.length + '/120';

    // Při psaní
    bioInput.addEventListener('input', ()=>{
        charCount.textContent = bioInput.value.length + '/120';
    });

    // Při odeslání
    bioForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const newBio = bioInput.value.trim();
        if (newBio.length > 121) {
            msg.textContent = 'Bio must not exceed 120 characters.';
            msg.style.color = 'red';
            return;
        }
        try {
            const response = await fetch('account.php', {
                method: 'POST',
                headers: { 'Content-Type':'application/json' },
                body: JSON.stringify({
                    action: 'update_bio',
                    bio: newBio
                })
            });
            const result = await response.json();
            console.log('Server response:', result);
            if (result.message === 'Bio successfully updated.') {
                msg.textContent = 'Bio successfully updated.';
                msg.style.color = 'green';
            } else {
                msg.textContent = result.message || 'An error occurred.';
                msg.style.color = 'red';
            }
        } catch (err) {
            console.error('Error updating bio:', err);
            msg.textContent = 'An error occurred, please try again.';
            msg.style.color = 'red';
        }
    });
});

//

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

 // ====================    Inicializace Karet ====================
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

document.addEventListener("DOMContentLoaded", function () {
    const swiper = document.getElementById("swiper"); // Kontejner pro karty
    const cards = Array.from(swiper.querySelectorAll(".card")); // Všechny karty jako pole
    let currentIndex = 0; // Index aktuální karty

    /**
     * Funkce pro aktualizaci viditelnosti karet
     * Zobrazuje pouze aktuální a následující kartu, ostatní skrývá
     */
    function updateCards() {
        cards.forEach((card, index) => {
            if (index === currentIndex) {
                card.style.display = "block"; // Zobrazit aktuální kartu
                card.style.transform = "translateY(0px)"; // Aktuální karta na svém místě
                card.style.opacity = 1; // Karta je plně viditelná
                card.style.zIndex = 2; // Aktuální karta nad ostatními
            } else if (index === currentIndex + 1) {
                card.style.display = "block"; // Zobrazit následující kartu
                card.style.transform = "translateY(-15px)"; // Následující karta je mírně výše
                card.style.opacity = 0.8; // Karta je částečně průhledná
                card.style.zIndex = 1; // Následující karta pod aktuální
            } else {
                card.style.display = "none"; // Skrýt ostatní karty
            }
        });
    }

    /**
     * Funkce pro zobrazení další karty s animací
     */
    function showNextCard(direction, card, callback) {
        card.style.transition = "transform 0.5s ease, opacity 0.5s ease";
        if (direction === "right") {
            card.style.transform = "translateX(100%)"; // Přesunout kartu doprava
        } else if (direction === "left") {
            card.style.transform = "translateX(-100%)"; // Přesunout kartu doleva
        }
        card.style.opacity = 0;

        card.addEventListener(
            "transitionend",
            function () {
                card.style.display = "none";
                currentIndex++;

                if (currentIndex < cards.length) {
                    updateCards();
                } else {
                    console.log("Konec seznamu karet");
                }

                if (typeof callback === "function") {
                    callback();
                }
            },
            { once: true }
        );
    }

    /**
     * Obsluha kliknutí na tlačítko "srdce"
     * Odesílá email na server a animuje kartu doprava
     */
    async function handleHeartClick(button, card) {
        const emailToLike = button.getAttribute("data-email");

        if (!emailToLike) {
            alert("Email uživatele chybí!");
            return;
        }

        try {
            const response = await fetch("people.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email: emailToLike }),
            });

            const data = await response.json();
            if (data.success) {
                console.log("Uživatel přidán do seznamu lajků!");
            } else {
                console.warn(data.message || "Došlo k chybě na serveru.");
            }
        } catch (error) {
            console.error("Chyba při odesílání dat:", error);
            alert("Nepodařilo se spojit se serverem.");
        } finally {
            showNextCard("right", card);
        }
    }

    /**
     * Obsluha kliknutí na tlačítko "křížek"
     * Animuje kartu doleva a přesune ji na konec seznamu
     */
    function handleCrossClick(card) {
        showNextCard("left", card, function () {
            // Přesun karty na konec seznamu
            swiper.appendChild(card); // Přidání karty na konec kontejneru
            cards.push(cards.shift()); // Aktualizace pořadí v poli karet
            currentIndex--; // Snížení indexu, aby animace fungovala správně
            updateCards(); // Aktualizace viditelnosti karet
        });
    }

    // Inicializace karet po načtení stránky
    updateCards();

    /**
     * Přidání obslužných funkcí pro každou kartu
     * Připojuje klikací události pro tlačítka "srdce" a "křížek"
     */
    cards.forEach((card) => {
        const buttonHeart = card.querySelector(".button_foto_heart")?.parentElement;
        const buttonCross = card.querySelector(".button_foto_cross")?.parentElement;

        if (buttonHeart) {
            buttonHeart.addEventListener("click", function () {
                handleHeartClick(buttonHeart, card);
            });
        }

        if (buttonCross) {
            buttonCross.addEventListener("click", function () {
                handleCrossClick(card);
            });
        }
    });
});


