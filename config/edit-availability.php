<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté et est un tuteur
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tutor') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();
$error_message = '';
$success_message = '';

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

// Récupérer les disponibilités actuelles
$stmt = $db->prepare("
    SELECT id, day_of_week, 
           DATE_FORMAT(start_time, '%H:%i') as start_time,
           DATE_FORMAT(end_time, '%H:%i') as end_time
    FROM availability 
    WHERE user_id = ?
    ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
");
$stmt->execute([$user_id]);
$availabilities = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        // Supprimer toutes les anciennes disponibilités
        $stmt = $db->prepare("DELETE FROM availability WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Ajouter les nouvelles disponibilités
        if (isset($_POST['availability']) && is_array($_POST['availability'])) {
            $stmt = $db->prepare("
                INSERT INTO availability (user_id, day_of_week, start_time, end_time) 
                VALUES (?, ?, ?, ?)
            ");

            foreach ($_POST['availability'] as $day => $times) {
                if (!empty($times['start']) && !empty($times['end'])) {
                    // Valider les heures
                    $start = strtotime($times['start']);
                    $end = strtotime($times['end']);
                    
                    if ($start && $end && $start < $end) {
                        $stmt->execute([
                            $user_id,
                            $day,
                            $times['start'],
                            $times['end']
                        ]);
                    }
                }
            }
        }

        $db->commit();
        $success_message = "Vos disponibilités ont été mises à jour avec succès.";

        // Rafraîchir les disponibilités
        $stmt = $db->prepare("
            SELECT id, day_of_week, 
                   DATE_FORMAT(start_time, '%H:%i') as start_time,
                   DATE_FORMAT(end_time, '%H:%i') as end_time
            FROM availability 
            WHERE user_id = ?
            ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
        ");
        $stmt->execute([$user_id]);
        $availabilities = $stmt->fetchAll();

    } catch (Exception $e) {
        $db->rollBack();
        $error_message = "Une erreur est survenue lors de la mise à jour des disponibilités.";
    }
}

// Créer un tableau associatif pour un accès plus facile aux disponibilités
$availability_by_day = [];
foreach ($availabilities as $availability) {
    $availability_by_day[$availability['day_of_week']] = $availability;
}

// Définir les variables pour le header
$currentPage = 'edit-availability';
$pageTitle = 'Gérer mes disponibilités';

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Gérer mes disponibilités</h1>
                <a href="tutor-profile.php" class="text-blue-600 hover:underline">
                    Retour au profil
                </a>
            </div>

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

            <form method="POST" class="space-y-6">
                <p class="text-sm text-gray-600 mb-4">
                    Définissez vos plages horaires de disponibilité pour chaque jour :
                </p>

                <?php foreach ($days_fr as $day_en => $day_fr): ?>
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium mb-3"><?php echo $day_fr; ?></h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Début</label>
                                <input type="time" 
                                       name="availability[<?php echo $day_en; ?>][start]" 
                                       value="<?php echo isset($availability_by_day[$day_en]) ? $availability_by_day[$day_en]['start_time'] : ''; ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-1">Fin</label>
                                <input type="time" 
                                       name="availability[<?php echo $day_en; ?>][end]" 
                                       value="<?php echo isset($availability_by_day[$day_en]) ? $availability_by_day[$day_en]['end_time'] : ''; ?>"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="flex justify-end space-x-4 mt-6">
                    <a href="tutor-profile.php" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Enregistrer les disponibilités
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>