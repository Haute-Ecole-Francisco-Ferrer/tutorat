<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/validation/tutor-validation.php';

session_start();
$currentPage = 'register-tutor';
$pageTitle = 'Devenir Tuteur';

$db = Database::getInstance()->getConnection();
$departments = get_departments($db);
$subjects = get_subjects($db);

// Get any error messages from session
$error_messages = $_SESSION['registration_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];

// Clear session data
unset($_SESSION['registration_errors']);
unset($_SESSION['form_data']);

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Devenir Tuteur</h2>
        
        <?php if (!empty($error_messages)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php foreach ($error_messages as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="process-tutor-registration.php" enctype="multipart/form-data" class="space-y-6">
            <!-- Personal Information -->
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                    <input type="text" id="firstname" name="firstname" 
                           value="<?php echo htmlspecialchars($form_data['firstname'] ?? ''); ?>"
                           required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" id="lastname" name="lastname" 
                           value="<?php echo htmlspecialchars($form_data['lastname'] ?? ''); ?>"
                           required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Account Information -->
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Pseudo</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>"
                           required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                           required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Password -->
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <input type="password" id="password" name="password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Contact and Academic Information -->
            <?php require 'includes/components/registration-fields.php'; ?>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    S'inscrire comme tuteur
                </button>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Subject selection limit
    const subjectCheckboxes = document.querySelectorAll('.subject-select');
    const maxSubjects = 5;

    function updateSubjectCheckboxes() {
        const checkedCount = document.querySelectorAll('.subject-select:checked').length;
        subjectCheckboxes.forEach(checkbox => {
            if (!checkbox.checked && checkedCount >= maxSubjects) {
                checkbox.disabled = true;
            } else {
                checkbox.disabled = false;
            }
        });
    }

    subjectCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSubjectCheckboxes);
    });

    // Availability time slots
    document.querySelectorAll('input[name="days[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const timeSlots = this.closest('.border').querySelector('.time-slots');
            timeSlots.style.display = this.checked ? 'grid' : 'none';
        });
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const subjects = document.querySelectorAll('.subject-select:checked');
        const days = document.querySelectorAll('input[name="days[]"]:checked');
        const email = document.getElementById('email').value;

        let errors = [];

        if (!email || !email.includes('@')) {
            errors.push('Veuillez entrer une adresse email valide.');
        }

        if (password !== confirmPassword) {
            errors.push('Les mots de passe ne correspondent pas.');
        }

        if (password.length < 8) {
            errors.push('Le mot de passe doit contenir au moins 8 caractères.');
        }

        if (subjects.length === 0) {
            errors.push('Veuillez sélectionner au moins une matière.');
        }

        if (subjects.length > maxSubjects) {
            errors.push('Vous ne pouvez sélectionner que ' + maxSubjects + ' matières maximum.');
        }

        if (days.length === 0) {
            errors.push('Veuillez sélectionner au moins une disponibilité.');
        }

        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join('\n'));
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>