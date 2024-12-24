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

// Récupérer l'ID du tuteur
$stmt = $db->prepare("SELECT id FROM tutors WHERE user_id = ?");
$stmt->execute([$user_id]);
$tutor = $stmt->fetch();
$tutor_id = $tutor['id'];

// Récupérer toutes les matières disponibles
$all_subjects = get_subjects($db);

// Récupérer les matières actuelles du tuteur
$stmt = $db->prepare("
    SELECT s.id, s.name, CASE WHEN ts.tutor_id IS NOT NULL THEN 1 ELSE 0 END as is_selected
    FROM subjects s
    LEFT JOIN tutor_subjects ts ON s.id = ts.subject_id AND ts.tutor_id = ?
    ORDER BY s.name
");
$stmt->execute([$tutor_id]);
$subjects = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Commencer une transaction
        $db->beginTransaction();

        // Supprimer toutes les matières actuelles du tuteur
        $stmt = $db->prepare("DELETE FROM tutor_subjects WHERE tutor_id = ?");
        $stmt->execute([$tutor_id]);

        // Ajouter les nouvelles matières sélectionnées
        if (isset($_POST['subjects']) && is_array($_POST['subjects'])) {
            $stmt = $db->prepare("INSERT INTO tutor_subjects (tutor_id, subject_id) VALUES (?, ?)");
            foreach ($_POST['subjects'] as $subject_id) {
                $stmt->execute([$tutor_id, $subject_id]);
            }
        }

        $db->commit();
        $success_message = "Vos matières ont été mises à jour avec succès.";

        // Rafraîchir la liste des matières
        $stmt = $db->prepare("
            SELECT s.id, s.name, CASE WHEN ts.tutor_id IS NOT NULL THEN 1 ELSE 0 END as is_selected
            FROM subjects s
            LEFT JOIN tutor_subjects ts ON s.id = ts.subject_id AND ts.tutor_id = ?
            ORDER BY s.name
        ");
        $stmt->execute([$tutor_id]);
        $subjects = $stmt->fetchAll();

    } catch (Exception $e) {
        $db->rollBack();
        $error_message = "Une erreur est survenue lors de la mise à jour des matières.";
    }
}

// Définir les variables pour le header
$currentPage = 'edit-subjects';
$pageTitle = 'Gérer mes matières';

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Gérer mes matières</h1>
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
                <div class="grid grid-cols-1 gap-4">
                    <p class="text-sm text-gray-600 mb-4">
                        Sélectionnez les matières que vous souhaitez enseigner :
                    </p>
                    
                    <?php foreach ($subjects as $subject): ?>
                        <label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" 
                                   name="subjects[]" 
                                   value="<?php echo $subject['id']; ?>" 
                                   <?php echo $subject['is_selected'] ? 'checked' : ''; ?>
                                   class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-3 text-gray-700">
                                <?php echo htmlspecialchars($subject['name']); ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <a href="tutor-profile.php" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>