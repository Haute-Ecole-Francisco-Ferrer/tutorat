<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth/admin-auth.php';
require_once '../includes/components/admin/relationship-table.php';
require_once '../includes/components/admin/relationship-modal.php';

// Verify admin authentication
checkAdminAuth();

$currentPage = 'admin-dashboard';
$pageTitle = 'Tableau de bord administrateur';

$db = Database::getInstance()->getConnection();

// Get admin's department
$stmt = $db->prepare("
    SELECT d.id, d.name 
    FROM departments d 
    JOIN admins a ON d.id = a.department_id 
    WHERE a.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$department = $stmt->fetch();

// Get all tutoring relationships for admin's department
$stmt = $db->prepare("
    SELECT 
        tr.*,
        ut.firstname as tutor_firstname,
        ut.lastname as tutor_lastname,
        ute.firstname as tutee_firstname,
        ute.lastname as tutee_lastname,
        s.name as subject_name,
        tr.status,
        tr.created_at,
        tr.archived_at
    FROM tutoring_relationships tr
    JOIN tutors t ON tr.tutor_id = t.id
    JOIN users ut ON t.user_id = ut.id
    JOIN tutees te ON tr.tutee_id = te.id
    JOIN users ute ON te.user_id = ute.id
    JOIN subjects s ON tr.subject_id = s.id
    WHERE ut.department_id = ?
    ORDER BY tr.created_at DESC
");
$stmt->execute([$department['id']]);
$relationships = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Relations de tutorat - <?php echo htmlspecialchars($department['name']); ?></h1>
        </div>

        <!-- Filters -->
        <div class="mb-6">
            <div class="flex gap-4">
                <button class="filter-btn active" data-status="all">Tous</button>
                <button class="filter-btn" data-status="pending">En attente</button>
                <button class="filter-btn" data-status="accepted">Actifs</button>
                <button class="filter-btn" data-status="archived">Archivés</button>
            </div>
        </div>

        <?php 
        renderRelationshipTable($relationships);
        renderRelationshipModal();
        ?>
    </div>
</div>

<script src="js/dashboard.js"></script>

<?php require_once '../includes/footer.php'; ?>