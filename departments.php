<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/utils/department-colors.php';

session_start();
$currentPage = 'departments';
$pageTitle = 'Départements';

$db = Database::getInstance()->getConnection();

// Get departments with their statistics
$query = "
    SELECT 
        d.id,
        d.name,
        COUNT(DISTINCT CASE WHEN u.user_type = 'tutor' THEN u.id END) as tutor_count,
        COUNT(DISTINCT CASE WHEN u.user_type = 'tutee' THEN u.id END) as tutee_count
    FROM departments d
    LEFT JOIN users u ON d.id = u.department_id
    GROUP BY d.id
    ORDER BY d.name
";

$departments = $db->query($query)->fetchAll();

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <!-- En-tête de la page -->
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Nos Départements</h1>
        <p class="text-gray-600 max-w-2xl mx-auto">
            Découvrez nos différents départements et leurs tuteurs. Chaque département dispose de tuteurs spécialisés 
            dans différentes matières pour vous accompagner dans votre apprentissage.
        </p>
    </div>

    <!-- Grille des départements -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($departments as $dept): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow border-t-4 <?php echo getDepartmentBorderClass($dept['id']); ?>">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4 <?php echo getDepartmentTextClass($dept['id']); ?>">
                        <?php echo htmlspecialchars($dept['name']); ?>
                    </h2>
                    
                    <div class="flex space-x-4 mb-4">
                        <div class="flex-1 text-center rounded-lg p-3" style="background-color: <?php echo getDepartmentColor($dept['id']); ?>20;">
                            <span class="block text-2xl font-bold" style="color: <?php echo getDepartmentColor($dept['id']); ?>;">
                                <?php echo $dept['tutor_count']; ?>
                            </span>
                            <span class="text-sm text-gray-600">Tuteur<?php echo $dept['tutor_count'] > 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="flex-1 text-center rounded-lg p-3" style="background-color: <?php echo getDepartmentColor($dept['id']); ?>10;">
                            <span class="block text-2xl font-bold" style="color: <?php echo getDepartmentColor($dept['id']); ?>;">
                                <?php echo $dept['tutee_count']; ?>
                            </span>
                            <span class="text-sm text-gray-600">Tutoré<?php echo $dept['tutee_count'] > 1 ? 's' : ''; ?></span>
                        </div>
                    </div>

                    <!-- Subjects section removed due to database structure mismatch -->

                    <div class="mt-4">
                        <a href="all-tutors.php?department=<?php echo $dept['id']; ?>" 
                           class="inline-block text-white px-4 py-2 rounded-md hover:opacity-90 transition-colors text-sm w-full text-center"
                           style="background-color: <?php echo getDepartmentColor($dept['id']); ?>;">
                            Voir les tuteurs
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Section aide au choix -->
    <div class="mt-12 bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Comment choisir son département ?</h2>
        <div class="text-gray-600 space-y-4">
            <p>
                Le département choisi lors de votre inscription est crucial car il détermine les tuteurs avec lesquels 
                vous pourrez interagir. Pour une expérience optimale :
            </p>
            <ul class="list-disc list-inside space-y-2 ml-4">
                <li>Choisissez le département qui correspond à votre cursus actuel</li>
                <li>Vous ne pourrez interagir qu'avec les tuteurs et tutorés de votre département</li>
                <li>Ce choix est définitif pour garantir une cohérence dans les échanges</li>
                <li>En cas de doute, contactez votre secrétariat pour confirmation</li>
            </ul>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
