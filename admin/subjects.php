<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth/admin-auth.php';

// Verify admin authentication
checkAdminAuth();

$db = Database::getInstance()->getConnection();
$currentPage = 'subjects';
$pageTitle = 'Gestion des matières';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();

        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $name = trim($_POST['name']);
                    $department_id = filter_var($_POST['department_id'], FILTER_VALIDATE_INT);
                    
                    if (empty($name) || !$department_id) {
                        throw new Exception("Données invalides.");
                    }

                    // Check if subject already exists in this department
                    $stmt = $db->prepare("
                        SELECT id FROM subjects 
                        WHERE name = ? AND department_id = ?
                    ");
                    $stmt->execute([$name, $department_id]);
                    if ($stmt->rowCount() > 0) {
                        throw new Exception("Cette matière existe déjà dans ce département.");
                    }

                    // Add new subject
                    $stmt = $db->prepare("
                        INSERT INTO subjects (name, department_id) 
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$name, $department_id]);
                    $_SESSION['success_message'] = "La matière a été ajoutée avec succès.";
                    break;

                case 'edit':
                    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
                    $name = trim($_POST['name']);
                    $department_id = filter_var($_POST['department_id'], FILTER_VALIDATE_INT);
                    
                    if (!$id || empty($name) || !$department_id) {
                        throw new Exception("Données invalides.");
                    }

                    // Update subject
                    $stmt = $db->prepare("
                        UPDATE subjects 
                        SET name = ?, department_id = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $department_id, $id]);
                    $_SESSION['success_message'] = "La matière a été mise à jour avec succès.";
                    break;

                case 'delete':
                    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
                    if (!$id) {
                        throw new Exception("ID de matière invalide.");
                    }

                    // Check if subject is being used
                    $stmt = $db->prepare("
                        SELECT COUNT(*) FROM tutor_subjects 
                        WHERE subject_id = ?
                    ");
                    $stmt->execute([$id]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception("Cette matière ne peut pas être supprimée car elle est utilisée par des tuteurs.");
                    }

                    // Delete subject
                    $stmt = $db->prepare("DELETE FROM subjects WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['success_message'] = "La matière a été supprimée avec succès.";
                    break;
            }
        }

        $db->commit();
        header('Location: subjects.php');
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error_message'] = $e->getMessage();
    }
}

// Get all departments
$stmt = $db->prepare("
    SELECT d.* 
    FROM departments d
    ORDER BY d.name
");
$stmt->execute();
$departments = $stmt->fetchAll();

// Get subjects for all departments
$stmt = $db->prepare("
    SELECT s.*, d.name as department_name,
           (SELECT COUNT(*) FROM tutor_subjects WHERE subject_id = s.id) as tutor_count
    FROM subjects s
    JOIN departments d ON s.department_id = d.id
    ORDER BY d.name, s.name
");
$stmt->execute();
$subjects = $stmt->fetchAll();

// Group subjects by department
$subjects_by_dept = [];
foreach ($subjects as $subject) {
    $subjects_by_dept[$subject['department_name']][] = $subject;
}

require_once '../includes/header.php';

?>

<div class="container mx-auto px-4 py-8">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestion des matières</h1>
        <button onclick="openAddModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Ajouter une matière
        </button>
    </div>

    <?php foreach ($departments as $department): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($department['name']); ?></h2>
            </div>
            <div class="p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tuteurs</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        $dept_subjects = $subjects_by_dept[$department['name']] ?? [];
                        if (empty($dept_subjects)): 
                        ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                    Aucune matière n'a été ajoutée.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dept_subjects as $subject): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($subject['name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo $subject['tutor_count']; ?> tuteur(s)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($subject)); ?>)" 
                                                class="text-blue-600 hover:text-blue-900 mr-3">
                                            Modifier
                                        </button>
                                        <?php if ($subject['tutor_count'] === 0): ?>
                                            <button onclick="confirmDelete(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars($subject['name']); ?>')" 
                                                    class="text-red-600 hover:text-red-900">
                                                Supprimer
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Add Subject Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-xl font-bold mb-4">Ajouter une matière</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Département
                    </label>
                    <select id="department_id" name="department_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>">
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nom de la matière
                    </label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeAddModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <h2 class="text-xl font-bold mb-4">Modifier la matière</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div>
                    <label for="edit_department_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Département
                    </label>
                    <select id="edit_department_id" name="department_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>">
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nom de la matière
                    </label>
                    <input type="text" id="edit_name" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" class="hidden">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.getElementById('name').value = '';
}

function openEditModal(subject) {
    document.getElementById('edit_id').value = subject.id;
    document.getElementById('edit_name').value = subject.name;
    document.getElementById('edit_department_id').value = subject.department_id;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function confirmDelete(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la matière "${name}" ?`)) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    if (event.target === addModal) {
        closeAddModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>