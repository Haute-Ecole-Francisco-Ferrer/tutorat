<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$currentPage = 'forgot-password';
$pageTitle = 'Mot de passe oublié';
$message = "";
$message_type = "";

// Check for flash messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'error';
    
    // Clear flash messages
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Mot de passe oublié</h2>
        
        <?php if ($message): ?>
            <div class="<?php echo $message_type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> px-4 py-3 rounded mb-4 border">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <p class="mb-4 text-gray-600">
            Entrez votre adresse e-mail ci-dessous et nous vous enverrons un lien pour réinitialiser votre mot de passe.
        </p>

        <form method="POST" action="process-forgot-password.php" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Adresse e-mail :
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <div class="flex flex-col space-y-4">
                <button type="submit" 
                        class="w-full bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Envoyer le lien de réinitialisation
                </button>
                
                <div class="text-center text-sm text-gray-600">
                    <a href="login.php" class="text-blue-600 hover:underline">Retour à la connexion</a>
                </div>
            </div>
        </form>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
