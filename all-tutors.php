<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
$currentPage = 'all-tutors';
$pageTitle = 'Tous les Tuteurs';

$db = Database::getInstance()->getConnection();

// Récupération des départements et matières pour les filtres
$departments = get_departments($db);
$subjects = get_subjects($db);

// Paramètres de filtrage
$selected_department = isset($_GET['department']) ? (int)$_GET['department'] : 0;
$selected_subject = isset($_GET['subject']) ? (int)$_GET['subject'] : 0;

// Traduction des jours en français
$days_fr = [
    'Monday' => 'Lundi',
    'Tuesday' => 'Mardi',
    'Wednesday' => 'Mercredi',
    'Thursday' => 'Jeudi',
    'Friday' => 'Vendredi',
    'Saturday' => 'Samedi',
    'Sunday' => 'Dimanche'
];

// Construction de la requête SQL
$query = "
    SELECT 
        u.id,
        u.firstname,
        u.lastname,
        u.photo,
        u.username,
        d.name as department_name,
        GROUP_CONCAT(DISTINCT s.name) as subjects,
        GROUP_CONCAT(DISTINCT s.id) as subject_ids,
        t.id as tutor_id,
        (
            SELECT COUNT(*)
            FROM tutoring_relationships tr
            WHERE tr.tutor_id = t.id
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

if ($selected_department) {
    $query .= " AND u.department_id = ?";
    $params[] = $selected_department;
}

if ($selected_subject) {
    $query .= " AND EXISTS (
        SELECT 1 FROM tutor_subjects ts2 
        WHERE ts2.tutor_id = t.id 
        AND ts2.subject_id = ?
    )";
    $params[] = $selected_subject;
}

$query .= " GROUP BY u.id ORDER BY u.lastname, u.firstname";

try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $tutors = $stmt->fetchAll();

    // Récupérer les disponibilités pour tous les tuteurs
    $tutor_ids = array_column($tutors, 'id');
    if (!empty($tutor_ids)) {
        $availability_query = "
            SELECT user_id, 
                   day_of_week, 
                   DATE_FORMAT(start_time, '%H:%i') as start_time,
                   DATE_FORMAT(end_time, '%H:%i') as end_time
            FROM availability
            WHERE user_id IN (" . implode(',', $tutor_ids) . ")
            ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
        ";
        $stmt = $db->query($availability_query);
        $availabilities = $stmt->fetchAll();

        // Organiser les disponibilités par tuteur
        $availability_by_tutor = [];
        foreach ($availabilities as $availability) {
            $availability_by_tutor[$availability['user_id']][] = $availability;
        }
    }

} catch (PDOException $e) {
    $tutors = [];
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Filtrer les tuteurs</h2>
        <form action="" method="GET" class="grid md:grid-cols-2 gap-4">
            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Par département :</label>
                <select name="department" id="department" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les départements</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" 
                                <?php echo $selected_department == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Par matière :</label>
                <select name="subject" id="subject" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Toutes les matières</option>
                    <?php foreach ($subjects as $subj): ?>
                        <option value="<?php echo $subj['id']; ?>" 
                                <?php echo $selected_subject == $subj['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subj['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2 flex justify-end space-x-4">
                <?php if ($selected_department || $selected_subject): ?>
                    <a href="all-tutors.php" class="inline-flex items-center text-gray-600 hover:text-gray-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Réinitialiser les filtres
                    </a>
                <?php endif; ?>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Résultats -->
    <div class="mb-4 text-gray-600">
        <?php echo count($tutors); ?> tuteur<?php echo count($tutors) > 1 ? 's' : ''; ?> trouvé<?php echo count($tutors) > 1 ? 's' : ''; ?>
    </div>

    <!-- Liste des tuteurs -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($tutors)): ?>
            <div class="md:col-span-2 lg:col-span-3 text-center py-8 text-gray-600">
                Aucun tuteur ne correspond aux critères sélectionnés.
            </div>
        <?php else: ?>
            <?php foreach ($tutors as $tutor): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-[1.02] transition-transform duration-200">
                    <div class="aspect-w-3 aspect-h-2">
                        <img src="uploads/<?php echo htmlspecialchars($tutor['photo'] ?? 'default.jpg'); ?>" 
                             alt="Photo de <?php echo htmlspecialchars($tutor['username']); ?>"
                             class="w-full h-48 object-cover">
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <?php echo htmlspecialchars($tutor['username']); ?>
                            </h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($tutor['department_name']); ?></p>
                        </div>

                        <!-- Matières -->
                        <?php if ($tutor['subjects']): ?>
                            <div class="mb-4">
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach (explode(',', $tutor['subjects']) as $subject): ?>
                                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                            <?php echo htmlspecialchars($subject); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Disponibilités -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Disponibilités :</h4>
                            <?php if (isset($availability_by_tutor[$tutor['id']]) && !empty($availability_by_tutor[$tutor['id']])): ?>
                                <div class="space-y-1 text-sm">
                                    <?php foreach ($availability_by_tutor[$tutor['id']] as $availability): ?>
                                        <div class="flex justify-between text-gray-600">
                                            <span class="font-medium"><?php echo $days_fr[$availability['day_of_week']]; ?></span>
                                            <span><?php echo $availability['start_time']; ?> - <?php echo $availability['end_time']; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-sm text-gray-500 italic">Aucune disponibilité renseignée</p>
                            <?php endif; ?>
                        </div>

                        <!-- Statut et bouton -->
                        <div class="mt-4 flex justify-between items-center">
                            <div class="text-sm">
                                <?php if ($tutor['current_tutees'] >= 4): ?>
                                    <span class="text-red-600 font-medium">Complet</span>
                                <?php else: ?>
                                    <span class="text-green-600 font-medium">
                                        <?php echo 4 - $tutor['current_tutees']; ?> place(s) disponible(s)
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($tutor['current_tutees'] < 4): ?>
                                <a href="contact-tutor.php?id=<?php echo $tutor['id']; ?>" 
                                   class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors text-sm">
                                    Contacter
                                </a>
                            <?php else: ?>
                                <span class="inline-block bg-gray-300 text-gray-600 px-4 py-2 rounded text-sm">
                                    Complet
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
// Soumission automatique du formulaire lors du changement de filtre
document.querySelectorAll('select[name="department"], select[name="subject"]').forEach(select => {
    select.addEventListener('change', () => {
        select.closest('form').submit();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>