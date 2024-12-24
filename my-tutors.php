<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Vérifier si l'utilisateur est connecté et est un tutoré
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutee') {
    header('Location: login.php');
    exit();
}

$currentPage = 'my-tutors';
$pageTitle = 'Mes Tuteurs';

$db = Database::getInstance()->getConnection();
$error_message = "";
$success_message = "";

// Traitement de l'annulation d'une demande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['relationship_id'])) {
    $relationship_id = (int)$_POST['relationship_id'];
    
    try {
        $stmt = $db->prepare("
            UPDATE tutoring_relationships 
            SET status = 'cancelled', updated_at = NOW() 
            WHERE id = ? AND tutee_id = ? AND status = 'pending'
        ");
        $stmt->execute([$relationship_id, $_SESSION['role_id']]);
        $success_message = "La demande a été annulée.";
    } catch (PDOException $e) {
        $error_message = "Une erreur est survenue. Veuillez réessayer.";
    }
}

// Récupérer les demandes en attente
$stmt = $db->prepare("
    SELECT 
        tr.id as relationship_id,
        tr.message,
        tr.created_at,
        tr.status,
        u.username,
        u.photo,
        s.name as subject_name
    FROM tutoring_relationships tr
    JOIN users u ON tr.tutor_id = u.id
    JOIN subjects s ON tr.subject_id = s.id
    WHERE tr.tutee_id = ? AND tr.status = 'pending'
    ORDER BY tr.created_at DESC
");
$stmt->execute([$_SESSION['role_id']]);
$pending_requests = $stmt->fetchAll();

// Récupérer les tuteurs actuels
$stmt = $db->prepare("
    SELECT 
        tr.id as relationship_id,
        tr.created_at as started_at,
        u.username,
        u.photo,
        s.name as subject_name
    FROM tutoring_relationships tr
    JOIN users u ON tr.tutor_id = u.id
    JOIN subjects s ON tr.subject_id = s.id
    WHERE tr.tutee_id = ? AND tr.status = 'accepted'
    ORDER BY tr.created_at DESC
");
$stmt->execute([$_SESSION['role_id']]);
$current_tutors = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
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

        <!-- Demandes en attente -->
        <section class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Demandes en attente</h2>
            
            <?php if (empty($pending_requests)): ?>
                <div class="bg-white rounded-lg shadow-md p-6 text-gray-600">
                    Aucune demande en attente.
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($pending_requests as $request): ?>
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-start">
                                <img src="uploads/<?php echo htmlspecialchars($request['photo'] ?? 'default.jpg'); ?>" 
                                     alt="Photo de <?php echo htmlspecialchars($request['username']); ?>"
                                     class="w-12 h-12 rounded-full object-cover mr-4">
                                <div class="flex-grow">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="font-semibold text-gray-800">
                                                <?php echo htmlspecialchars($request['username']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                Matière : <?php echo htmlspecialchars($request['subject_name']); ?>
                                            </p>
                                        </div>
                                        <span class="text-sm text-gray-500">
                                            <?php echo date('d/m/Y H:i', strtotime($request['created_at'])); ?>
                                        </span>
                                    </div>
                                    <p class="text-gray-700 mb-4">
                                        <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                                    </p>
                                    <div class="flex justify-end">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="relationship_id" value="<?php echo $request['relationship_id']; ?>">
                                            <button type="submit" name="action" value="cancel"
                                                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')">
                                                Annuler la demande
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Tuteurs actuels -->
        <section>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Mes tuteurs actuels</h2>
            
            <?php if (empty($current_tutors)): ?>
                <div class="bg-white rounded-lg shadow-md p-6 text-gray-600">
                    <p class="mb-4">Vous n'avez pas encore de tuteur.</p>
                    <a href="all-tutors.php" class="text-blue-600 hover:text-blue-800 font-medium">
                        Rechercher un tuteur →
                    </a>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 gap-4">
                    <?php foreach ($current_tutors as $tutor): ?>
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <img src="uploads/<?php echo htmlspecialchars($tutor['photo'] ?? 'default.jpg'); ?>" 
                                     alt="Photo de <?php echo htmlspecialchars($tutor['username']); ?>"
                                     class="w-12 h-12 rounded-full object-cover mr-4">
                                <div>
                                    <h3 class="font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($tutor['username']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Matière : <?php echo htmlspecialchars($tutor['subject_name']); ?>
                                    </p>
                                    <span class="text-sm text-gray-500">
                                        Depuis le <?php echo date('d/m/Y', strtotime($tutor['started_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($current_tutors) < 4): ?>
                    <div class="mt-6 text-center">
                        <a href="all-tutors.php" 
                           class="inline-block bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            Trouver d'autres tuteurs
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</main>
</body>
</html>
