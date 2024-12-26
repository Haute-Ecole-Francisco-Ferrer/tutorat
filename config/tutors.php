<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

$currentPage = 'tutors';
$pageTitle = 'Liste des Tuteurs';

$db = Database::getInstance()->getConnection();

// Récupération des départements et matières pour les filtres
$departments = get_departments($db);
$subjects = get_subjects($db);

// Gestion des filtres
$filter_department = isset($_GET['department']) ? (int)$_GET['department'] : null;
$filter_subject = isset($_GET['subject']) ? (int)$_GET['subject'] : null;

// Construction de la requête
$query = "
    SELECT DISTINCT
        u.id,
        u.firstname,
        u.lastname,
        u.username,
        u.photo,
        d.name as department_name,
        GROUP_CONCAT(DISTINCT s.name) as subjects,
        (
            SELECT COUNT(*)
            FROM tutoring_relationships tr
            INNER JOIN tutors t ON t.id = tr.tutor_id
            WHERE t.user_id = u.id
            AND tr.status = 'accepted'
        ) as current_tutees
    FROM users u
    INNER JOIN departments d ON u.department_id = d.id
    INNER JOIN tutors t ON u.id = t.user_id
    LEFT JOIN tutor_subjects ts ON t.id = ts.tutor_id
    LEFT JOIN subjects s ON ts.subject_id = s.id
    WHERE u.user_type = 'tutor'
";

$params = [];

if ($filter_department) {
    $query .= " AND u.department_id = ?";
    $params[] = $filter_department;
}

if ($filter_subject) {
    $query .= " AND ts.subject_id = ?";
    $params[] = $filter_subject;
}

$query .= " GROUP BY u.id ORDER BY u.created_at DESC";

try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $tutors = $stmt->fetchAll();
} catch (PDOException $e) {
    $tutors = [];
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Filtrer les tuteurs</h2>
        
        <form method="GET" class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Filtre par département -->
            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Département</label>
                <select name="department" id="department" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="this.form.submit()">
                    <option value="">Tous les départements</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" 
                                <?php echo $filter_department == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtre par matière -->
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Matière</label>
                <select name="subject" id="subject" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="this.form.submit()">
                    <option value="">Toutes les matières</option>
                    <?php foreach ($subjects as $subj): ?>
                        <option value="<?php echo $subj['id']; ?>" 
                                <?php echo $filter_subject == $subj['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subj['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Liste des tuteurs -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($tutors)): ?>
            <div class="col-span-full text-center py-8">
                <p class="text-gray-600">Aucun tuteur ne correspond aux critères sélectionnés.</p>
            </div>
        <?php else: ?>
            <?php foreach ($tutors as $tutor): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center space-x-4 mb-4">
                        <!-- Photo -->
                        <img src="uploads/<?php echo htmlspecialchars($tutor['photo'] ?? 'default.jpg'); ?>" 
                             alt="Photo de <?php echo htmlspecialchars($tutor['username']); ?>"
                             class="w-20 h-20 rounded-full object-cover">
                        
                        <!-- Informations de base -->
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800"><?php echo htmlspecialchars($tutor['username']); ?></h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($tutor['department_name']); ?></p>
                        </div>
                    </div>

                    <!-- Matières -->
                    <?php if (!empty($tutor['subjects'])): ?>
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Matières :</h4>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach (explode(',', $tutor['subjects']) as $subject): ?>
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                        <?php echo htmlspecialchars($subject); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Bouton Contacter/Complet -->
                    <div class="mt-4 text-center">
                        <?php if ($tutor['current_tutees'] >= 4): ?>
                            <span class="inline-block bg-gray-100 text-gray-800 px-4 py-2 rounded-md">
                                Complet
                            </span>
                        <?php else: ?>
                            <a href="contact.php?tutor=<?php echo $tutor['id']; ?>" 
                               class="inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                Contacter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
