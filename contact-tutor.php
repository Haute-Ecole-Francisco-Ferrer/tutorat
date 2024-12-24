<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

// Vérifier si un ID de tuteur est fourni
if (!isset($_GET['id'])) {
    header('Location: all-tutors.php');
    exit();
}

$currentPage = 'contact-tutor';
$pageTitle = 'Contacter un tuteur';
$error_message = "";
$success_message = "";

$db = Database::getInstance()->getConnection();
$tutor_id = (int)$_GET['id'];

// Récupérer les informations du tuteur
try {
    $stmt = $db->prepare("
        SELECT 
            u.*,
            d.name as department_name,
            GROUP_CONCAT(DISTINCT s.name) as subjects,
            GROUP_CONCAT(DISTINCT s.id) as subject_ids,
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
        WHERE u.id = ? AND u.user_type = 'tutor'
        GROUP BY u.id
    ");
    
    $stmt->execute([$tutor_id]);
    $tutor = $stmt->fetch();

    if (!$tutor) {
        header('Location: all-tutors.php');
        exit();
    }

    // Vérifier si le tuteur est dans le même département
    if ($tutor['department_id'] != $_SESSION['department_id']) {
        header('Location: all-tutors.php');
        $error_message = "Vous ne pouvez contacter que les tuteurs de votre département.";
        exit();
    }

    // Vérifier si le tuteur est déjà complet
    if ($tutor['current_tutees'] >= 4) {
        header('Location: all-tutors.php');
        $error_message = "Ce tuteur a déjà atteint son nombre maximum de tutorés.";
        exit();
    }

} catch (PDOException $e) {
    header('Location: all-tutors.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = filter_var($_POST['subject_id'], FILTER_VALIDATE_INT);
    $message = trim($_POST['message']);

    if (!$subject_id) {
        $error_message = "Veuillez sélectionner une matière.";
    } elseif (empty($message)) {
        $error_message = "Veuillez écrire un message.";
    } else {
        try {
            // Vérifier si une demande existe déjà
            $stmt = $db->prepare("
                SELECT id FROM tutoring_relationships 
                WHERE tutor_id = ? AND tutee_id = ? AND subject_id = ? 
                AND status IN ('pending', 'accepted')
            ");
            $stmt->execute([$tutor['id'], $_SESSION['user_id'], $subject_id]);
            
            if ($stmt->rowCount() > 0) {
                $error_message = "Une demande est déjà en cours pour cette matière avec ce tuteur.";
            } else {
                // Créer la demande
                $stmt = $db->prepare("
                    INSERT INTO tutoring_relationships (tutor_id, tutee_id, subject_id, message, status, created_at)
                    VALUES (?, ?, ?, ?, 'pending', NOW())
                ");
                $stmt->execute([$tutor['id'], $_SESSION['user_id'], $subject_id, $message]);
                
                $success_message = "Votre demande a été envoyée au tuteur.";
            }
        } catch (PDOException $e) {
            $error_message = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Carte du tuteur -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="md:flex">
                <div class="md:flex-shrink-0">
                    <img src="uploads/<?php echo htmlspecialchars($tutor['photo'] ?? 'default.jpg'); ?>" 
                         alt="Photo de <?php echo htmlspecialchars($tutor['username']); ?>"
                         class="h-48 w-full md:w-48 object-cover">
                </div>
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        <?php echo htmlspecialchars($tutor['username']); ?>
                    </h2>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($tutor['department_name']); ?></p>
                    
                    <?php if ($tutor['subjects']): ?>
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Matières :</h3>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach (explode(',', $tutor['subjects']) as $subject): ?>
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                        <?php echo htmlspecialchars($subject); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <p class="text-sm text-gray-600">
                        <span class="font-medium"><?php echo 4 - $tutor['current_tutees']; ?></span> place(s) disponible(s)
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulaire de contact -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6">Envoyer une demande de tutorat</h3>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Matière souhaitée :
                    </label>
                    <select name="subject_id" id="subject_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sélectionnez une matière</option>
                        <?php
                        $subject_ids = explode(',', $tutor['subject_ids']);
                        $stmt = $db->prepare("SELECT id, name FROM subjects WHERE id IN (" . implode(',', $subject_ids) . ")");
                        $stmt->execute();
                        $subjects = $stmt->fetchAll();
                        
                        foreach ($subjects as $subject):
                        ?>
                            <option value="<?php echo $subject['id']; ?>">
                                <?php echo htmlspecialchars($subject['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                        Message pour le tuteur :
                    </label>
                    <textarea name="message" id="message" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Expliquez brièvement vos besoins et vos attentes..."></textarea>
                </div>

                <div class="flex justify-between items-center">
                    <a href="all-tutors.php" 
                       class="text-gray-600 hover:text-gray-800">
                        Retour à la liste
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Envoyer la demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>
</body>
</html>
