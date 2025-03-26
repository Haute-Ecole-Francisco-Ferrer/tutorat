<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$currentPage = 'reset-password';
$pageTitle = 'Réinitialiser le mot de passe';
$message = "";
$message_type = "";
$valid_token = false;
$token = $_GET['token'] ?? '';

// Check for flash messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'error';
    
    // Clear flash messages
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Check if token is valid
if (!empty($token)) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT prt.*, u.firstname, u.lastname 
            FROM password_reset_tokens prt
            JOIN users u ON prt.user_id = u.id
            WHERE prt.token = ? 
            AND prt.expires_at > NOW() 
            AND prt.used = 0
        ");
        $stmt->execute([$token]);
        $token_data = $stmt->fetch();
        
        if ($token_data) {
            $valid_token = true;
            $user_id = $token_data['user_id'];
            $user_name = $token_data['firstname'] . ' ' . $token_data['lastname'];
        } else {
            $message = "Ce lien de réinitialisation est invalide ou a expiré.";
            $message_type = "error";
        }
    } catch (PDOException $e) {
        $message = "Une erreur est survenue. Veuillez réessayer.";
        $message_type = "error";
    }
} else {
    $message = "Token de réinitialisation manquant.";
    $message_type = "error";
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Réinitialiser le mot de passe</h2>
        
        <?php if ($message): ?>
            <div class="<?php echo $message_type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> px-4 py-3 rounded mb-4 border">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($valid_token): ?>
            <p class="mb-4 text-gray-600">
                Bonjour <?php echo htmlspecialchars($user_name); ?>, veuillez choisir un nouveau mot de passe.
            </p>

            <form method="POST" action="process-reset-password.php" class="space-y-6">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Nouveau mot de passe :
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           minlength="8"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <p class="text-xs text-gray-500 mt-1">Le mot de passe doit contenir au moins 8 caractères.</p>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmer le mot de passe :
                    </label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required 
                           minlength="8"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div class="flex flex-col space-y-4">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Réinitialiser le mot de passe
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="text-center text-sm text-gray-600 mt-4">
                <a href="forgot-password.php" class="text-blue-600 hover:underline">Demander un nouveau lien de réinitialisation</a>
            </div>
        <?php endif; ?>
        
        <div class="text-center text-sm text-gray-600 mt-6">
            <a href="login.php" class="text-blue-600 hover:underline">Retour à la connexion</a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
