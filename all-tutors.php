<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/queries/tutor-queries.php';
require_once 'includes/components/tutor-card.php';
require_once 'includes/utils/department-colors.php';

session_start();
$currentPage = 'all-tutors';
$pageTitle = 'Tous les Tuteurs';

$db = Database::getInstance()->getConnection();

// Get departments and subjects for filters
$departments = get_departments($db);
$subjects = get_subjects($db);

// Get filter parameters
$selected_department = isset($_GET['department']) ? (int)$_GET['department'] : 0;
$selected_subject = isset($_GET['subject']) ? (int)$_GET['subject'] : 0;

// If user is a tutee, force their department filter
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'tutee') {
    $stmt = $db->prepare("SELECT department_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $selected_department = $user['department_id'];
}

try {
    // Get filtered tutors
    $tutors = getFilteredTutors($db, $selected_department, $selected_subject);
    
    // Get availabilities for tutors
    $tutor_ids = array_column($tutors, 'id');
    $availability_by_tutor = getTutorsAvailabilities($db, $tutor_ids);
} catch (PDOException $e) {
    $tutors = [];
    $availability_by_tutor = [];
}

require_once 'includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Filtrer les tuteurs</h2>
        <form action="" method="GET" class="grid md:grid-cols-2 gap-4">
            <?php if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutee'): ?>
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Par département :</label>
                    <select name="department" id="department" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous les départements</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" 
                                    <?php echo $selected_department == $dept['id'] ? 'selected' : ''; ?>
                                    style="background-color: <?php echo getDepartmentColor($dept['id']); ?>20; color: <?php echo getDepartmentColor($dept['id']); ?>;">
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Par matière :</label>
                <select name="subject" id="subject" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Toutes les matières</option>
                    <?php foreach ($subjects as $subj): ?>
                        <option value="<?php echo $subj['id']; ?>" 
                                <?php echo $selected_subject == $subj['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subj['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2 flex justify-end space-x-4">
                <?php if ($selected_subject): ?>
                    <a href="all-tutors.php<?php echo $selected_department ? '?department=' . $selected_department : ''; ?>" 
                       class="inline-flex items-center text-gray-600 hover:text-gray-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Réinitialiser les filtres
                    </a>
                <?php endif; ?>
                <button type="submit" class="bg-slate-700 text-white px-6 py-2 rounded-md hover:bg-slate-800 transition-colors">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Results count -->
    <div class="mb-4 text-gray-600">
        <?php echo count($tutors); ?> tuteur<?php echo count($tutors) > 1 ? 's' : ''; ?> trouvé<?php echo count($tutors) > 1 ? 's' : ''; ?>
    </div>

    <!-- Tutors Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($tutors)): ?>
            <div class="md:col-span-2 lg:col-span-3 text-center py-8 text-gray-600">
                Aucun tuteur ne correspond aux critères sélectionnés.
            </div>
        <?php else: ?>
            <?php foreach ($tutors as $tutor): ?>
                <?php renderTutorCard($tutor, $availability_by_tutor); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
document.querySelectorAll('select[name="department"], select[name="subject"]').forEach(select => {
    select.addEventListener('change', () => {
        select.closest('form').submit();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
