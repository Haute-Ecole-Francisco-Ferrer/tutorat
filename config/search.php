<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$currentPage = 'search';
$pageTitle = 'Recherche';

$db = Database::getInstance()->getConnection();
$departments = get_departments($db);
$subjects = get_subjects($db);

// Initialisation des variables de recherche
$search_department = $_GET['department_id'] ?? '';
$search_subject = $_GET['subject_id'] ?? '';
$search_day = $_GET['day'] ?? '';
$search_results = [];

// Traitement de la recherche
if (!empty($_GET['search'])) {
    $query = "
        SELECT DISTINCT 
            u.id,
            u.firstname,
            u.lastname,
            u.photo,
            u.study_level,
            d.name as department_name,
            u.section,
            GROUP_CONCAT(DISTINCT s.name) as subjects,
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
        LEFT JOIN availability a ON u.id = a.user_id
        WHERE u.user_type = 'tutor'
    ";

    $params = [];
    $conditions = [];

    // Filtre par département
    if (!empty($search_department)) {
        $conditions[] = "u.department_id = ?";
        $params[] = $search_department;
    }

    // Filtre par matière
    if (!empty($search_subject)) {
        $conditions[] = "s.id = ?";
        $params[] = $search_subject;
    }

    // Filtre par jour de disponibilité
    if (!empty($search_day)) {
        $conditions[] = "a.day_of_week = ?";
        $params[] = $search_day;
    }

    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $query .= " GROUP BY u.id";

    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $search_results = $stmt->fetchAll();
    } catch (PDOException $e) {
        // En production, logger l'erreur au lieu de l'afficher
        // error_log($e->getMessage());
    }
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Formulaire de recherche -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Rechercher un tuteur</h2>
            
            <form method="GET" class="grid md:grid-cols-4 gap-4">
                <!-- Département -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Département :</label>
                    <select id="department_id" name="department_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous les départements</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo $department['id']; ?>" 
                                    <?php echo $search_department == $department['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($department['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Matière -->
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-1">Matière :</label>
                    <select id="subject_id" name="subject_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Toutes les matières</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>" 
                                    <?php echo $search_subject == $subject['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jour -->
                <div>
                    <label for="day" class="block text-sm font-medium text-gray-700 mb-1">Disponibilité :</label>
                    <select id="day" name="day" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous les jours</option>
                        <option value="Monday" <?php echo $search_day === 'Monday' ? 'selected' : ''; ?>>Lundi</option>
                        <option value="Tuesday" <?php echo $search_day === 'Tuesday' ? 'selected' : ''; ?>>Mardi</option>
                        <option value="Wednesday" <?php echo $search_day === 'Wednesday' ? 'selected' : ''; ?>>Mercredi</option>
                        <option value="Thursday" <?php echo $search_day === 'Thursday' ? 'selected' : ''; ?>>Jeudi</option>
                        <option value="Friday" <?php echo $search_day === 'Friday' ? 'selected' : ''; ?>>Vendredi</option>
                        <option value="Saturday" <?php echo $search_day === 'Saturday' ? 'selected' : ''; ?>>Samedi</option>
                        <option value="Sunday" <?php echo $search_day === 'Sunday' ? 'selected' : ''; ?>>Dimanche</option>
                    </select>
                </div>

                <!-- Bouton de recherche -->
                <div class="flex items-end">
                    <button type="submit" name="search" value="1" 
                            class="w-full bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Rechercher
                    </button>
                </div>
            </form>
        </div>

        <!-- Résultats de recherche -->
        <?php if (!empty($_GET['search'])): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($search_results)): ?>
                    <div class="col-span-full text-center py-8 text-gray-600">
                        Aucun tuteur ne correspond à vos critères de recherche.
                    </div>
                <?php else: ?>
                    <?php foreach ($search_results as $tutor): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-6">
                                <!-- Photo et informations de base -->
                                <div class="flex items-start space-x-4 mb-4">
                                    <div class="flex-shrink-0">
                                        <img src="uploads/<?php echo htmlspecialchars($tutor['photo'] ?? 'default.jpg'); ?>" 
                                             alt="Photo de <?php echo htmlspecialchars($tutor['firstname']); ?>"
                                             class="w-20 h-20 rounded-full object-cover">
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($tutor['firstname'] . ' ' . $tutor['lastname']); ?>
                                        </h3>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($tutor['department_name']); ?></p>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($tutor['section']); ?></p>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($tutor['study_level']); ?></p>
                                    </div>
                                </div>

                                <!-- Matières -->
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

                                <!-- Statut et action -->
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
                                        <a href="request-tutoring.php?tutor_id=<?php echo $tutor['id']; ?>" 
                                           class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors text-sm">
                                            Demander du tutorat
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
