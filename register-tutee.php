<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
$currentPage = 'register-tutee';
$pageTitle = 'Devenir Tutoré';

$db = Database::getInstance()->getConnection();
$departments = get_departments($db);
$subjects = get_subjects($db);
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'process-tutee-registration.php';
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Devenir Tutoré</h2>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- Nom et Prénom -->
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Nom :</label>
                    <input type="text" id="lastname" name="lastname" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">Prénom :</label>
                    <input type="text" id="firstname" name="firstname" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Pseudo :</label>
                <input type="text" id="username" name="username" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Mot de passe -->
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe :</label>
                    <input type="password" id="password" name="password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Répéter mot de passe :</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Photo -->
            <div>
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">Photo :</label>
                <input type="file" id="photo" name="photo" accept="image/*" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Téléphone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Numéro de téléphone :</label>
                <input type="tel" id="phone" name="phone" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Niveau d'études -->
            <div>
                <label for="study_level" class="block text-sm font-medium text-gray-700 mb-1">Niveau d'études :</label>
                <select id="study_level" name="study_level" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Sélectionnez votre niveau</option>
                    <option value="Bloc 1">Bloc 1</option>
                    <option value="Bloc 2 - poursuite d'études">Bloc 2 - poursuite d'études</option>
                    <option value="Bloc 2 - année diplômante">Bloc 2 - année diplômante</option>
                    <option value="Master">Master</option>
                </select>
            </div>

            <!-- Département -->
            <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Département :</label>
                <select id="department_id" name="department_id" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Sélectionnez votre département</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo $department['id']; ?>">
                            <?php echo htmlspecialchars($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Section -->
            <div>
                <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section :</label>
                <input type="text" id="section" name="section" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Matières -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Matières recherchées :</label>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($subjects as $subject): ?>
                        <div class="flex items-center">
                            <input type="checkbox" name="subjects[]" value="<?php echo $subject['id']; ?>" 
                                   id="subject_<?php echo $subject['id']; ?>" class="h-4 w-4 text-blue-600">
                            <label for="subject_<?php echo $subject['id']; ?>" class="ml-2 text-sm text-gray-700">
                                <?php echo htmlspecialchars($subject['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Disponibilités -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Disponibilités :</label>
                <div class="space-y-4">
                    <?php
                    $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                    foreach ($days as $index => $day):
                    ?>
                        <div class="border border-gray-200 rounded-md p-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="days[]" value="<?php echo $index + 1; ?>" 
                                       class="h-4 w-4 text-blue-600">
                                <span class="ml-2 font-medium"><?php echo $day; ?></span>
                            </label>
                            <div class="time-slots mt-3 grid grid-cols-2 gap-4" style="display: none;">
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Début :</label>
                                    <select name="start_time_<?php echo $index + 1; ?>" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <?php
                                        for ($hour = 7; $hour <= 23; $hour++) {
                                            for ($min = 0; $min < 60; $min += 30) {
                                                $time = sprintf("%02d:%02d", $hour, $min);
                                                echo "<option value=\"{$time}\">{$time}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Fin :</label>
                                    <select name="end_time_<?php echo $index + 1; ?>" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <?php
                                        for ($hour = 7; $hour <= 23; $hour++) {
                                            for ($min = 0; $min < 60; $min += 30) {
                                                $time = sprintf("%02d:%02d", $hour, $min);
                                                echo "<option value=\"{$time}\">{$time}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Bouton de soumission -->
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
    // Gestion des disponibilités
    document.querySelectorAll('input[name="days[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const timeSlots = this.closest('.border').querySelector('.time-slots');
            timeSlots.style.display = this.checked ? 'grid' : 'none';
        });
    });

    // Validation du formulaire
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const subjects = document.querySelectorAll('input[name="subjects[]"]:checked');
        const days = document.querySelectorAll('input[name="days[]"]:checked');

        let errors = [];

        if (password !== confirmPassword) {
            errors.push('Les mots de passe ne correspondent pas.');
        }

        if (password.length < 8) {
            errors.push('Le mot de passe doit contenir au moins 8 caractères.');
        }

        if (subjects.length === 0) {
            errors.push('Veuillez sélectionner au moins une matière.');
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
</body>
</html>
