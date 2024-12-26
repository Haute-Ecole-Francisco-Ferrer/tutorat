<?php
function renderPendingRequests($pending_requests) {
    if (empty($pending_requests)): ?>
        <p class="text-gray-600 text-center py-4">
            Aucune demande en attente.
        </p>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($pending_requests as $request): ?>
                <div class="border rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <?php renderUserAvatar($request); ?>
                            <?php renderRequestDetails($request); ?>
                        </div>
                        <?php renderRequestActions($request['id']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif;
}

function renderActiveTutees($active_tutees) {
    if (empty($active_tutees)): ?>
        <p class="text-gray-600 text-center py-4">
            Vous n'avez pas encore de tutorés.
        </p>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($active_tutees as $tutee): ?>
                <div class="border rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <?php renderUserAvatar($tutee); ?>
                            <?php renderTuteeDetails($tutee); ?>
                        </div>
                        <button onclick="openArchiveModal(<?php echo $tutee['id']; ?>)" 
                                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            Mettre fin
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif;
}

function renderArchivedRelationships($archived_relationships) {
    if (!empty($archived_relationships)): ?>
        <div class="space-y-4">
            <?php foreach ($archived_relationships as $archived): ?>
                <div class="border rounded-lg p-4 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium">
                                <?php echo htmlspecialchars($archived['firstname'] . ' ' . $archived['lastname']); ?>
                                - <?php echo htmlspecialchars($archived['subject_name']); ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                Du <?php echo date('d/m/Y', strtotime($archived['created_at'])); ?> 
                                au <?php echo date('d/m/Y', strtotime($archived['archived_at'])); ?>
                            </p>
                            <?php if ($archived['archive_reason']): ?>
                                <p class="text-sm text-gray-600 mt-1">
                                    <strong>Raison :</strong> <?php echo htmlspecialchars($archived['archive_reason']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif;
}

function renderUserAvatar($user) {
    ?>
    <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
        <?php if ($user['photo']): ?>
            <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                 alt="Photo de <?php echo htmlspecialchars($user['firstname']); ?>"
                 class="w-full h-full object-cover">
        <?php else: ?>
            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                <span class="text-xl text-gray-500">
                    <?php echo strtoupper(substr($user['firstname'], 0, 1) . substr($user['lastname'], 0, 1)); ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

function renderRequestDetails($request) {
    ?>
    <div>
        <h3 class="font-medium text-lg">
            <?php echo htmlspecialchars($request['firstname'] . ' ' . $request['lastname']); ?>
        </h3>
        <p class="text-gray-600">
            <?php echo htmlspecialchars($request['department_name']); ?> - 
            <?php echo htmlspecialchars($request['study_level']); ?>
        </p>
        <p class="text-gray-600 mt-1">
            <strong>Matière :</strong> <?php echo htmlspecialchars($request['subject_name']); ?>
        </p>
        <p class="text-gray-600 mt-1">
            <strong>Email :</strong> <?php echo htmlspecialchars($request['email']); ?><br>
            <strong>Téléphone :</strong> <?php echo htmlspecialchars($request['phone']); ?>
        </p>
        <?php if ($request['message']): ?>
            <div class="mt-2 p-3 bg-gray-50 rounded">
                <p class="text-sm text-gray-700">
                    <strong>Message du tutoré :</strong><br>
                    <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                </p>
            </div>
        <?php endif; ?>
        <p class="text-sm text-gray-500 mt-2">
            Demande reçue le <?php echo date('d/m/Y à H:i', strtotime($request['created_at'])); ?>
        </p>
    </div>
    <?php
}

function renderRequestActions($request_id) {
    ?>
    <div class="flex-shrink-0">
        <form method="POST" action="process-tutoring-request.php" class="space-y-2">
            <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
            <textarea name="message" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md mb-2"
                      placeholder="Message (optionnel)"></textarea>
            <div class="flex justify-end space-x-2">
                <button type="submit" name="action" value="accept" 
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Accepter
                </button>
                <button type="submit" name="action" value="reject" 
                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    Refuser
                </button>
            </div>
        </form>
    </div>
    <?php
}

function renderTuteeDetails($tutee) {
    ?>
    <div class="flex-grow">
        <h3 class="font-medium text-lg">
            <?php echo htmlspecialchars($tutee['firstname'] . ' ' . $tutee['lastname']); ?>
        </h3>
        <p class="text-gray-600">
            <?php echo htmlspecialchars($tutee['department_name']); ?> - 
            <?php echo htmlspecialchars($tutee['study_level']); ?>
        </p>
        <p class="text-gray-600 mt-1">
            <strong>Matière :</strong> <?php echo htmlspecialchars($tutee['subject_name']); ?>
        </p>
        <div class="mt-3 text-sm text-gray-600">
            <p><strong>Contact :</strong></p>
            <p>Email : <?php echo htmlspecialchars($tutee['email']); ?></p>
            <p>Téléphone : <?php echo htmlspecialchars($tutee['phone']); ?></p>
        </div>
    </div>
    <?php
}
?>