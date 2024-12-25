<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/validation/profile-validation.php';
require_once 'includes/components/profile-form.php';

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();
$error_message = "";
$success_message = "";

// Get user data
try {
    $stmt = $db->prepare("
        SELECT u.*, d.name as department_name 
        FROM users u 
        JOIN departments d ON u.department_id = d.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: index.php');
        exit;
    }

    $departments = get_departments($db);

} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

$currentPage = 'edit-profile';
$pageTitle = 'Modifier mon profil';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $validatedData = validateProfileData($_POST, $_FILES);
        updateUserProfile($db, $user_id, $validatedData);
        $success_message = "Votre profil a été mis à jour avec succès.";
        
        // Refresh user data
        $stmt = $db->prepare("
            SELECT u.*, d.name as department_name 
            FROM users u 
            JOIN departments d ON u.department_id = d.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Modifier mon profil</h1>
                <a href="<?php echo $_SESSION['user_type'] === 'tutor' ? 'tutor-profile.php' : 'tutee-profile.php'; ?>" 
                   class="text-blue-600 hover:underline">
                    Retour au profil
                </a>
            </div>
            
            <?php renderMessages($error_message, $success_message); ?>
            <?php renderProfileForm($user, $departments); ?>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>