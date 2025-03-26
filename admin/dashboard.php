<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth/admin-auth.php';
require_once '../includes/components/admin/relationship-table.php';
require_once '../includes/components/admin/relationship-modal.php';
require_once '../includes/utils/department-colors.php';

// Verify admin authentication
checkAdminAuth();

try {
    $db = Database::getInstance()->getConnection();

    // Get all departments
    $stmt = $db->prepare("SELECT * FROM departments ORDER BY name");
    $stmt->execute();
    $departments = $stmt->fetchAll();

    // Get pending tutors and tutees
    $pending_tutors_by_dept = [];
    $pending_tutees_by_dept = [];
    
    foreach ($departments as $dept) {
        // Get pending tutors for this department
        $stmt = $db->prepare("
            SELECT u.*, 
                   GROUP_CONCAT(s.name) as subjects
            FROM users u
            LEFT JOIN tutors t ON u.id = t.user_id
            LEFT JOIN tutor_subjects ts ON t.id = ts.tutor_id
            LEFT JOIN subjects s ON ts.subject_id = s.id
            WHERE u.user_type = 'tutor' 
            AND u.status = 'pending'
            AND u.department_id = ?
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");
        $stmt->execute([$dept['id']]);
        $pending_tutors = $stmt->fetchAll();
        
        if (!empty($pending_tutors)) {
            $pending_tutors_by_dept[$dept['id']] = [
                'name' => $dept['name'],
                'tutors' => $pending_tutors
            ];
        }
        
        // Get pending tutees for this department
        $stmt = $db->prepare("
            SELECT u.*
            FROM users u
            WHERE u.user_type = 'tutee' 
            AND u.status = 'pending'
            AND u.department_id = ?
            ORDER BY u.created_at DESC
        ");
        $stmt->execute([$dept['id']]);
        $pending_tutees = $stmt->fetchAll();
        
        if (!empty($pending_tutees)) {
            $pending_tutees_by_dept[$dept['id']] = [
                'name' => $dept['name'],
                'tutees' => $pending_tutees
            ];
        }
    }

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

    // Count total pending users
    $total_pending_tutors = 0;
    $total_pending_tutees = 0;
    
    foreach ($pending_tutors_by_dept as $dept_data) {
        $total_pending_tutors += count($dept_data['tutors']);
    }
    
    foreach ($pending_tutees_by_dept as $dept_data) {
        $total_pending_tutees += count($dept_data['tutees']);
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

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-700">Tuteurs en attente</h3>
            <p class="text-3xl font-bold mt-2"><?php echo $total_pending_tutors; ?></p>
            <a href="pending-tutors.php" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Voir tous</a>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-700">Tutorés en attente</h3>
            <p class="text-3xl font-bold mt-2"><?php echo $total_pending_tutees; ?></p>
            <a href="pending-tutees.php" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Voir tous</a>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-700">Relations de tutorat</h3>
            <p class="text-3xl font-bold mt-2"><?php 
                $total_relationships = 0;
                foreach ($relationships_by_dept as $dept_data) {
                    $total_relationships += count($dept_data['relationships']);
                }
                echo $total_relationships;
            ?></p>
            <a href="relationships.php" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Voir toutes les relations</a>
        </div>
    </div>

    <!-- Pending Tutors Section -->
    <?php if (!empty($pending_tutors_by_dept)): ?>
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4">Tuteurs en attente de validation</h2>
            
            <?php foreach ($pending_tutors_by_dept as $dept_id => $dept_data): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-4 border-t-4 <?php echo getDepartmentBorderClass($dept_id); ?>">
                    <h3 class="text-lg font-semibold mb-3 <?php echo getDepartmentTextClass($dept_id); ?>"><?php echo htmlspecialchars($dept_data['name']); ?></h3>
                    <div class="space-y-4">
                        <?php foreach ($dept_data['tutors'] as $tutor): ?>
                            <div class="border rounded-lg p-4" style="border-color: <?php echo getDepartmentColor($dept_id); ?>20;">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium">
                                            <?php echo htmlspecialchars($tutor['firstname'] . ' ' . $tutor['lastname']); ?>
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            Email: <?php echo htmlspecialchars($tutor['email']); ?><br>
                                            Section: <?php echo htmlspecialchars($tutor['section']); ?><br>
                                            Inscrit le: <?php echo date('d/m/Y', strtotime($tutor['created_at'])); ?>
                                        </p>
                                        <?php if (!empty($tutor['subjects'])): ?>
                                            <div class="mt-2">
                                                <p class="text-sm font-medium">Matières:</p>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    <?php foreach (explode(',', $tutor['subjects']) as $subject): ?>
                                                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                                            <?php echo htmlspecialchars($subject); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="pending-tutors.php" class="text-white px-4 py-2 rounded hover:opacity-90" style="background-color: <?php echo getDepartmentColor($dept_id); ?>;">
                                            Gérer
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pending Tutees Section -->
    <?php if (!empty($pending_tutees_by_dept)): ?>
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4">Tutorés en attente de validation</h2>
            
            <?php foreach ($pending_tutees_by_dept as $dept_id => $dept_data): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-4 border-t-4 <?php echo getDepartmentBorderClass($dept_id); ?>">
                    <h3 class="text-lg font-semibold mb-3 <?php echo getDepartmentTextClass($dept_id); ?>"><?php echo htmlspecialchars($dept_data['name']); ?></h3>
                    <div class="space-y-4">
                        <?php foreach ($dept_data['tutees'] as $tutee): ?>
                            <div class="border rounded-lg p-4" style="border-color: <?php echo getDepartmentColor($dept_id); ?>20;">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium">
                                            <?php echo htmlspecialchars($tutee['firstname'] . ' ' . $tutee['lastname']); ?>
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            Email: <?php echo htmlspecialchars($tutee['email']); ?><br>
                                            Section: <?php echo htmlspecialchars($tutee['section']); ?><br>
                                            Inscrit le: <?php echo date('d/m/Y', strtotime($tutee['created_at'])); ?>
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="pending-tutees.php" class="text-white px-4 py-2 rounded hover:opacity-90" style="background-color: <?php echo getDepartmentColor($dept_id); ?>;">
                                            Gérer
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Link to Relationships Page -->
    <div class="mb-6 text-center">
        <a href="relationships.php" class="inline-block bg-slate-700 text-white px-6 py-3 rounded-md hover:bg-slate-800 transition-colors">
            Voir toutes les relations de tutorat
        </a>
    </div>
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
