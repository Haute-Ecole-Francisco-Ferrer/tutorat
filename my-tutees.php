<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Verify user is logged in and is a tutor
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tutor') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

// Get tutor ID
$stmt = $db->prepare("SELECT id FROM tutors WHERE user_id = ?");
$stmt->execute([$user_id]);
$tutor = $stmt->fetch();

if (!$tutor) {
    header('Location: index.php');
    exit;
}

$tutor_id = $tutor['id'];

// Get pending requests
$query = "SELECT tr.id, tr.status, tr.created_at,
          u.firstname, u.lastname, u.photo, u.study_level, u.section,
          s.name as subject_name, d.name as department_name,
          u.phone, u.email, tr.message
          FROM tutoring_relationships tr
          JOIN tutees t ON tr.tutee_id = t.id
          JOIN users u ON t.user_id = u.id
          JOIN subjects s ON tr.subject_id = s.id
          JOIN departments d ON u.department_id = d.id
          WHERE tr.tutor_id = ? AND tr.status = 'pending'
          ORDER BY tr.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute([$tutor_id]);
$pending_requests = $stmt->fetchAll();

// Get active tutees
$query = "SELECT tr.id, tr.created_at,
          u.firstname, u.lastname, u.photo, u.study_level, u.section,
          s.name as subject_name, d.name as department_name,
          u.phone, u.email
          FROM tutoring_relationships tr
          JOIN tutees t ON tr.tutee_id = t.id
          JOIN users u ON t.user_id = u.id
          JOIN subjects s ON tr.subject_id = s.id
          JOIN departments d ON u.department_id = d.id
          WHERE tr.tutor_id = ? AND tr.status = 'accepted'
          ORDER BY u.lastname, u.firstname";

$stmt = $db->prepare($query);
$stmt->execute([$tutor_id]);
$active_tutees = $stmt->fetchAll();

$currentPage = 'my-tutees';
$pageTitle = 'Mes tutorés';

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Pending Requests -->
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
                            <div class="flex items-start justify-between">
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
                                        <p class="text-gray-600 mt-1">
                                            <strong>Email :</strong> <?php echo htmlspecialchars($request['email']); ?><br>
                                            <strong>Téléphone :</strong> <?php echo htmlspecialchars($request['phone']); ?>
                                        </p>
                                        <?php if ($request['message']): ?>
                                            <div class="mt-2 p-3 bg-gray-50 rounded">
                                                <p class="text-sm text-gray-700">
                                                    <strong>Message du tutoré :</strong><br>
                                                    <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                        <p class="text-sm text-gray-500 mt-2">
                                            Demande reçue le <?php echo date('d/m/Y à H:i', strtotime($request['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <form method="POST" action="process-tutoring-request.php" class="space-y-2">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <textarea name="message" 
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md mb-2"
                                                  placeholder="Message (optionnel)"></textarea>
                                        <div class="flex justify-end space-x-2">
                                            <button type="submit" name="action" value="accept" 
                                                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                                Accepter
                                            </button>
                                            <button type="submit" name="action" value="reject" 
                                                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                                Refuser
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Active Tutees -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold mb-4">Mes tutorés actuels</h2>
            <?php if (empty($active_tutees)): ?>
                <p class="text-gray-600 text-center py-4">
                    Vous n'avez pas encore de tutorés.
                </p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($active_tutees as $tutee): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
                                    <?php if ($tutee['photo']): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($tutee['photo']); ?>" 
                                             alt="Photo de <?php echo htmlspecialchars($tutee['firstname']); ?>"
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-xl text-gray-500">
                                                <?php echo strtoupper(substr($tutee['firstname'], 0, 1) . substr($tutee['lastname'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow">
                                    <h3 class="font-medium text-lg">
                                        <?php echo htmlspecialchars($tutee['firstname'] . ' ' . $tutee['lastname']); ?>
                                    </h3>
                                    <p class="text-gray-600">
                                        <?php echo htmlspecialchars($tutee['department_name']); ?> - 
                                        <?php echo htmlspecialchars($tutee['study_level']); ?>
                                    </p>
                                    <p class="text-gray-600 mt-1">
                                        <strong>Matière :</strong> <?php echo htmlspecialchars($tutee['subject_name']); ?>
                                    </p>
                                    <div class="mt-3 text-sm text-gray-600">
                                        <p><strong>Contact :</strong></p>
                                        <p>Email : <?php echo htmlspecialchars($tutee['email']); ?></p>
                                        <p>Téléphone : <?php echo htmlspecialchars($tutee['phone']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>