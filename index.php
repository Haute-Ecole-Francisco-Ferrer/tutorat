<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
$currentPage = 'home';
$pageTitle = 'Accueil';

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

// Récupération des 6 derniers tuteurs
$db = Database::getInstance()->getConnection();
$query = "
    SELECT 
        u.id,
        u.firstname,
        u.lastname,
        u.photo,
        u.username,
        d.name as department_name,
        GROUP_CONCAT(DISTINCT s.name) as subjects,
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
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT 6
";

try {
    $stmt = $db->query($query);
    $latest_tutors = $stmt->fetchAll();

    // Récupérer les disponibilités pour tous les tuteurs
    $tutor_ids = array_column($latest_tutors, 'id');
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
    $latest_tutors = [];
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
        <!-- Carte Tuteur -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Devenir Tuteur</h3>
            <p class="text-gray-600 mb-6">
                Vous maîtrisez certaines matières et souhaitez aider d'autres étudiants ? Devenez tuteur !
            </p>
            <a href="register-tutor.php" 
               class="inline-block bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                S'inscrire comme tuteur
            </a>
        </div>

        <!-- Carte Tutoré -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Devenir Tutoré</h3>
            <p class="text-gray-600 mb-6">
                Vous cherchez de l'aide dans certaines matières ? Trouvez un tuteur qui pourra vous accompagner.
            </p>
            <a href="register-tutee.php" 
               class="inline-block bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                S'inscrire comme tutoré
            </a>
        </div>

        <!-- Carte Tous les tuteurs -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Découvrir</h3>
            <p class="text-gray-600 mb-6">
                Parcourez la liste de tous nos tuteurs disponibles et trouvez celui qui vous correspond.
            </p>
            <a href="all-tutors.php" 
               class="inline-block bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                Voir tous les tuteurs
            </a>
        </div>
    </section>

    <!-- Section Derniers tuteurs -->
    <section class="mb-12">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Nos derniers tuteurs</h2>
            <a href="all-tutors.php" class="text-blue-600 hover:underline">Voir tous les tuteurs</a>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($latest_tutors as $tutor): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
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
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>