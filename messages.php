<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Only non-admin users should access this page
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    header('Location: admin/dashboard.php');
    exit();
}

$currentPage = 'messages';
$pageTitle = 'Messages';

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Messages</h1>
        
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        La fonctionnalité de messagerie sera bientôt disponible. Vous pourrez communiquer directement avec vos tuteurs/tutorés.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="text-center py-12">
            <svg class="mx-auto h-24 w-24 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Aucun message pour le moment</h2>
            <p class="mt-2 text-sm text-gray-500">
                Vous n'avez pas encore de messages. Les messages apparaîtront ici lorsque vous communiquerez avec vos tuteurs ou tutorés.
            </p>
        </div>
        
        <?php if ($_SESSION['user_type'] === 'tutee'): ?>
            <div class="mt-8 text-center">
                <a href="all-tutors.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors">
                    Trouver un tuteur
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
