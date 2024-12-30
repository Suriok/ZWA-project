//pop up
document.addEventListener('DOMContentLoaded', function () {
    const isLoggedIn = window.isLoggedIn; // Получаем переменную из PHP

    if (!isLoggedIn) {
        const popup = document.getElementById('popup');
        const overlay = document.getElementById('popup-overlay');

        // Показываем popup и overlay
        popup.style.display = 'block';
        overlay.style.display = 'block';

        // Закрытие по клику на overlay
        overlay.addEventListener('click', () => {
            popup.style.display = 'none';
            overlay.style.display = 'none';
        });
    }
});


document.addEventListener("DOMContentLoaded", function () {
    const swiper = document.getElementById("swiper"); // Kontejner pro karty
    const cards = Array.from(swiper.querySelectorAll(".card")); // Všechny karty jako pole
    let currentIndex = 0; // Index aktuální karty

    const buttonHeart = document.querySelector(".button_foto_heart")?.parentElement; // Tlačítko "srdce"
    const buttonCross = document.querySelector(".button_foto_cross")?.parentElement; // Tlačítko "křížek"

    // Funkce pro aktualizaci viditelnosti karet
    function updateCards() {
        cards.forEach((card, index) => {
            if (index === currentIndex) {
                card.style.display = "block"; // Zobrazit aktuální kartu
                card.style.transform = "translateY(0px)"; // Aktuální karta na svém místě
                card.style.opacity = 1; // Karta je plně viditelná
                card.style.zIndex = 2; // Nastavení aktuální karty nad ostatními
            } else if (index === currentIndex + 1) {
                card.style.display = "block"; // Zobrazit další kartu
                card.style.transform = "translateY(-15px)"; // Další karta trochu výš
                card.style.opacity = 0.8; // Mírně průhledná karta
                card.style.zIndex = 1; // Další karta je pod aktuální
            } else {
                card.style.display = "none"; // Skrýt ostatní karty
            }
        });
    }

    // Funkce pro přesunutí na další kartu
    function showNextCard(direction) {
        if (currentIndex >= cards.length) {
            console.log("Všechny karty byly zobrazeny");
            return; // Pokud byly všechny karty zobrazeny, ukončíme
        }

        const currentCard = cards[currentIndex]; // Aktuální karta

        // Animace přesunutí karty na stranu
        currentCard.style.transition = "transform 0.5s ease, opacity 0.5s ease";
        if (direction === "right") {
            currentCard.style.transform = "translateX(100%)"; // Přesunout kartu doprava
        } else if (direction === "left") {
            currentCard.style.transform = "translateX(-100%)"; // Přesunout kartu doleva
        }
        currentCard.style.opacity = 0; // Nastavit neviditelnost karty

        // Obsluha události pro dokončení animace a přesunutí na další kartu
        currentCard.addEventListener(
            "transitionend",
            function () {
                currentCard.style.display = "none"; // Úplně skrýt kartu po animaci
                currentIndex++; // Přejdeme na další kartu

                // Aktualizace viditelnosti karet
                if (currentIndex < cards.length) {
                    updateCards(); // Aktualizujeme zobrazení karet
                } else {
                    console.log("Konec seznamu karet");
                }
            },
            { once: true } // Událost se spustí pouze jednou
        );
    }

    // Obsluha kliknutí na tlačítka "srdce" a "křížek"
    buttonHeart.addEventListener("click", function () {
        showNextCard("right"); // Kliknutí na "srdce" přesune kartu doprava
    });

    buttonCross.addEventListener("click", function () {
        showNextCard("left"); // Kliknutí na "křížek" přesune kartu doleva
    });

    // Inicializace karet po načtení stránky
    updateCards();
});



// Realizace Burger menu
document.addEventListener('DOMContentLoaded', () => {
    const profileImage = document.querySelector('.profil_image'); // Кнопка открытия меню
    const burgerMenu = document.getElementById('burgerMenu'); // Само меню

    if (profileImage && burgerMenu) { // Проверяем, существуют ли элементы на странице
        // Функция для переключения видимости меню
        profileImage.addEventListener('click', () => {
            burgerMenu.style.display = burgerMenu.style.display === 'flex' ? 'none' : 'flex';
        });

        // Закрытие меню при клике вне его области
        document.addEventListener('click', (event) => {
            if (!burgerMenu.contains(event.target) && event.target !== profileImage) {
                burgerMenu.style.display = 'none';
            }
        });
    }
});


//photo adder
    function previewPhoto(event) {
    const photoPreview = document.getElementById('photo_preview');
    const file = event.target.files[0];

    if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
    photoPreview.src = e.target.result;
    photoPreview.style.display = 'block';
};
    reader.readAsDataURL(file);
} else {
    photoPreview.src = "#";
    photoPreview.style.display = 'none';
}
}

// Sčitačka pro sloveso v bio
document.addEventListener("DOMContentLoaded", function () {
    const bio = document.getElementById("bio");
    const charCount = document.getElementById("charCount");

    bio.addEventListener("input", function () {
        const currentLength = bio.value.length;
        charCount.textContent = `${currentLength}/120`;
    });
});

// Different password
document.addEventListener("DOMContentLoaded", function () {
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");
    const passwordError = document.getElementById("passwordError");

    function validatePasswords() {
        if (password.value !== confirmPassword.value) {
            password.classList.add("error");
            confirmPassword.classList.add("error");
            passwordError.style.display = "block";
        } else {
            password.classList.remove("error");
            confirmPassword.classList.remove("error");
            passwordError.style.display = "none";
        }
    }

    password.addEventListener("input", validatePasswords);
    confirmPassword.addEventListener("input", validatePasswords);
});





document.addEventListener('DOMContentLoaded', function() {
    const likeButtons = document.querySelectorAll('.button_border');
    likeButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const email = this.getAttribute('data-user-id'); // Получаем email из атрибута
            const hashedEmail = await sha256(email); // Хэшируем email перед отправкой

            fetch('like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: hashedEmail }) // Передаём хэшированный email
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Пользователь добавлен в избранное!');
                    } else {
                        alert('Ошибка добавления пользователя: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Ошибка запроса:', error);
                    alert('Произошла ошибка при добавлении пользователя.');
                });
        });
    });
});

// Функция для хэширования email с использованием SHA-256
async function sha256(message) {
    const msgBuffer = new TextEncoder().encode(message);                   // Кодируем строку в массив байтов
    const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);  // Хэшируем данные
    const hashArray = Array.from(new Uint8Array(hashBuffer));             // Преобразуем буфер в массив байтов
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join(''); // Конвертируем в строку HEX
    return hashHex;
}




