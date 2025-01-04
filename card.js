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
    document.addEventListener('DOMContentLoaded', function () {
        // Собираем все карточки в массив
        const cards = document.querySelectorAll('#swiper .card');

        // Одна пара кнопок (за пределами цикла)
        const buttonHeart = document.getElementById('buttonHeart');
        const buttonCross = document.getElementById('buttonCross');

        // Индекс текущей карточки
        let currentIndex = 0;

        // Функция для обновления отображения карточек
        function updateCards() {
            cards.forEach((card, index) => {
                if (index === currentIndex) {
                    // Показываем текущую карту
                    card.style.display = 'block';
                    card.style.transform = 'translateY(0px)';
                    card.style.opacity = '1';
                    card.style.zIndex = '2';
                } else if (index === currentIndex + 1) {
                    // Следующую карту слегка приподнимаем
                    card.style.display = 'block';
                    card.style.transform = 'translateY(-15px)';
                    card.style.opacity = '0.8';
                    card.style.zIndex = '1';
                } else {
                    // Остальные скрываем
                    card.style.display = 'none';
                }
            });
        }

        // Функция «свайпа» (уходит текущая карточка влево/вправо)
        function showNextCard(direction) {
            if (currentIndex >= cards.length) {
                console.log('Все карточки уже просмотрены');
                return;
            }

            // Текущая карточка
            const currentCard = cards[currentIndex];

            // Добавляем CSS-переход для анимации
            currentCard.style.transition = 'transform 0.5s ease, opacity 0.5s ease';

            // Определяем направление анимации
            if (direction === 'right') {
                currentCard.style.transform = 'translateX(100%)';
            } else {
                currentCard.style.transform = 'translateX(-100%)';
            }
            currentCard.style.opacity = '0';

            // Когда анимация закончится, прячем эту карточку
            currentCard.addEventListener('transitionend', function handler() {
                // Удаляем обработчик сразу (чтоб не вызывался несколько раз)
                currentCard.removeEventListener('transitionend', handler);

                // Скрываем карточку полностью
                currentCard.style.display = 'none';
                // Сбрасываем transition, чтобы при возврате на экран карта не анимировалась резко
                currentCard.style.transition = '';

                // Переходим к следующей карточке
                currentIndex++;

                if (currentIndex < cards.length) {
                    updateCards();
                } else {
                    console.log('Конец списка карточек');
                }
            });
        }

        // Обработчик клика на сердечко
        buttonHeart.addEventListener('click', function () {
            // 1. «Лайкаем» текущую карточку, если она есть
            if (currentIndex < cards.length) {
                const currentCard = cards[currentIndex];
                const likedEmail = currentCard.getAttribute('data-email');
                sendLike(likedEmail);
            }
            // 2. Убираем карточку анимацией (направление вправо)
            showNextCard('right');
        });

        // Обработчик клика на крестик
        buttonCross.addEventListener('click', function () {
            // Можно, если нужно, что-то делать при дизлайке
            // ...
            // И убираем карточку анимацией (направление влево)
            showNextCard('left');
        });

        // Функция отправки «лайка» на сервер
        async function sendLike(email) {
            try {
                const response = await fetch('people.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({email: email})
                });
                const data = await response.json();
                if (data.success) {
                    console.log('Лайк отправлен, email:', email);
                } else {
                    console.log('Ошибка лайка:', data.message);
                }
            } catch (error) {
                console.error('Ошибка при отправке лайка:', error);
            }
        }

        // Инициализация отображения (показать первую карту и вторую)
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


document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.button_border[data-email]');
    buttons.forEach(button => {
        button.addEventListener('click', async function () {
            const emailToLike = this.getAttribute('data-email');

            if (!emailToLike) {
                alert('Email пользователя отсутствует!');
                return;
            }

            try {
                const response = await fetch('people.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: emailToLike })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Пользователь добавлен в список лайков!');
                } else {
                    alert(data.message || 'Произошла ошибка на сервере.');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Не удалось связаться с сервером.');
            }
        });
    });
});
