<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$currentPage = 'login';
$pageTitle = 'Connexion';
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $db = Database::getInstance()->getConnection();
        
        // First check if user is an admin
        $stmt = $db->prepare("
            SELECT u.*, a.id as admin_id 
            FROM users u
            LEFT JOIN admins a ON u.id = a.user_id
            WHERE u.username = ?
        ");
        
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            if ($user['admin_id']) {
                $_SESSION['user_type'] = 'admin';
                header('Location: admin/dashboard.php');
            } else {
                // Check if user is tutor or tutee
                $stmt = $db->prepare("
                    SELECT 
                        COALESCE(tutors.id, tutees.id) as role_id,
                        CASE 
                            WHEN tutors.id IS NOT NULL THEN 'tutor'
                            WHEN tutees.id IS NOT NULL THEN 'tutee'
                        END as role_type
                    FROM users u
                    LEFT JOIN tutors ON u.id = tutors.user_id
                    LEFT JOIN tutees ON u.id = tutees.user_id
                    WHERE u.id = ?
                ");
                
                $stmt->execute([$user['id']]);
                $role = $stmt->fetch();
                
                $_SESSION['user_type'] = $role['role_type'];
                $_SESSION['role_id'] = $role['role_id'];
                header('Location: index.php');
            }
            exit();
        } else {
            $error_message = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        $error_message = "Une erreur est survenue. Veuillez réessayer.";
    }
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Connexion</h2>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                    Nom d'utilisateur :
                </label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Mot de passe :
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <div class="flex flex-col space-y-4">
                <button type="submit" 
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Se connecter
                </button>
                
                <div class="text-center text-sm text-gray-600">
                    <a href="forgot-password.php" class="text-blue-600 hover:underline">Mot de passe oublié ?</a>
                </div>
                
                <div class="text-center text-sm text-gray-600">
                    Pas encore inscrit ?
                    <div class="mt-2 space-x-4">
                        <a href="register-tutor.php" class="text-blue-600 hover:underline">Devenir tuteur</a>
                        <span class="text-gray-400">|</span>
                        <a href="register-tutee.php" class="text-blue-600 hover:underline">Devenir tutoré</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
