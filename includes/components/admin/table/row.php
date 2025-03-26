<?php
require_once __DIR__ . '/status.php';

function renderTableRow($rel) {
    ?>
    <tr class="relationship-row" 
        data-relationship-id="<?php echo $rel['id']; ?>"
        data-status="<?php echo htmlspecialchars($rel['status']); ?>">
        <td class="px-6 py-4 whitespace-nowrap">
            <?php echo htmlspecialchars($rel['tutor_firstname'] . ' ' . $rel['tutor_lastname']); ?>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <?php echo htmlspecialchars($rel['tutee_firstname'] . ' ' . $rel['tutee_lastname']); ?>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <?php echo htmlspecialchars($rel['subject_name']); ?>
        </td>
        <td class="px-6 py-4 whitespace-nowrap status-cell">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                <?php echo getStatusClass($rel['status']); ?>">
                <?php echo getStatusLabel($rel['status']); ?>
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <?php echo formatDate($rel['created_at']); ?>
        </td>
        <td class="px-6 py-4 whitespace-nowrap flex items-center gap-4">
            <button onclick="showDetails(<?php echo htmlspecialchars(json_encode($rel)); ?>)"
                    class="text-blue-600 hover:text-blue-900">
                Détails
            </button>
            <?php if (!empty($rel['id'])): ?>
                <select onchange="updateStatus('<?php echo $rel['id']; ?>', this.value)"
                        class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="pending" <?php echo $rel['status'] === 'pending' || $rel['status'] === '' ? 'selected' : ''; ?>>En attente</option>
                    <option value="accepted" <?php echo $rel['status'] === 'accepted' ? 'selected' : ''; ?>>Actif</option>
                    <option value="archived" <?php echo $rel['status'] === 'archived' ? 'selected' : ''; ?>>Archivé</option>
                </select>
            <?php else: ?>
                <span class="text-gray-500 text-sm italic">ID manquant</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}
