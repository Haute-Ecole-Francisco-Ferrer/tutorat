<?php
require_once __DIR__ . '/utils/department-colors.php';
?>

<footer class="bg-slate-800 text-white mt-8">
        <div class="container mx-auto px-4 py-6">
            <!-- Légende des couleurs des départements -->
            <div class="mb-4 flex flex-wrap justify-center gap-4">
                <?php
                // Afficher la légende des couleurs pour chaque département
                for ($i = 1; $i <= 5; $i++) {
                    $color = getDepartmentColor($i);
                    $name = getDepartmentName($i);
                    echo '<div class="flex items-center">';
                    echo '<div class="w-4 h-4 rounded-full mr-2" style="background-color: ' . $color . ';"></div>';
                    echo '<span class="text-sm">' . htmlspecialchars($name) . '</span>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Plateforme de Tutorat. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html>
