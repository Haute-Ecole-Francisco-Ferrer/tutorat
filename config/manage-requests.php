<?php
require_once 'includes/init.php';
require_once 'includes/auth_check.php';

// Vérifier si l'utilisateur est un tuteur
if ($_SESSION['user_type'] !== 'tutor') {
    header('Location: index.php');
    exit;
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        // Vérifier d'abord le nombre de tutorés actuels
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tutor_requests WHERE tutor_id = ? AND status = 'accepted'");
        $stmt->execute([$_SESSION['user_id']]);
        $current_tutees = $stmt->fetchColumn();

        if ($current_tutees >= 4) {
            $_SESSION['error'] = "Vous avez déjà atteint le maximum de 4 tutorés.";
        } else {
            $stmt = $pdo->prepare("UPDATE tutor_requests SET status = 'accepted' WHERE id = ? AND tutor_id = ?");
            if ($stmt->execute([$request_id, $_SESSION['user_id']])) {
                $_SESSION['success'] = "La demande a été acceptée.";
            }
        }
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE tutor_requests SET status = 'rejected' WHERE id = ? AND tutor_id = ?");
        if ($stmt->execute([$request_id, $_SESSION['user_id']])) {
            $_SESSION['success'] = "La demande a été refusée.";
        }
    }

    header('Location: manage-requests.php');
    exit;
}

// Récupérer les demandes en attente
$stmt = $pdo->prepare("
    SELECT r.*, u.firstname, u.lastname, u.email, u.level, d.name as department_name
    FROM tutor_requests r
    JOIN users u ON r.tutee_id = u.id
    LEFT JOIN departments d ON u.department_id = d.id
    WHERE r.tutor_id = ? AND r.status = 'pending'
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$pending_requests = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Gérer les demandes de tutorat</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($pending_requests)): ?>
        <div class="grid gap-6">
            <?php foreach ($pending_requests as $request): ?>
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold">
                                <?= htmlspecialchars($request['firstname'] . ' ' . $request['lastname']) ?>
                            </h3>
                            <p class="text-gray-600"><?= htmlspecialchars($request['email']) ?></p>
                            <p class="text-gray-600">
                                Niveau : <?= htmlspecialchars($request['level']) ?><br>
                                Département : <?= htmlspecialchars($request['department_name']) ?>
                            </p>
                            <div class="mt-2">
                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($request['message'])) ?></p>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">
                                Demande reçue le <?= date('d/m/Y à H:i', strtotime($request['created_at'])) ?>
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <form method="POST" class="inline">
                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                <input type="hidden" name="action" value="accept">
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Accepter
                                </button>
                            </form>
                            <form method="POST" class="inline">
                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Refuser
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-600">Vous n'avez aucune demande en attente.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>