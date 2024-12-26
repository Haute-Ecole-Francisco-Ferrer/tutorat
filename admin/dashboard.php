<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth/admin-auth.php';
require_once '../includes/components/admin/relationship-table.php';
require_once '../includes/components/admin/relationship-modal.php';

// Verify admin authentication
checkAdminAuth();

try {
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

    if (!$department) {
        throw new Exception('Department not found');
    }

    // Debug department info
    echo "<!-- Admin Department ID: " . $department['id'] . " -->\n";

    // Get all relationships (active and archived) for admin's department
    $stmt = $db->prepare("
        (SELECT 
            tr.*,
            ut.firstname as tutor_firstname,
            ut.lastname as tutor_lastname,
            ut.department_id as tutor_dept_id,
            ute.firstname as tutee_firstname,
            ute.lastname as tutee_lastname,
            s.name as subject_name
        FROM tutoring_relationships tr
        JOIN tutors t ON tr.tutor_id = t.id
        JOIN users ut ON t.user_id = ut.id
        JOIN tutees te ON tr.tutee_id = te.id
        JOIN users ute ON te.user_id = ute.id
        JOIN subjects s ON tr.subject_id = s.id
        WHERE ut.department_id = ?)
        
        UNION ALL
        
        (SELECT 
            tra.id,
            tra.tutor_id,
            tra.tutee_id,
            tra.subject_id,
            'archived' as status,
            tra.created_at,
            tra.archived_at as updated_at,
            tra.message,
            tra.tutor_response,
            tra.archived_at,
            tra.archive_reason,
            ut.firstname as tutor_firstname,
            ut.lastname as tutor_lastname,
            ut.department_id as tutor_dept_id,
            ute.firstname as tutee_firstname,
            ute.lastname as tutee_lastname,
            s.name as subject_name
        FROM tutoring_relationships_archive tra
        JOIN tutors t ON tra.tutor_id = t.id
        JOIN users ut ON t.user_id = ut.id
        JOIN tutees te ON tra.tutee_id = te.id
        JOIN users ute ON te.user_id = ute.id
        JOIN subjects s ON tra.subject_id = s.id
        WHERE ut.department_id = ?)
        
        ORDER BY created_at DESC
    ");
    $stmt->execute([$department['id'], $department['id']]);
    $relationships = $stmt->fetchAll();

    // Debug relationship counts
    echo "<!-- Total relationships found: " . count($relationships) . " -->\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$currentPage = 'admin-dashboard';
$pageTitle = 'Tableau de bord administrateur';

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">
                Relations de tutorat - <?php echo htmlspecialchars($department['name']); ?>
                (<?php echo count($relationships); ?> relations)
            </h1>
        </div>

        <!-- Filters -->
        <div class="mb-6">
            <div class="flex gap-4">
                <button class="filter-btn active bg-blue-500 text-white px-4 py-2 rounded-md transition-colors duration-200" data-status="all">Tous</button>
                <button class="filter-btn px-4 py-2 rounded-md transition-colors duration-200 hover:bg-gray-100" data-status="pending">En attente</button>
                <button class="filter-btn px-4 py-2 rounded-md transition-colors duration-200 hover:bg-gray-100" data-status="accepted">Actifs</button>
                <button class="filter-btn px-4 py-2 rounded-md transition-colors duration-200 hover:bg-gray-100" data-status="archived">Archivés</button>
            </div>
        </div>

        <?php 
        // Render relationship table
        renderRelationshipTable($relationships);
        
        // Render relationship modal
        renderRelationshipModal();
        ?>
    </div>
</div>

<script type="module" src="js/dashboard.js"></script>

<?php require_once '../includes/footer.php'; ?>