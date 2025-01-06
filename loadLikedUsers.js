// Funkce pro výpočet věku uživatele na základě data narození
function calculateAge(dateOfBirth) {
    const birthDate = new Date(dateOfBirth); // Převod data narození na objekt Date
    const today = new Date(); // Získání aktuálního data
    let age = today.getFullYear() - birthDate.getFullYear(); // Rozdíl mezi roky
    const m = today.getMonth() - birthDate.getMonth(); // Kontrola rozdílu měsíců
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--; // Pokud aktuální měsíc a den nejsou dosaženy, odečte 1 rok
    }
    return age; // Vrací vypočtený věk
}

// Funkce pro načtení lajknutých uživatelů pro konkrétní stránku
async function loadLikedUsers(page = 1) {
    try {
        // Odeslání GET požadavku na server s parametrem stránky
        const response = await fetch('people.php?action=get_liked&page=' + page);
        const data = await response.json(); // Získání odpovědi serveru jako JSON
        // data = { page, totalPages, likedList }

        const container = document.getElementById('likedUsersContainer'); // Kontejner pro uživatelské karty
        container.innerHTML = ''; // Vyprázdnění obsahu kontejneru

        if (!data.likedList || data.likedList.length === 0) {
            container.textContent = 'Zatím nikdo nebyl lajknut.'; // Zpráva pro prázdný seznam
            document.getElementById('paginationContainer').innerHTML = ''; // Vyprázdnění stránkování
            return;
        }

        // Vykreslení karet uživatelů
        const allUsers = window.users || []; // Všichni uživatelé dostupní z PHP
        data.likedList.forEach(likedEmail => {
            const user = allUsers.find(u => u.email === likedEmail); // Vyhledání uživatele podle emailu
            if (user) {
                const card = document.createElement('div'); // Vytvoření elementu pro kartu
                card.className = 'user-card'; // Přidání třídy
                card.innerHTML = `
                    <img class="user_foto"
                         src="data:${user.photo_mime};base64,${user.photo}"
                         alt="User">
                    <p class="name_and_age">${user.name}, ${calculateAge(user.date_birth)}</p>
                    <p class="inerests">${user.bio}</p>
                `; // Vyplnění obsahu karty
                container.appendChild(card); // Přidání karty do kontejneru
            }
        });

        // Vykreslení stránkování
        renderPagination(data.page, data.totalPages);
    } catch (e) {
        console.error("Chyba načtení:", e); // Zobrazení chyby v konzoli
        document.getElementById('likedUsersContainer').textContent = 'Došlo k chybě při načítání.'; // Chybová zpráva
    }
}

// Funkce pro vykreslení stránkování
function renderPagination(currentPage, totalPages) {
    const pagCont = document.getElementById('paginationContainer'); // Kontejner pro stránkování
    pagCont.innerHTML = ''; // Vyprázdnění kontejneru

    if (totalPages <= 1) return; // Pokud je pouze jedna stránka, stránkování není třeba

    // Přidání tlačítka "Předchozí"
    if (currentPage > 1) {
        const prevLink = document.createElement('a');
        prevLink.href = '#';
        prevLink.textContent = 'Předchozí';
        prevLink.addEventListener('click', (e) => {
            e.preventDefault();
            loadLikedUsers(currentPage - 1); // Načtení předchozí stránky
        });
        pagCont.appendChild(prevLink);
    }

    // Přidání čísel stránek
    for (let i = 1; i <= totalPages; i++) {
        const pageLink = document.createElement('a');
        pageLink.href = '#';
        pageLink.textContent = i;
        if (i === currentPage) {
            pageLink.classList.add('active'); // Zvýraznění aktuální stránky
        }
        pageLink.addEventListener('click', (e) => {
            e.preventDefault();
            loadLikedUsers(i); // Načtení vybrané stránky
        });
        pagCont.appendChild(pageLink);
    }

    // Přidání tlačítka "Další"
    if (currentPage < totalPages) {
        const nextLink = document.createElement('a');
        nextLink.href = '#';
        nextLink.textContent = 'Další';
        nextLink.addEventListener('click', (e) => {
            e.preventDefault();
            loadLikedUsers(currentPage + 1); // Načtení další stránky
        });
        pagCont.appendChild(nextLink);
    }
}

// Funkce pro lajknutí uživatele
async function likeUser(email) {
    try {
        const response = await fetch('people.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email }) // Odeslání emailu uživatele
        });
        const result = await response.json();
        if (result.success) {
            alert(result.message); // Zobrazení zprávy
            loadLikedUsers(); // Obnovení seznamu (stránka 1)
        } else {
            alert(result.message); // Chybová zpráva ze serveru
        }
    } catch (e) {
        console.error("Chyba při lajkování:", e); // Chyba odesílání
    }
}

// Po načtení stránky načteme lajknuté uživatele
document.addEventListener('DOMContentLoaded', () => {
    loadLikedUsers(1); // Načtení první stránky
});

