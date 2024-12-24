document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire avant soumission
    document.querySelector('.registration-form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const subjects = document.querySelectorAll('.subject-select:checked');
        const days = document.querySelectorAll('input[name="days[]"]:checked');

        let errors = [];

        // Validation du mot de passe
        if (password !== confirmPassword) {
            errors.push('Les mots de passe ne correspondent pas.');
        }

        if (password.length < 8) {
            errors.push('Le mot de passe doit contenir au moins 8 caractères.');
        }

        // Validation des matières
        if (subjects.length === 0) {
            errors.push('Veuillez sélectionner au moins une matière.');
        }

        // Validation des disponibilités
        if (days.length === 0) {
            errors.push('Veuillez sélectionner au moins une disponibilité.');
        }

        // Si des erreurs sont présentes
        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join('\n'));
        }
    });

    // Gestion des disponibilités
    document.querySelectorAll('input[name="days[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const timeSlots = this.parentElement.nextElementSibling;
            timeSlots.style.display = this.checked ? 'flex' : 'none';
        });
    });

    // Initialiser l'affichage des créneaux horaires
    document.querySelectorAll('.time-slots').forEach(slot => {
        slot.style.display = 'none';
    });
});
