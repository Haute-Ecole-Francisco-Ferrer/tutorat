<?php
function renderRelationshipTable($relationships) {
    ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tuteur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutoré</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matière</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($relationships as $rel): ?>
                    <tr class="relationship-row" data-status="<?php echo htmlspecialchars($rel['status']); ?>">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo htmlspecialchars($rel['tutor_firstname'] . ' ' . $rel['tutor_lastname']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo htmlspecialchars($rel['tutee_firstname'] . ' ' . $rel['tutee_lastname']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo htmlspecialchars($rel['subject_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusClass($rel['status']); ?>">
                                <?php echo getStatusLabel($rel['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo formatDate($rel['created_at']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button onclick="showDetails(<?php echo htmlspecialchars(json_encode($rel)); ?>)"
                                    class="text-blue-600 hover:text-blue-900">
                                Détails
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}