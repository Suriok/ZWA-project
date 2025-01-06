document.addEventListener('DOMContentLoaded', function () {
    const deleteForms = document.querySelectorAll('.delete-form');

    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const deleteEmail = formData.get('delete_email');

            if (confirm('Are you sure you want to delete this user?')) {
                fetch('admin.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(data => {
                        // Reload the page to reflect changes
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the user.');
                    });
            }
        });
    });
});
