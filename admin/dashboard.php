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

    // Get all departments
    $stmt = $db->prepare("SELECT * FROM departments ORDER BY name");
    $stmt->execute();
    $departments = $stmt->fetchAll();

    // Get relationships for each department
    $relationships_by_dept = [];
    foreach ($departments as $dept) {
        // Get all relationships (active and archived) for each department
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
        $stmt->execute([$dept['id'], $dept['id']]);
        $relationships = $stmt->fetchAll();
        
        if (!empty($relationships)) {
            $relationships_by_dept[$dept['id']] = [
                'name' => $dept['name'],
                'relationships' => $relationships
            ];
        }
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$currentPage = 'admin-dashboard';
$pageTitle = 'Tableau de bord administrateur';

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-8">Tableau de bord administrateur</h1>

    <!-- Filters -->
    <div class="mb-6">
        <div class="flex gap-4">
            <button class="filter-btn active bg-blue-500 text-white px-4 py-2 rounded-md transition-colors duration-200" data-status="all">Tous</button>
            <button class="filter-btn px-4 py-2 rounded-md transition-colors duration-200 hover:bg-gray-100" data-status="pending">En attente</button>
            <button class="filter-btn px-4 py-2 rounded-md transition-colors duration-200 hover:bg-gray-100" data-status="accepted">Actifs</button>
            <button class="filter-btn px-4 py-2 rounded-md transition-colors duration-200 hover:bg-gray-100" data-status="archived">Archivés</button>
        </div>
    </div>

    <!-- Department Sections -->
    <?php foreach ($relationships_by_dept as $dept_id => $dept_data): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">
                    <?php echo htmlspecialchars($dept_data['name']); ?>
                    (<?php echo count($dept_data['relationships']); ?> relations)
                </h2>
            </div>
            <?php renderRelationshipTable($dept_data['relationships']); ?>
        </div>
    <?php endforeach; ?>

    <?php if (empty($relationships_by_dept)): ?>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <p class="text-center text-gray-600">Aucune relation de tutorat trouvée.</p>
        </div>
    <?php endif; ?>

    <?php renderRelationshipModal(); ?>
</div>

<!-- Load JavaScript modules -->
<script type="module">
    import { initializeFilters } from './js/filters.js';
    import { initializeModal } from './js/modal.js';
    import { updateStatus } from './js/status.js';

    // Initialize all modules when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        initializeFilters();
        initializeModal();
        
        // Make updateStatus available globally
        window.updateStatus = updateStatus;
    });
</script>

<?php require_once '../includes/footer.php'; ?>