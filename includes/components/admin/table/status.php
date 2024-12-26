<?php
function renderStatusCell($rel) {
    ?>
    <div class="flex items-center gap-2">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
            <?php echo getStatusClass($rel['status']); ?>">
            <?php echo getStatusLabel($rel['status']); ?>
        </span>
        <select onchange="updateStatus('<?php echo $rel['id']; ?>', this.value)"
                class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            <option value="pending" <?php echo $rel['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
            <option value="accepted" <?php echo $rel['status'] === 'accepted' ? 'selected' : ''; ?>>Actif</option>
            <option value="archived" <?php echo $rel['status'] === 'archived' ? 'selected' : ''; ?>>Archivé</option>
        </select>
    </div>
    <?php
}

function getStatusClass($status) {
    switch ($status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'accepted':
            return 'bg-green-100 text-green-800';
        case 'archived':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'pending':
            return 'En attente';
        case 'accepted':
            return 'Actif';
        case 'archived':
            return 'Archivé';
        default:
            return $status;
    }
}