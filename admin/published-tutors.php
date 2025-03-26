<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth/admin-auth.php';
require_once '../includes/email/mailer.php';

// Verify admin authentication
checkAdminAuth();

$db = Database::getInstance()->getConnection();
$currentPage = 'published-tutors';
$pageTitle = 'Tuteurs publiés';

// Process unpublish
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
    
    if ($user_id) {
        try {
            $db->beginTransaction();
            
            // Update user status
            $stmt = $db->prepare("
                UPDATE users 
                SET status = 'pending'
                WHERE id = ? AND user_type = 'tutor'
            ");
            $stmt->execute([$user_id]);
            
            // Get user email
            $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            // Send notification email
            $subject = "Votre compte tuteur a été suspendu";
            $message = "Bonjour,\n\nVotre compte tuteur a été temporairement suspendu. Pour plus d'informations, veuillez contacter le secrétariat.\n\nCordialement,\nLe secrétariat";
            
            send_utf8_email($user['email'], $subject, $message);
            
            $db->commit();
            $_SESSION['success_message'] = "Le tuteur a été suspendu avec succès.";
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error_message'] = "Une erreur est survenue : " . $e->getMessage();
        }
    }
}

// Get published tutors grouped by department
$stmt = $db->prepare("
    SELECT u.*, d.name as department_name,
           GROUP_CONCAT(s.name) as subjects,
           (
               SELECT COUNT(*)
               FROM tutoring_relationships tr
               JOIN tutors t ON tr.tutor_id = t.id
               WHERE t.user_id = u.id
               AND tr.status = 'accepted'
           ) as active_tutees
    FROM users u
    JOIN departments d ON u.department_id = d.id
    LEFT JOIN tutors t ON u.id = t.user_id
    LEFT JOIN tutor_subjects ts ON t.id = ts.tutor_id
    LEFT JOIN subjects s ON ts.subject_id = s.id
    WHERE u.user_type = 'tutor' 
    AND u.status = 'published'
    GROUP BY u.id, d.id
    ORDER BY d.name, u.lastname, u.firstname
");
$stmt->execute();
$tutors = $stmt->fetchAll();

// Group tutors by department
$tutors_by_dept = [];
foreach ($tutors as $tutor) {
    $tutors_by_dept[$tutor['department_name']][] = $tutor;
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

    <h1 class="text-2xl font-bold mb-6">Tuteurs publiés</h1>

    <?php if (empty($tutors_by_dept)): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <p class="text-gray-600 text-center">Aucun tuteur publié.</p>
        </div>
    <?php else: ?>
        <?php foreach ($tutors_by_dept as $dept_name => $dept_tutors): ?>
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4"><?php echo htmlspecialchars($dept_name); ?></h2>
                <div class="space-y-4">
                    <?php foreach ($dept_tutors as $tutor): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-medium">
                                        <?php echo htmlspecialchars($tutor['firstname'] . ' ' . $tutor['lastname']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Email: <?php echo htmlspecialchars($tutor['email']); ?><br>
                                        Téléphone: <?php echo htmlspecialchars($tutor['phone']); ?><br>
                                        Section: <?php echo htmlspecialchars($tutor['section']); ?><br>
                                        Niveau: <?php echo htmlspecialchars($tutor['study_level']); ?><br>
                                        Tutorés actifs: <?php echo $tutor['active_tutees']; ?>
                                    </p>
                                    <?php if ($tutor['subjects']): ?>
                                        <div class="mt-2">
                                            <p class="text-sm font-medium">Matières:</p>
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                <?php foreach (explode(',', $tutor['subjects']) as $subject): ?>
                                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                                        <?php echo htmlspecialchars($subject); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Inscrit le <?php echo date('d/m/Y', strtotime($tutor['created_at'])); ?>
                                    </p>
                                </div>
                                <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir suspendre ce tuteur ?');">
                                    <input type="hidden" name="user_id" value="<?php echo $tutor['id']; ?>">
                                    <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                                        Suspendre
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
