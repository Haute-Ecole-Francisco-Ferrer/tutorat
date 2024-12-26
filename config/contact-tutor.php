<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/queries/relationship-queries.php';
require_once 'includes/email/mailer.php';
require_once 'includes/components/tutor-profile-card.php';
require_once 'includes/components/contact-form.php';

session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

$currentPage = 'contact-tutor';
$pageTitle = 'Contacter un tuteur';
$error_message = "";
$success_message = "";

$db = Database::getInstance()->getConnection();
$tutor_user_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $subject_id = filter_var($_POST['subject_id'], FILTER_VALIDATE_INT);
        $message = trim($_POST['message']);

        if (!$subject_id) {
            throw new Exception("Veuillez sélectionner une matière.");
        }
        if (empty($message)) {
            throw new Exception("Veuillez écrire un message.");
        }

        $db->beginTransaction();

        // Get tutee ID
        $tutee_id = getTuteeId($db, $_SESSION['user_id']);
        if (!$tutee_id) {
            throw new Exception("Erreur d'identification du tutoré.");
        }

        // Get tutor details
        $stmt = $db->prepare("
            SELECT t.id as tutor_id, u.email, u.username
            FROM tutors t
            JOIN users u ON t.user_id = u.id
            WHERE u.id = ?
        ");
        $stmt->execute([$tutor_user_id]);
        $tutor = $stmt->fetch();
        
        if (!$tutor) {
            throw new Exception("Tuteur non trouvé.");
        }

        // Check tutor availability
        checkTutorAvailability($db, $tutor['tutor_id']);

        // Check for existing request
        checkExistingRequest($db, $tutor['tutor_id'], $tutee_id, $subject_id);

        // Create tutoring request
        createTutoringRequest($db, $tutor['tutor_id'], $tutee_id, $subject_id, $message);

        // Send email to tutor
        sendTutorRequestEmail(
            $tutor['email'],
            $_SESSION['username'],
            $message
        );

        $db->commit();
        $success_message = "Votre demande a été envoyée au tuteur.";

    } catch (Exception $e) {
        $db->rollBack();
        $error_message = $e->getMessage();
    }
}

// Get tutor information for display
try {
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.firstname,
            u.lastname,
            u.photo,
            u.username,
            d.name as department_name,
            GROUP_CONCAT(DISTINCT s.name) as subjects,
            GROUP_CONCAT(DISTINCT s.id) as subject_ids,
            t.id as tutor_id,
            t.current_tutees
        FROM users u
        JOIN departments d ON u.department_id = d.id
        JOIN tutors t ON u.id = t.user_id
        LEFT JOIN tutor_subjects ts ON t.id = ts.tutor_id
        LEFT JOIN subjects s ON ts.subject_id = s.id
        WHERE u.id = ? AND u.user_type = 'tutor'
        GROUP BY u.id
    ");
    $stmt->execute([$tutor_user_id]);
    $tutor = $stmt->fetch();

    if (!$tutor || $tutor['current_tutees'] >= 4) {
        header('Location: all-tutors.php');
        exit();
    }

    // Get available subjects for this tutor
    $subject_ids = explode(',', $tutor['subject_ids']);
    $stmt = $db->prepare("SELECT id, name FROM subjects WHERE id IN (" . implode(',', $subject_ids) . ")");
    $stmt->execute();
    $subjects = $stmt->fetchAll();

} catch (PDOException $e) {
    header('Location: all-tutors.php');
    exit();
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
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

        <?php renderTutorProfileCard($tutor); ?>
        <?php renderContactForm($tutor, $subjects); ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>