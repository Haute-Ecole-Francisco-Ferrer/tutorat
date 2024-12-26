<?php
require_once __DIR__ . '/table/header.php';
require_once __DIR__ . '/table/row.php';
require_once __DIR__ . '/table/status.php';

function renderRelationshipTable($relationships) {
    ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <?php renderTableHeader(); ?>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($relationships as $rel): ?>
                    <?php renderTableRow($rel); ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}