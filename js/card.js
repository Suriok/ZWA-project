// Card Animation start
document.addEventListener("DOMContentLoaded", function () {
    const swiper = document.getElementById("swiper"); // Контейнер для карточек
    const cards = Array.from(swiper.querySelectorAll(".card")); // Все карточки как массив
    let currentIndex = 0; // Индекс текущей отображаемой карточки

    // Получаем кнопки "сердце" и "крестик"
    const buttonHeart = document.querySelector(".button_foto_heart");
    const buttonCross = document.querySelector(".button_foto_cross");

    // Проверка наличия кнопок
    if (!buttonHeart || !buttonCross) {
        console.error("Кнопки не найдены!");
        return;
    }

    /**
     * Функция для инициализации начальных классов карточек
     * Устанавливает `.visible` для текущей карточки, `.next` для следующей и `.next-next` для второй по очереди
     */
    function initializeStack() {
        cards.forEach((card, index) => {
            card.classList.remove("visible", "next", "next-next"); // Сначала убираем все классы

            if (index === currentIndex) {
                card.classList.add("visible"); // Текущая карточка
            } else if (index === (currentIndex + 1) % cards.length) {
                card.classList.add("next"); // Следующая карточка
            } else if (index === (currentIndex + 2) % cards.length) {
                card.classList.add("next-next"); // Вторая по очереди
            }
        });
    }

    initializeStack(); // Первоначальная инициализация стека

    /**
     * Функция для анимации и циклического перемещения карточек
     * При клике карточка уходит, перемещается в конец и обновляется стек
     */
    function showNextCard(direction) {
        if (currentIndex >= cards.length) return; // Если все карточки закончились, выходим из функции

        const currentCard = cards[currentIndex]; // Текущая карточка, которую переместим
        currentCard.classList.add(direction === 'right' ? 'swipe-right' : 'swipe-left'); // Добавляем класс анимации

        currentCard.addEventListener("animationend", () => {
            currentCard.classList.remove("visible", "swipe-left", "swipe-right"); // Убираем классы анимации и видимости
            swiper.appendChild(currentCard); // Перемещаем карточку в конец контейнера

            // Обновляем индекс текущей карточки: переходим к следующей
            currentIndex = (currentIndex + 1) % cards.length; // Цикличный индекс для бесконечного стека

            initializeStack(); // Обновляем классы карточек для эффекта лесенки
        }, {once: true}); // Обработчик срабатывает один раз
    }

    // Обработчики кликов для кнопок
    buttonHeart.parentElement.addEventListener("click", () => showNextCard("right")); // Клик на сердце — уход вправо
    buttonCross.parentElement.addEventListener("click", () => showNextCard("left")); // Клик на крестик — уход влево
// Card Animation end

});

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
document.addEventListener("DOMContentLoaded", function () {
    const heartButton = document.querySelector(".button_foto_heart");

    if (heartButton) {
        heartButton.addEventListener("click", function () {
            // Найти видимую карточку
            const card = document.querySelector(".card.visible");
            if (card) {
                // Извлечь данные пользователя из карточки
                const userData = {
                    image: card.querySelector(".user_foto").src,
                    nameAndAge: card.querySelector(".name_and_age").textContent,
                    interests: card.querySelector(".inerests").textContent
                };

                // Сохранить данные карточки в localStorage
                let savedUsers = JSON.parse(localStorage.getItem("savedUsers")) || [];
                savedUsers.push(userData);
                localStorage.setItem("savedUsers", JSON.stringify(savedUsers));

                // Показать уведомление о сохранении
                const notification = document.createElement("div");
                notification.className = "save-notification";
                notification.textContent = "Пользователь сохранен на страницу people";
                document.body.appendChild(notification);

                // Удалить уведомление через 3 секунды
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
        });
    }
});



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


