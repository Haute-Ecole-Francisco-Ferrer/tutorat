<?php
require_once 'config/database.php'; 
require_once 'includes/auth_check.php';

// Vérifier si l'utilisateur est un tutoré
if ($_SESSION['user_type'] !== 'tutee') {
    header('Location: index.php');
    exit;
}

$tutor_id = isset($_GET['tutor_id']) ? (int)$_GET['tutor_id'] : 0;

if (!$tutor_id) {
    header('Location: tutors.php');
    exit;
}

// Vérifier si une demande existe déjà
$stmt = $pdo->prepare("SELECT status FROM tutor_requests WHERE tutee_id = ? AND tutor_id = ?");
$stmt->execute([$_SESSION['user_id'], $tutor_id]);
$existing_request = $stmt->fetch();

// Vérifier le nombre de tuteurs actuels
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tutor_requests WHERE tutee_id = ? AND status = 'accepted'");
$stmt->execute([$_SESSION['user_id']]);
$current_tutors = $stmt->fetchColumn();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($existing_request) {
        $error = "Vous avez déjà envoyé une demande à ce tuteur.";
    } elseif ($current_tutors >= 4) {
        $error = "Vous avez déjà atteint le maximum de 4 tuteurs.";
    } else {
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        
        $stmt = $pdo->prepare("INSERT INTO tutor_requests (tutee_id, tutor_id, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $tutor_id, $message])) {
            $_SESSION['success'] = "Votre demande a été envoyée avec succès.";
            header('Location: tutor-profile.php?id=' . $tutor_id);
            exit;
        } else {
            $error = "Une erreur est survenue lors de l'envoi de la demande.";
        }
    }
}

// Récupérer les informations du tuteur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'tutor'");
$stmt->execute([$tutor_id]);
$tutor = $stmt->fetch();

if (!$tutor) {
    header('Location: tutors.php');
    exit;
}

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Contacter <?= htmlspecialchars($tutor['firstname'] . ' ' . $tutor['lastname']) ?></h1>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if ($existing_request): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            Statut de votre demande : <?= htmlspecialchars($existing_request['status']) ?>
        </div>
    <?php elseif ($current_tutors < 4): ?>
        <form method="POST" class="max-w-lg">
            <div class="mb-4">
                <label for="message" class="block text-sm font-medium text-gray-700">Message pour le tuteur</label>
                <textarea 
                    id="message" 
                    name="message" 
                    rows="4" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    required
                ></textarea>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Envoyer la demande
            </button>
        </form>
    <?php else: ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
            Vous avez atteint le maximum de 4 tuteurs.
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>