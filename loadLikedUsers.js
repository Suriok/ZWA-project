// Функция вычисления возраста (JS)
function calculateAge(dateOfBirth) {
    const birthDate = new Date(dateOfBirth);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m===0 && today.getDate()<birthDate.getDate())) {
        age--;
    }
    return age;
}

// Загрузка лайкнутых с конкретной страницы
async function loadLikedUsers(page=1) {
    try {
        // GET запрос c ?action=get_liked&page=...
        const response = await fetch('people.php?action=get_liked&page='+page);
        const data = await response.json();
        // data = { page, totalPages, likedList }

        const container = document.getElementById('likedUsersContainer');
        container.innerHTML = '';

        if (!data.likedList || data.likedList.length===0) {
            container.textContent = 'Пока никто не лайкнут.';
            document.getElementById('paginationContainer').innerHTML = '';
            return;
        }

        // Рисуем карточки
        const allUsers = window.users || [];
        data.likedList.forEach(likedEmail => {
            const user = allUsers.find(u => u.email === likedEmail);
            if (user) {
                const card = document.createElement('div');
                card.className = 'user-card';
                card.innerHTML = `
                    <img class="user_foto"
                         src="data:${user.photo_mime};base64,${user.photo}"
                         alt="User">
                    <p class="name_and_age">${user.name}, ${calculateAge(user.date_birth)}</p>
                    <p class="inerests">${user.bio}</p>
                `;
                container.appendChild(card);
            }
        });

        // Рендерим пагинацию
        renderPagination(data.page, data.totalPages);
    } catch(e) {
        console.error("Ошибка загрузки:", e);
        document.getElementById('likedUsersContainer').textContent = 'Ошибка при загрузке.';
    }
}

// Отрисовка пагинации (Prev, номера, Next)
function renderPagination(currentPage, totalPages) {
    const pagCont = document.getElementById('paginationContainer');
    pagCont.innerHTML = '';

    if (totalPages <= 1) return;

    // Prev
    if (currentPage > 1) {
        const prevLink = document.createElement('a');
        prevLink.href = '#';
        prevLink.textContent='Previous';
        prevLink.addEventListener('click', (e)=>{
            e.preventDefault();
            loadLikedUsers(currentPage - 1);
        });
        pagCont.appendChild(prevLink);
    }

    // 1..N
    for (let i=1; i<=totalPages; i++) {
        const pageLink = document.createElement('a');
        pageLink.href = '#';
        pageLink.textContent = i;
        if (i===currentPage) {
            pageLink.classList.add('active');
        }
        pageLink.addEventListener('click', (e)=>{
            e.preventDefault();
            loadLikedUsers(i);
        });
        pagCont.appendChild(pageLink);
    }

    // Next
    if (currentPage < totalPages) {
        const nextLink = document.createElement('a');
        nextLink.href='#';
        nextLink.textContent='Next';
        nextLink.addEventListener('click', (e)=>{
            e.preventDefault();
            loadLikedUsers(currentPage+1);
        });
        pagCont.appendChild(nextLink);
    }
}

// Функция лайка (POST)
async function likeUser(email) {
    try {
        const response = await fetch('people.php', {
            method:'POST',
            headers: { 'Content-Type':'application/json' },
            body: JSON.stringify({ email })
        });
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            loadLikedUsers(); // обновляем (вернёмся на page=1)
        } else {
            alert(result.message);
        }
    } catch(e) {
        console.error("Ошибка лайка:", e);
    }
}

// При загрузке
document.addEventListener('DOMContentLoaded', () => {
    loadLikedUsers(1);
});
