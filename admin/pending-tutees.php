<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth/admin-auth.php';
require_once '../includes/email/mailer.php';

// Verify admin authentication
checkAdminAuth();

$db = Database::getInstance()->getConnection();
$currentPage = 'pending-tutees';
$pageTitle = 'Tutorés en attente';

// Process approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    
    if ($user_id && in_array($action, ['approve', 'reject'])) {
        try {
            $db->beginTransaction();
            
            // Update user status
            $stmt = $db->prepare("
                UPDATE users 
                SET status = ? 
                WHERE id = ? AND user_type = 'tutee'
            ");
            $stmt->execute([$action === 'approve' ? 'published' : 'rejected', $user_id]);
            
            // Get user email
            $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            // Send notification email
            if ($action === 'approve') {
                $subject = "Votre inscription comme tutoré a été approuvée";
                $message = "Bonjour,\n\nVotre inscription comme tutoré a été approuvée. Vous pouvez maintenant vous connecter à la plateforme.\n\nCordialement,\nLe secrétariat";
            } else {
                $subject = "Votre inscription comme tutoré a été refusée";
                $message = "Bonjour,\n\nVotre inscription comme tutoré a été refusée. Pour plus d'informations, veuillez contacter le secrétariat.\n\nCordialement,\nLe secrétariat";
            }
            
            mail($user['email'], $subject, $message);
            
            $db->commit();
            $_SESSION['success_message'] = "Le statut du tutoré a été mis à jour avec succès.";
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error_message'] = "Une erreur est survenue : " . $e->getMessage();
        }
    }
}

// Get pending tutees grouped by department
$stmt = $db->prepare("
    SELECT u.*, d.name as department_name
    FROM users u
    JOIN departments d ON u.department_id = d.id
    WHERE u.user_type = 'tutee' 
    AND u.status = 'pending'
    ORDER BY d.name, u.created_at DESC
");
$stmt->execute();
$tutees = $stmt->fetchAll();

// Group tutees by department
$tutees_by_dept = [];
foreach ($tutees as $tutee) {
    $tutees_by_dept[$tutee['department_name']][] = $tutee;
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
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

    <h1 class="text-2xl font-bold mb-6">Tutorés en attente de validation</h1>

    <?php if (empty($tutees_by_dept)): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-center">Aucun tutoré en attente de validation.</p>
        </div>
    <?php else: ?>
        <?php foreach ($tutees_by_dept as $dept_name => $dept_tutees): ?>
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4"><?php echo htmlspecialchars($dept_name); ?></h2>
                <div class="space-y-4">
                    <?php foreach ($dept_tutees as $tutee): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-medium">
                                        <?php echo htmlspecialchars($tutee['firstname'] . ' ' . $tutee['lastname']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Email: <?php echo htmlspecialchars($tutee['email']); ?><br>
                                        Téléphone: <?php echo htmlspecialchars($tutee['phone']); ?><br>
                                        Section: <?php echo htmlspecialchars($tutee['section']); ?><br>
                                        Niveau: <?php echo htmlspecialchars($tutee['study_level']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Inscrit le <?php echo date('d/m/Y', strtotime($tutee['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?php echo $tutee['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                            Approuver
                                        </button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?php echo $tutee['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                            Refuser
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>