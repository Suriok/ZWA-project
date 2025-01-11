
document.addEventListener('DOMContentLoaded', function () {
    // Najděte všechny formuláře s třídou 'delete-form'
    const deleteForms = document.querySelectorAll('.delete-form');

    // Pro každý formulář přidejte posluchač události 'submit'
    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Zabraňte výchozímu odeslání formuláře
            const formData = new FormData(this); // Získejte data z formuláře
            const deleteEmail = formData.get('delete_email'); // Získejte email uživatele k odstranění

            // Zobrazení potvrzovacího dialogu
            if (confirm('Opravdu chcete odstranit tohoto uživatele?')) {
                // Odeslání formuláře pomocí Fetch API
                fetch('admin.php', {
                    method: 'POST', // Metoda odeslání
                    body: formData // Data z formuláře
                })
                    .then(response => response.text()) // Zpracování odpovědi jako text
                    .then(data => {
                        // Obnovte stránku, aby se změny projevily
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('Chyba:', error); // Záznam chyby do konzole
                        alert('Došlo k chybě při odstraňování uživatele.'); // Zobrazení chybové zprávy
                    });
            }
        });
    });
});
