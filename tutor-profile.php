<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tutor') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

// Requête pour obtenir les informations du tuteur et son département
$query = "SELECT u.*, t.id as tutor_id, t.max_tutees, t.current_tutees, d.name as department_name 
          FROM users u 
          JOIN tutors t ON u.id = t.user_id 
          JOIN departments d ON u.department_id = d.id 
          WHERE u.id = ?";

try {
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tutor) {
        header('Location: index.php');
        exit;
    }

    // Obtenir les matières du tuteur
    $query = "SELECT s.name 
              FROM subjects s 
              JOIN tutor_subjects ts ON s.id = ts.subject_id 
              WHERE ts.tutor_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$tutor['tutor_id']]);
    $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Obtenir les disponibilités du tuteur
    $query = "SELECT day_of_week, DATE_FORMAT(start_time, '%H:%i') as start_time, 
              DATE_FORMAT(end_time, '%H:%i') as end_time 
              FROM availability 
              WHERE user_id = ? 
              ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $availabilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

// Définir les variables pour le header
$currentPage = 'tutor-profile';
$pageTitle = 'Mon Profil Tuteur';

// Traduction des jours en français
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
        <!-- En-tête du profil -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-24 h-24 rounded-full overflow-hidden mr-6">
                            <?php if ($tutor['photo']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($tutor['photo']); ?>" 
                                     alt="Photo de profil" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-3xl text-gray-500">
                                        <?php echo strtoupper(substr($tutor['firstname'], 0, 1)) . strtoupper(substr($tutor['lastname'], 0, 1)); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">
                                <?php echo htmlspecialchars($tutor['firstname'] . ' ' . $tutor['lastname']); ?>
                            </h1>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($tutor['department_name']); ?> - 
                                <?php echo htmlspecialchars($tutor['section']); ?>
                            </p>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($tutor['study_level']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">
                            Tutorés actuels: <?php echo $tutor['current_tutees']; ?>/<?php echo $tutor['max_tutees']; ?>
                        </p>
                        <a href="edit-profile.php" 
                           class="inline-block mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                            Modifier mon profil
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matières -->
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Matières enseignées</h2>
                <a href="edit-subjects.php" class="text-blue-500 hover:underline text-sm">
                    Gérer mes matières
                </a>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($subjects as $subject): ?>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded">
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

        <!-- Disponibilités -->
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Disponibilités</h2>
                <a href="edit-availability.php" class="text-blue-500 hover:underline text-sm">
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