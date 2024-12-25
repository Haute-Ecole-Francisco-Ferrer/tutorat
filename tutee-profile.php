<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/validation/profile-validation.php';
require_once 'includes/components/profile-form.php';

// Verify user is logged in and is a tutee
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutee') {
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

$currentPage = 'tutee-profile';
$pageTitle = 'Mon Profil';

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

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-24 h-24 rounded-full overflow-hidden mr-6">
                            <?php if ($user['photo']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                                     alt="Photo de profil" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-3xl text-gray-500">
                                        <?php echo strtoupper(substr($user['firstname'], 0, 1)) . strtoupper(substr($user['lastname'], 0, 1)); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">
                                <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                            </h1>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($user['department_name']); ?> - 
                                <?php echo htmlspecialchars($user['section']); ?>
                            </p>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($user['study_level']); ?>
                            </p>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                        </div>
                    </div>
                    <a href="edit-profile.php" 
                       class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                        Modifier mon profil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>