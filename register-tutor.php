<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/validation/registration-validation.php';

session_start();
$currentPage = 'register-tutor';
$pageTitle = 'Devenir Tuteur';

$db = Database::getInstance()->getConnection();
$departments = get_departments($db);
$subjects = get_subjects($db);
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate registration data
        $data = validateRegistrationData($_POST, $_FILES);
        
        // Check if username or email already exists
        checkExistingUser($db, $data['username'], $data['email']);

        $db->beginTransaction();

        // Insert user
        $stmt = $db->prepare("
            INSERT INTO users (
                firstname, lastname, username, email, password, 
                photo, phone, study_level, department_id, section, user_type
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'tutor')
        ");
        
        $stmt->execute([
            $data['firstname'],
            $data['lastname'],
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['photo'] ?? null,
            $data['phone'],
            $data['study_level'],
            $data['department_id'],
            $data['section']
        ]);
        
        $user_id = $db->lastInsertId();

        // Create tutor record
        $stmt = $db->prepare("INSERT INTO tutors (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $tutor_id = $db->lastInsertId();

        // Add subjects
        if (isset($_POST["subjects"]) && is_array($_POST["subjects"])) {
            $stmt = $db->prepare("INSERT INTO tutor_subjects (tutor_id, subject_id) VALUES (?, ?)");
            foreach ($_POST["subjects"] as $subject_id) {
                $stmt->execute([$tutor_id, $subject_id]);
            }
        }

        // Add availabilities
        if (isset($_POST["days"]) && is_array($_POST["days"])) {
            $stmt = $db->prepare("
                INSERT INTO availability (user_id, day_of_week, start_time, end_time) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($_POST["days"] as $day) {
                $start_time = $_POST["start_time_" . $day] ?? null;
                $end_time = $_POST["end_time_" . $day] ?? null;
                
                if ($start_time && $end_time) {
                    $days_map = [
                        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
                        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
                    ];
                    
                    $stmt->execute([
                        $user_id,
                        $days_map[$day],
                        $start_time,
                        $end_time
                    ]);
                }
            }
        }

        $db->commit();
        $success_message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
        
        header("Location: login.php?registered=1");
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        $error_message = $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Devenir Tuteur</h2>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- Personal Information -->
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                    <input type="text" id="firstname" name="firstname" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" id="lastname" name="lastname" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Account Information -->
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Pseudo</label>
                    <input type="text" id="username" name="username" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" required 
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