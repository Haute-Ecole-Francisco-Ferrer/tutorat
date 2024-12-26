<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/queries/tutor-queries.php';
require_once 'includes/components/tutor-card.php';

session_start();
$currentPage = 'home';
$pageTitle = 'Accueil';

$db = Database::getInstance()->getConnection();

try {
    // Get latest tutors - filter by department if user is a tutee
    $department_id = null;
    if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'tutee') {
        $stmt = $db->prepare("SELECT department_id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $department_id = $user['department_id'];
    }
    
    $latest_tutors = getLatestTutors($db, $department_id);
    
    // Get availabilities for tutors
    $tutor_ids = array_column($latest_tutors, 'id');
    $availability_by_tutor = getTutorsAvailabilities($db, $tutor_ids);
} catch (PDOException $e) {
    $latest_tutors = [];
    $availability_by_tutor = [];
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <!-- Welcome Section -->
    <section class="text-center mb-12 bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Bienvenue sur la plateforme de tutorat</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
            Cette plateforme permet de mettre en relation les étudiants qui souhaitent partager leurs connaissances (tuteurs) 
            avec ceux qui cherchent de l'aide dans certaines matières (tutorés).
        </p>
    </section>

    <!-- Feature Cards -->
    <section class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
        <?php require_once 'includes/components/feature-cards.php'; ?>
    </section>

    <!-- Latest Tutors Section -->
    <section class="mb-12">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Nos derniers tuteurs</h2>
            <a href="all-tutors.php" class="text-blue-600 hover:underline">Voir tous les tuteurs</a>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($latest_tutors)): ?>
                <div class="md:col-span-2 lg:col-span-3 text-center py-8 text-gray-600">
                    Aucun tuteur disponible pour le moment.
                </div>
            <?php else: ?>
                <?php foreach ($latest_tutors as $tutor): ?>
                    <?php renderTutorCard($tutor, $availability_by_tutor); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>