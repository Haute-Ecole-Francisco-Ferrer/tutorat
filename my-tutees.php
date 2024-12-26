<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/queries/tutoring-queries.php';
require_once 'includes/components/tutoring-requests.php';
require_once 'includes/components/archived-tutees.php';
require_once 'includes/utils/logging.php';

debug_log('my-tutees', "Page load started");

// Verify user is logged in and is a tutor
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tutor') {
    debug_log('my-tutees', "Authentication failed - redirecting to login");
    header('Location: login.php');
    exit;
}

try {
    debug_log('my-tutees', "Getting database connection");
    $db = Database::getInstance()->getConnection();

    // Get tutor ID
    debug_log('my-tutees', "Getting tutor ID for user: " . $_SESSION['user_id']);
    $tutor_id = getTutorId($db, $_SESSION['user_id']);
    if (!$tutor_id) {
        debug_log('my-tutees', "Tutor ID not found - redirecting to index");
        header('Location: index.php');
        exit;
    }

    debug_log('my-tutees', "Fetching tutoring data for tutor: " . $tutor_id);
    
    // Get tutoring data
    $pending_requests = getTutorPendingRequests($db, $tutor_id);
    $active_tutees = getTutorActiveTutees($db, $tutor_id);
    $archived_relationships = getTutorArchivedRelationships($db, $tutor_id);

    debug_log('my-tutees', "Data fetched successfully", [
        'pending_count' => count($pending_requests),
        'active_count' => count($active_tutees),
        'archived_count' => count($archived_relationships)
    ]);

} catch (Exception $e) {
    debug_log('my-tutees', "Error occurred: " . $e->getMessage());
    die("Une erreur est survenue : " . $e->getMessage());
}

$currentPage = 'my-tutees';
$pageTitle = 'Mes tutorés';

debug_log('my-tutees', "Loading header");
require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <?php debug_log('my-tutees', "Rendering messages"); ?>
        
        <!-- Success/Error Messages -->
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

        <?php debug_log('my-tutees', "Rendering pending requests section"); ?>
        <!-- Pending Requests Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Demandes en attente</h2>
            <?php renderPendingRequests($pending_requests); ?>
        </div>

        <?php debug_log('my-tutees', "Rendering active tutees section"); ?>
        <!-- Active Tutees Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Mes tutorés actuels</h2>
            <?php renderActiveTutees($active_tutees); ?>
        </div>

        <?php debug_log('my-tutees', "Rendering archived tutees section"); ?>
        <!-- Archived Tutees Section -->
        <?php 
        debug_log('my-tutees', "About to render archived relationships", [
            'count' => count($archived_relationships),
            'first_archived' => !empty($archived_relationships) ? $archived_relationships[0] : null
        ]);
        renderArchivedTutees($archived_relationships); 
        ?>
    </div>
</div>

<?php debug_log('my-tutees', "Rendering archive modal"); ?>
<!-- Archive Modal -->
<div id="archiveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-bold mb-4">Mettre fin au tutorat</h3>
                <form action="archive-relationship.php" method="POST">
                    <input type="hidden" id="relationshipId" name="relationship_id" value="">
                    <div class="mb-4">
                        <label for="archive_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Raison de la fin du tutorat :
                        </label>
                        <textarea id="archive_reason" name="archive_reason" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeArchiveModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Confirmer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openArchiveModal(relationshipId) {
    document.getElementById('relationshipId').value = relationshipId;
    document.getElementById('archiveModal').classList.remove('hidden');
}

function closeArchiveModal() {
    document.getElementById('archiveModal').classList.add('hidden');
    document.getElementById('archive_reason').value = '';
}
</script>

<?php 
debug_log('my-tutees', "Loading footer");
require_once 'includes/footer.php'; 
debug_log('my-tutees', "Page load completed");
?>