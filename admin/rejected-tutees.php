<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth/admin-auth.php';
require_once '../includes/email/mailer.php';

// Verify admin authentication
checkAdminAuth();

$db = Database::getInstance()->getConnection();
$currentPage = 'rejected-tutees';
$pageTitle = 'Tutorés rejetés';

// Process actions (restore or delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    
    if ($user_id) {
        try {
            $db->beginTransaction();
            
            if ($action === 'restore') {
                // Update user status to pending
                $stmt = $db->prepare("
                    UPDATE users 
                    SET status = 'pending'
                    WHERE id = ? AND user_type = 'tutee'
                ");
                $stmt->execute([$user_id]);
                
                // Get user email
                $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
                
                // Send notification email
                $subject = "Votre compte tutoré a été restauré";
                $message = "Bonjour,\n\nVotre compte tutoré a été restauré et est en attente de validation. Vous serez notifié lorsqu'il sera approuvé.\n\nCordialement,\nLe secrétariat";
                
                send_utf8_email($user['email'], $subject, $message);
                
                $db->commit();
                $_SESSION['success_message'] = "Le tutoré a été restauré avec succès.";
            } elseif ($action === 'delete') {
                // Get tutee_id from users table
                $stmt = $db->prepare("
                    SELECT t.id as tutee_id 
                    FROM users u
                    LEFT JOIN tutees t ON u.id = t.user_id
                    WHERE u.id = ? AND u.user_type = 'tutee'
                ");
                $stmt->execute([$user_id]);
                $tutee = $stmt->fetch();
                
                if ($tutee && $tutee['tutee_id']) {
                    // Delete related relationships from active relationships
                    $stmt = $db->prepare("
                        DELETE FROM tutoring_relationships 
                        WHERE tutee_id = ?
                    ");
                    $stmt->execute([$tutee['tutee_id']]);
                    
                    // Delete related relationships from archive
                    $stmt = $db->prepare("
                        DELETE FROM tutoring_relationships_archive 
                        WHERE tutee_id = ?
                    ");
                    $stmt->execute([$tutee['tutee_id']]);
                    
                    // Delete from tutees table
                    $stmt = $db->prepare("
                        DELETE FROM tutees 
                        WHERE id = ?
                    ");
                    $stmt->execute([$tutee['tutee_id']]);
                }
                
                // Delete from users table
                $stmt = $db->prepare("
                    DELETE FROM users 
                    WHERE id = ? AND user_type = 'tutee'
                ");
                $stmt->execute([$user_id]);
                
                $db->commit();
                $_SESSION['success_message'] = "Le tutoré a été supprimé avec succès.";
            }
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error_message'] = "Une erreur est survenue : " . $e->getMessage();
        }
    }
}

// Get rejected tutees grouped by department
$stmt = $db->prepare("
    SELECT u.*, d.name as department_name
    FROM users u
    JOIN departments d ON u.department_id = d.id
    WHERE u.user_type = 'tutee' 
    AND u.status = 'rejected'
    ORDER BY d.name, u.lastname, u.firstname
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

    <h1 class="text-2xl font-bold mb-6">Tutorés rejetés</h1>

    <?php if (empty($tutees_by_dept)): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-center">Aucun tutoré rejeté.</p>
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
                                    <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir restaurer ce tutoré ?');">
                                        <input type="hidden" name="user_id" value="<?php echo $tutee['id']; ?>">
                                        <input type="hidden" name="action" value="restore">
                                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                            Restaurer
                                        </button>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce tutoré ? Cette action est irréversible et supprimera également toutes les relations associées.');">
                                        <input type="hidden" name="user_id" value="<?php echo $tutee['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                            Supprimer
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
