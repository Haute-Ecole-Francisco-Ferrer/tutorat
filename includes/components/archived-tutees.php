<?php
require_once __DIR__ . '/../utils/logging.php';

function renderArchivedTutees($archived_relationships) {
    debug_log('archived-tutees', "Rendering archived tutees", ['count' => count($archived_relationships)]);
    
    if (empty($archived_relationships)) {
        debug_log('archived-tutees', "No archived relationships to display");
        return;
    }
    ?>
    <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
        <h2 class="text-xl font-bold mb-4">Tutorats archivés récents</h2>
        <div class="space-y-4">
            <?php foreach ($archived_relationships as $archived): ?>
                <div class="border rounded-lg p-4 bg-gray-50">
                    <div class="flex items-start space-x-4">
                        <div class="flex-grow">
                            <h3 class="font-medium">
                                <?php echo htmlspecialchars($archived['firstname'] . ' ' . $archived['lastname']); ?>
                            </h3>
                            <p class="text-gray-600">
                                <strong>Matière :</strong> <?php echo htmlspecialchars($archived['subject_name']); ?>
                            </p>
                            <p class="text-sm text-gray-500">
                                Du <?php echo date('d/m/Y', strtotime($archived['created_at'])); ?>
                                au <?php echo date('d/m/Y', strtotime($archived['archived_at'])); ?>
                            </p>
                            <?php if ($archived['archive_reason']): ?>
                                <div class="mt-2 text-sm text-gray-600">
                                    <strong>Raison de fin :</strong><br>
                                    <?php echo nl2br(htmlspecialchars($archived['archive_reason'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    debug_log('archived-tutees', "Finished rendering archived tutees");
}
?>