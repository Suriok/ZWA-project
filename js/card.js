document.addEventListener("DOMContentLoaded", function() {
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
        }, { once: true }); // Обработчик срабатывает один раз
    }

    // Обработчики кликов для кнопок
    buttonHeart.parentElement.addEventListener("click", () => showNextCard("right")); // Клик на сердце — уход вправо
    buttonCross.parentElement.addEventListener("click", () => showNextCard("left")); // Клик на крестик — уход влево
});
