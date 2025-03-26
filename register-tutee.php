<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/validation/tutee-validation.php';

session_start();
$currentPage = 'register-tutee';
$pageTitle = 'Devenir Tutoré';

$db = Database::getInstance()->getConnection();
$departments = get_departments($db);

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
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Devenir Tutoré</h2>
        
        <?php if (!empty($error_messages)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php foreach ($error_messages as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="process-tutee-registration.php" enctype="multipart/form-data" class="space-y-6">
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

            <!-- Photo -->
            <div>
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                <input type="file" id="photo" name="photo" accept="image/*" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">Format accepté : JPG, PNG (max 5MB)</p>
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                <input type="tel" id="phone" name="phone" required pattern="[0-9]{10}"
                       value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Study Level -->
            <div>
                <label for="study_level" class="block text-sm font-medium text-gray-700 mb-1">Niveau d'études</label>
                <select id="study_level" name="study_level" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Sélectionnez votre niveau</option>
                    <option value="Bloc 1" <?php echo ($form_data['study_level'] ?? '') === 'Bloc 1' ? 'selected' : ''; ?>>Bloc 1</option>
                    <option value="Bloc 2 - poursuite d'études" <?php echo ($form_data['study_level'] ?? '') === "Bloc 2 - poursuite d'études" ? 'selected' : ''; ?>>Bloc 2 - poursuite d'études</option>
                    <option value="Bloc 2 - année diplômante" <?php echo ($form_data['study_level'] ?? '') === "Bloc 2 - année diplômante" ? 'selected' : ''; ?>>Bloc 2 - année diplômante</option>
                    <option value="Master" <?php echo ($form_data['study_level'] ?? '') === 'Master' ? 'selected' : ''; ?>>Master</option>
                </select>
            </div>

            <!-- Department -->
            <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Département</label>
                <select id="department_id" name="department_id" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Sélectionnez votre département</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo $department['id']; ?>" 
                                <?php echo ($form_data['department_id'] ?? '') == $department['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Section -->
            <div>
                <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                <input type="text" id="section" name="section" required 
                       value="<?php echo htmlspecialchars($form_data['section'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    S'inscrire comme tutoré
                </button>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
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

        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join('\n'));
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>