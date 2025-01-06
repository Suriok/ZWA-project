// pop up
document.addEventListener('DOMContentLoaded', function () {
    const isLoggedIn = window.isLoggedIn; // Получаем переменную из PHP

    if (!isLoggedIn) {
        const popup = document.getElementById('popup');
        const overlay = document.getElementById('popup-overlay');
        const loginButton = document.querySelector('.login-button');
        const registerButton = document.querySelector('.register-button');

        // Показываем popup и overlay
        popup.style.display = 'block';
        overlay.style.display = 'block';

        // Добавляем обработчик для кнопки "Log In"
        if (loginButton) {
            loginButton.addEventListener('click', () => {
                window.location.href = 'log_in.php';
            });
        }

        // Добавляем обработчик для кнопки "Register"
        if (registerButton) {
            registerButton.addEventListener('click', () => {
                window.location.href = 'register.php';
            });
        }
    }
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


document.addEventListener("DOMContentLoaded", function () {
    const swiper = document.getElementById("swiper"); // Контейнер для карточек
    const cards = Array.from(swiper.querySelectorAll(".card")); // Все карточки как массив
    let currentIndex = 0; // Индекс текущей карточки

    // Функция для обновления видимости карточек
    function updateCards() {
        cards.forEach((card, index) => {
            if (index === currentIndex) {
                card.style.display = "block"; // Показать текущую карточку
                card.style.transform = "translateY(0px)"; // Текущая карточка на месте
                card.style.opacity = 1; // Карточка полностью видима
                card.style.zIndex = 2; // Текущая карточка над другими
            } else if (index === currentIndex + 1) {
                card.style.display = "block"; // Показать следующую карточку
                card.style.transform = "translateY(-15px)"; // Следующая карточка немного выше
                card.style.opacity = 0.8; // Карточка слегка прозрачна
                card.style.zIndex = 1; // Следующая карточка под текущей
            } else {
                card.style.display = "none"; // Скрыть остальные карточки
            }
        });
    }

    // Функция для отображения следующей карточки с анимацией
    function showNextCard(direction, card, callback) {
        // Анимация перемещения карточки
        card.style.transition = "transform 0.5s ease, opacity 0.5s ease";
        if (direction === "right") {
            card.style.transform = "translateX(100%)"; // Переместить вправо
        } else if (direction === "left") {
            card.style.transform = "translateX(-100%)"; // Переместить влево
        }
        card.style.opacity = 0; // Сделать карточку прозрачной

        // Обработчик завершения анимации
        card.addEventListener(
            "transitionend",
            function () {
                card.style.display = "none"; // Полностью скрыть карточку после анимации

                if (direction === "left") {
                    // Перемещаем карточку в конец списка
                    cards.push(cards.splice(currentIndex, 1)[0]);
                } else {
                    currentIndex++; // Переходим к следующей карточке
                }

                // Обновление видимости карточек
                if (currentIndex < cards.length) {
                    updateCards(); // Обновляем отображение карточек
                } else {
                    console.log("Кончился список карточек");
                }

                // Вызов обратного вызова, если есть
                if (typeof callback === "function") {
                    callback();
                }
            },
            { once: true } // Обработчик срабатывает только один раз
        );
    }

    // Обработчик для кнопки "сердце"
    async function handleHeartClick(button, card) {
        const emailToLike = button.getAttribute("data-email");

        if (!emailToLike) {
            alert("Email пользователя отсутствует!");
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
                console.log("Пользователь добавлен в список лайков!");
            } else {
                console.warn(data.message || "Произошла ошибка на сервере.");
            }
        } catch (error) {
            console.error("Ошибка при отправке данных:", error);
            alert("Не удалось связаться с сервером.");
        } finally {
            // Анимация карточки после попытки отправки данных
            showNextCard("right", card);
        }
    }

    // Обработчик для кнопки "крестик"
    function handleCrossClick(card) {
        showNextCard("left", card);
    }

    // Инициализация карточек после загрузки страницы
    updateCards();

    // Добавление обработчиков событий для каждой карточки
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


document.addEventListener('DOMContentLoaded', function () {
    const bioForm = document.getElementById('bioForm');
    const bioTextarea = document.getElementById('bio');
    const bioMessage = document.getElementById('bioMessage');

    if (!bioForm) {
        console.error("Форма 'bioForm' не найдена.");
        return;
    }

    bioForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Предотвращаем стандартную отправку формы

        const bio = bioTextarea.value.trim();
        console.log("Отправка био:", bio); // Отладка

        // Проверка длины био (например, не более 500 символов)
        if (bio.length > 500) {
            bioMessage.textContent = 'Био не должно превышать 500 символов.';
            bioMessage.style.color = 'red';
            console.log("Био превышает допустимую длину."); // Отладка
            return;
        }

        // Отправка AJAX-запроса для обновления био
        fetch('account.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'action': 'update_bio',
                'bio': bio
            })
        })
            .then(response => response.json())
            .then(data => {
                console.log("Ответ сервера:", data); // Отладка
                if (data.success) {
                    bioMessage.textContent = data.message;
                    bioMessage.style.color = 'green';
                    // Обновляем textarea с экранированным био
                    bioTextarea.value = data.bio;
                } else {
                    bioMessage.textContent = data.message;
                    bioMessage.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                bioMessage.textContent = 'Произошла ошибка при обновлении био.';
                bioMessage.style.color = 'red';
            });
    });
});
