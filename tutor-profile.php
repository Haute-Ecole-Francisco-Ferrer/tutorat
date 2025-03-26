<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/validation/profile-validation.php';
require_once 'includes/components/profile-form.php';
require_once 'includes/utils/department-colors.php';

// Verify user is logged in and is a tutor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();
$error_message = "";
$success_message = "";

// Get user data
try {
    $stmt = $db->prepare("
        SELECT u.*, t.id as tutor_id, t.current_tutees, d.name as department_name 
        FROM users u 
        JOIN tutors t ON u.id = t.user_id 
        JOIN departments d ON u.department_id = d.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: index.php');
        exit;
    }

    // Get tutor's subjects
    $stmt = $db->prepare("
        SELECT s.name 
        FROM subjects s 
        JOIN tutor_subjects ts ON s.id = ts.subject_id 
        WHERE ts.tutor_id = ?
    ");
    $stmt->execute([$user['tutor_id']]);
    $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get tutor's availabilities
    $stmt = $db->prepare("
        SELECT day_of_week, DATE_FORMAT(start_time, '%H:%i') as start_time, 
               DATE_FORMAT(end_time, '%H:%i') as end_time 
        FROM availability 
        WHERE user_id = ? 
        ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
    ");
    $stmt->execute([$user_id]);
    $availabilities = $stmt->fetchAll();

    $departments = get_departments($db);

} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

$currentPage = 'tutor-profile';
$pageTitle = 'Mon Profil Tuteur';

// Translation array for days
$days_fr = [
    'Monday' => 'Lundi',
    'Tuesday' => 'Mardi',
    'Wednesday' => 'Mercredi',
    'Thursday' => 'Jeudi',
    'Friday' => 'Vendredi',
    'Saturday' => 'Samedi',
    'Sunday' => 'Dimanche'
];

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-24 h-24 rounded-full overflow-hidden mr-6">
                            <?php if ($user['photo']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                                     alt="Photo de profil" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-3xl text-gray-500">
                                        <?php echo strtoupper(substr($user['firstname'], 0, 1)) . strtoupper(substr($user['lastname'], 0, 1)); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">
                                <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                            </h1>
                            <p style="color: <?php echo getDepartmentColor($user['department_id']); ?>;">
                                <?php echo htmlspecialchars($user['department_name']); ?> - 
                                <?php echo htmlspecialchars($user['section']); ?>
                            </p>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($user['study_level']); ?>
                            </p>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 mb-2">
                            Tutorés actuels: <?php echo $user['current_tutees']; ?>/4
                        </p>
                        <a href="edit-profile.php" 
                           class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                            Modifier mon profil
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subjects -->
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6 border-t-4 <?php echo getDepartmentBorderClass($user['department_id']); ?>">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Matières enseignées</h2>
                <a href="edit-subjects.php" class="text-blue-500 hover:underline text-sm">
                    Gérer mes matières
                </a>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($subjects as $subject): ?>
                    <span class="px-3 py-1 rounded" 
                          style="background-color: <?php echo getDepartmentColor($user['department_id']); ?>20; color: <?php echo getDepartmentColor($user['department_id']); ?>;">
                        <?php echo htmlspecialchars($subject); ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <?php if (empty($subjects)): ?>
                <p class="text-gray-600 text-center py-4">
                    Aucune matière renseignée
                </p>
            <?php endif; ?>
        </div>

        <!-- Availabilities -->
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6 border-t-4 <?php echo getDepartmentBorderClass($user['department_id']); ?>">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Disponibilités</h2>
                <a href="edit-availability.php" class="hover:underline text-sm" style="color: <?php echo getDepartmentColor($user['department_id']); ?>;">
                    Gérer mes disponibilités
                </a>
            </div>
            <div class="grid gap-4">
                <?php foreach ($availabilities as $availability): ?>
                    <div class="flex justify-between items-center border-b pb-2">
                        <span class="font-medium">
                            <?php echo $days_fr[$availability['day_of_week']]; ?>
                        </span>
                        <span class="text-gray-600">
                            <?php echo $availability['start_time']; ?> - <?php echo $availability['end_time']; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (empty($availabilities)): ?>
                <p class="text-gray-600 text-center py-4">
                    Aucune disponibilité renseignée
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
