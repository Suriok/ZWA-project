// AJAX pro stranku register.php
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.account_form');
    const submitButton = document.querySelector('.create_account_button');

    submitButton.addEventListener('click', function (e) {
        e.preventDefault(); // Предотвращаем стандартное поведение кнопки

        const formData = new FormData(form);

        fetch('register.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                // Убираем старые ошибки
                document.querySelectorAll('.error_message').forEach(el => el.textContent = '');
                document.querySelectorAll('.input_inf').forEach(el => el.classList.remove('error'));

                if (data.success) {
                    alert('Registration successful!');
                    form.reset(); // Очищаем форму
                } else {
                    // Обрабатываем ошибки
                    for (const [field, message] of Object.entries(data.errors)) {
                        const input = document.querySelector(`[name="${field}"]`);
                        const errorMessage = input?.closest('.form')?.querySelector('.error_message');

                        if (input) {
                            input.classList.add('error'); // Подсвечиваем поле
                        }
                        if (errorMessage) {
                            errorMessage.textContent = message; // Выводим сообщение
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
    });
});
