<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Vérifier si l'utilisateur est connecté et est un tuteur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header('Location: login.php');
    exit();
}

$currentPage = 'my-tutees';
$pageTitle = 'Mes Tutorés';

$db = Database::getInstance()->getConnection();
$error_message = "";
$success_message = "";

// Traitement des actions (accepter/refuser)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['relationship_id'])) {
    $relationship_id = (int)$_POST['relationship_id'];
    $action = $_POST['action'];

    try {
        if ($action === 'accept') {
            // Vérifier si le tuteur n'a pas déjà 4 tutorés
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM tutoring_relationships 
                WHERE tutor_id = ? AND status = 'accepted'
            ");
            $stmt->execute([$_SESSION['role_id']]);
            $current_tutees = $stmt->fetch()['count'];

            if ($current_tutees >= 4) {
                $error_message = "Vous avez déjà atteint le nombre maximum de tutorés (4).";
            } else {
                $stmt = $db->prepare("
                    UPDATE tutoring_relationships 
                    SET status = 'accepted', updated_at = NOW() 
                    WHERE id = ? AND tutor_id = ?
                ");
                $stmt->execute([$relationship_id, $_SESSION['role_id']]);
                $success_message = "La demande a été acceptée.";
            }
        } elseif ($action === 'reject' || $action === 'end') {
            $status = $action === 'reject' ? 'rejected' : 'terminated';
            $stmt = $db->prepare("
                UPDATE tutoring_relationships 
                SET status = ?, updated_at = NOW() 
                WHERE id = ? AND tutor_id = ?
            ");
            $stmt->execute([$status, $relationship_id, $_SESSION['role_id']]);
            $success_message = $action === 'reject' ? "La demande a été refusée." : "Le tutorat a été terminé.";
        }
    } catch (PDOException $e) {
        $error_message = "Une erreur est survenue. Veuillez réessayer.";
    }
}

// Récupérer les demandes en attente
$stmt = $db->prepare("
    SELECT 
        tr.id as relationship_id,
        tr.status,
        tr.message,
        tr.created_at,
        u.username,
        u.photo,
        s.name as subject_name
    FROM tutoring_relationships tr
    JOIN users u ON tr.tutee_id = u.id
    JOIN subjects s ON tr.subject_id = s.id
    WHERE tr.tutor_id = ? AND tr.status = 'pending'
    ORDER BY tr.created_at DESC
");
$stmt->execute([$_SESSION['role_id']]);
$pending_requests = $stmt->fetchAll();

// Récupérer les tutorés actuels
$stmt = $db->prepare("
    SELECT 
        tr.id as relationship_id,
        tr.created_at as started_at,
        u.username,
        u.photo,
        s.name as subject_name
    FROM tutoring_relationships tr
    JOIN users u ON tr.tutee_id = u.id
    JOIN subjects s ON tr.subject_id = s.id
    WHERE tr.tutor_id = ? AND tr.status = 'accepted'
    ORDER BY tr.created_at DESC
");
$stmt->execute([$_SESSION['role_id']]);
$current_tutees = $stmt->fetchAll();

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
                                    <div class="flex justify-end space-x-2">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="relationship_id" value="<?php echo $request['relationship_id']; ?>">
                                            <button type="submit" name="action" value="reject"
                                                    class="bg-red-100 text-red-700 px-4 py-2 rounded hover:bg-red-200 transition-colors">
                                                Refuser
                                            </button>
                                            <button type="submit" name="action" value="accept"
                                                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                                                Accepter
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

        <!-- Tutorés actuels -->
        <section>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Mes tutorés actuels</h2>
                <span class="text-sm text-gray-600">
                    <?php echo count($current_tutees); ?>/4 tutorés
                </span>
            </div>
            
            <?php if (empty($current_tutees)): ?>
                <div class="bg-white rounded-lg shadow-md p-6 text-gray-600">
                    Aucun tutoré actuellement.
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 gap-4">
                    <?php foreach ($current_tutees as $tutee): ?>
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center mb-4">
                                <img src="uploads/<?php echo htmlspecialchars($tutee['photo'] ?? 'default.jpg'); ?>" 
                                     alt="Photo de <?php echo htmlspecialchars($tutee['username']); ?>"
                                     class="w-12 h-12 rounded-full object-cover mr-4">
                                <div>
                                    <h3 class="font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($tutee['username']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Matière : <?php echo htmlspecialchars($tutee['subject_name']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">
                                    Depuis le <?php echo date('d/m/Y', strtotime($tutee['started_at'])); ?>
                                </span>
                                <form method="POST">
                                    <input type="hidden" name="relationship_id" value="<?php echo $tutee['relationship_id']; ?>">
                                    <button type="submit" name="action" value="end"
                                            class="text-red-600 hover:text-red-800 text-sm font-medium"
                                            onclick="return confirm('Êtes-vous sûr de vouloir terminer ce tutorat ?')">
                                        Terminer le tutorat
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
</body>
</html>
