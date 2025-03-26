<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
$currentPage = 'departments';
$pageTitle = 'Départements';

$db = Database::getInstance()->getConnection();

// Get departments with their statistics and subjects
$query = "
    SELECT 
        d.id,
        d.name,
        COUNT(DISTINCT CASE WHEN u.user_type = 'tutor' AND u.status = 'published' THEN u.id END) as tutor_count,
        COUNT(DISTINCT CASE WHEN u.user_type = 'tutee' AND u.status = 'published' THEN u.id END) as tutee_count,
        GROUP_CONCAT(DISTINCT s.name ORDER BY s.name) as subjects
    FROM departments d
    LEFT JOIN users u ON d.id = u.department_id
    LEFT JOIN subjects s ON s.department_id = d.id
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
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <?php echo htmlspecialchars($dept['name']); ?>
                    </h2>
                    
                    <div class="flex space-x-4 mb-4">
                        <div class="flex-1 text-center bg-blue-50 rounded-lg p-3">
                            <span class="block text-2xl font-bold text-blue-600">
                                <?php echo $dept['tutor_count']; ?>
                            </span>
                            <span class="text-sm text-gray-600">Tuteur<?php echo $dept['tutor_count'] > 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="flex-1 text-center bg-green-50 rounded-lg p-3">
                            <span class="block text-2xl font-bold text-green-600">
                                <?php echo $dept['tutee_count']; ?>
                            </span>
                            <span class="text-sm text-gray-600">Tutoré<?php echo $dept['tutee_count'] > 1 ? 's' : ''; ?></span>
                        </div>
                    </div>

                    <?php if ($dept['subjects']): ?>
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Matières disponibles :</h3>
                            <div class="flex flex-wrap gap-1">
                                <?php 
                                $subjects = explode(',', $dept['subjects']);
                                $display_subjects = array_slice($subjects, 0, 5);
                                foreach ($display_subjects as $subject): 
                                ?>
                                    <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                                        <?php echo htmlspecialchars($subject); ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php 
                                $remaining = count($subjects) - count($display_subjects);
                                if ($remaining > 0):
                                ?>
                                    <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                                        +<?php echo $remaining; ?> autres
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="all-tutors.php?department=<?php echo $dept['id']; ?>" 
                           class="inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm w-full text-center">
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