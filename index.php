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
    // Get latest tutors
    $latest_tutors = getLatestTutors($db);
    
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
    <!-- Section de bienvenue -->
    <section class="text-center mb-12 bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Bienvenue sur la plateforme de tutorat</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
            Cette plateforme permet de mettre en relation les étudiants qui souhaitent partager leurs connaissances (tuteurs) 
            avec ceux qui cherchent de l'aide dans certaines matières (tutorés).
        </p>
    </section>

    <!-- Cartes de fonctionnalités -->
    <section class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
        <!-- Cards for different user types -->
        <?php include 'includes/components/feature-cards.php'; ?>
    </section>

    <!-- Section Derniers tuteurs -->
    <section class="mb-12">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Nos derniers tuteurs</h2>
            <a href="all-tutors.php" class="text-blue-600 hover:underline">Voir tous les tuteurs</a>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($latest_tutors as $tutor): ?>
                <?php renderTutorCard($tutor, $availability_by_tutor); ?>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>