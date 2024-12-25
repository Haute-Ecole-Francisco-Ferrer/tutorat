<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté et est un tutoré
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tutee') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

// Récupérer l'ID du tutoré
$stmt = $db->prepare("SELECT id FROM tutees WHERE user_id = ?");
$stmt->execute([$user_id]);
$tutee = $stmt->fetch();

if (!$tutee) {
    header('Location: index.php');
    exit;
}

$tutee_id = $tutee['id'];

// Récupérer les demandes en attente
$query = "SELECT tr.id, tr.status, tr.created_at, tr.tutee_message, tr.tutor_response,
          u.firstname, u.lastname, u.photo, u.study_level, u.section,
          u.username as tutor_email, u.phone,
          s.name as subject_name, d.name as department_name
          FROM tutoring_relationships tr
          JOIN tutors t ON tr.tutor_id = t.id
          JOIN users u ON t.user_id = u.id
          JOIN subjects s ON tr.subject_id = s.id
          JOIN departments d ON u.department_id = d.id
          WHERE tr.tutee_id = ? AND tr.status = 'pending'
          ORDER BY tr.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute([$tutee_id]);
$pending_requests = $stmt->fetchAll();

// Récupérer les tuteurs actifs
$query = "SELECT tr.id, tr.created_at, tr.tutee_message, tr.tutor_response,
          u.firstname, u.lastname, u.photo, u.study_level, u.section,
          u.username as tutor_email, u.phone,
          s.name as subject_name, d.name as department_name
          FROM tutoring_relationships tr
          JOIN tutors t ON tr.tutor_id = t.id
          JOIN users u ON t.user_id = u.id
          JOIN subjects s ON tr.subject_id = s.id
          JOIN departments d ON u.department_id = d.id
          WHERE tr.tutee_id = ? AND tr.status = 'accepted'
          ORDER BY tr.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute([$tutee_id]);
$active_tutors = $stmt->fetchAll();

// Récupérer les demandes refusées récentes (moins de 7 jours)
$query = "SELECT tr.id, tr.status, tr.updated_at, tr.tutee_message, tr.tutor_response,
          u.firstname, u.lastname,
          s.name as subject_name
          FROM tutoring_relationships tr
          JOIN tutors t ON tr.tutor_id = t.id
          JOIN users u ON t.user_id = u.id
          JOIN subjects s ON tr.subject_id = s.id
          WHERE tr.tutee_id = ? 
          AND tr.status = 'rejected'
          AND tr.updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
          ORDER BY tr.updated_at DESC";

$stmt = $db->prepare($query);
$stmt->execute([$tutee_id]);
$rejected_requests = $stmt->fetchAll();

$currentPage = 'my-tutors';
$pageTitle = 'Mes tuteurs';

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Demandes en attente -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Demandes en attente</h2>
            <?php if (empty($pending_requests)): ?>
                <p class="text-gray-600 text-center py-4">
                    Aucune demande en attente.
                </p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($pending_requests as $request): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
                                    <?php if ($request['photo']): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($request['photo']); ?>" 
                                             alt="Photo de <?php echo htmlspecialchars($request['firstname']); ?>"
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-xl text-gray-500">
                                                <?php echo strtoupper(substr($request['firstname'], 0, 1) . substr($request['lastname'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="font-medium text-lg">
                                        <?php echo htmlspecialchars($request['firstname'] . ' ' . $request['lastname']); ?>
                                    </h3>
                                    <p class="text-gray-600">
                                        <?php echo htmlspecialchars($request['department_name']); ?> - 
                                        <?php echo htmlspecialchars($request['study_level']); ?>
                                    </p>
                                    <p class="text-gray-600 mt-1">
                                        <strong>Matière :</strong> <?php echo htmlspecialchars($request['subject_name']); ?>
                                    </p>
                                    <?php if ($request['tutee_message']): ?>
                                        <div class="mt-2 p-3 bg-gray-50 rounded-md">
                                            <p class="text-sm text-gray-700">
                                                <strong>Votre message :</strong><br>
                                                <?php echo nl2br(htmlspecialchars($request['tutee_message'])); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Demande envoyée le <?php echo date('d/m/Y à H:i', strtotime($request['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tuteurs actifs -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Mes tuteurs actuels</h2>
            <?php if (empty($active_tutors)): ?>
                <p class="text-gray-600 text-center py-4">
                    Vous n'avez pas encore de tuteur.
                </p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($active_tutors as $tutor): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
                                    <?php if ($tutor['photo']): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($tutor['photo']); ?>" 
                                             alt="Photo de <?php echo htmlspecialchars($tutor['firstname']); ?>"
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-xl text-gray-500">
                                                <?php echo strtoupper(substr($tutor['firstname'], 0, 1) . substr($tutor['lastname'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow">
                                    <h3 class="font-medium text-lg">
                                        <?php echo htmlspecialchars($tutor['firstname'] . ' ' . $tutor['lastname']); ?>
                                    </h3>
                                    <p class="text-gray-600">
                                        <?php echo htmlspecialchars($tutor['department_name']); ?> - 
                                        <?php echo htmlspecialchars($tutor['study_level']); ?>
                                    </p>
                                    <p class="text-gray-600 mt-1">
                                        <strong>Matière :</strong> <?php echo htmlspecialchars($tutor['subject_name']); ?>
                                    </p>
                                    <?php if ($tutor['tutor_response']): ?>
                                        <div class="mt-2 p-3 bg-green-50 rounded-md">
                                            <p class="text-sm text-gray-700">
                                                <strong>Message du tuteur :</strong><br>
                                                <?php echo nl2br(htmlspecialchars($tutor['tutor_response'])); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mt-3 text-sm text-gray-600">
                                        <p><strong>Contact :</strong></p>
                                        <p>Email : <?php echo htmlspecialchars($tutor['tutor_email']); ?></p>
                                        <p>Téléphone : <?php echo htmlspecialchars($tutor['phone']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Demandes refusées récentes -->
        <?php if (!empty($rejected_requests)): ?>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">Demandes refusées récemment</h2>
                <div class="space-y-4">
                    <?php foreach ($rejected_requests as $request): ?>
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <p class="text-gray-600">
                                Votre demande de tutorat en <strong><?php echo htmlspecialchars($request['subject_name']); ?></strong> 
                                auprès de <strong><?php echo htmlspecialchars($request['firstname'] . ' ' . $request['lastname']); ?></strong>
                                a été refusée le <?php echo date('d/m/Y', strtotime($request['updated_at'])); ?>.
                            </p>
                            <?php if ($request['tutor_response']): ?>
                                <div class="mt-2 p-3 bg-gray-100 rounded-md">
                                    <p class="text-sm text-gray-700">
                                        <strong>Message du tuteur :</strong><br>
                                        <?php echo nl2br(htmlspecialchars($request['tutor_response'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>