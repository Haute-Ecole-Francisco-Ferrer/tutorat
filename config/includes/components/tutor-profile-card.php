<?php
function renderTutorProfileCard($tutor) {
    ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="md:flex">
            <div class="md:flex-shrink-0">
                <img src="uploads/<?php echo htmlspecialchars($tutor['photo'] ?? 'default.jpg'); ?>" 
                     alt="Photo de <?php echo htmlspecialchars($tutor['username']); ?>"
                     class="h-48 w-full md:w-48 object-cover">
            </div>
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                    <?php echo htmlspecialchars($tutor['username']); ?>
                </h2>
                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($tutor['department_name']); ?></p>
                
                <?php if ($tutor['subjects']): ?>
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Matières :</h3>
                        <div class="flex flex-wrap gap-1">
                            <?php foreach (explode(',', $tutor['subjects']) as $subject): ?>
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                    <?php echo htmlspecialchars($subject); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <p class="text-sm text-gray-600">
                    <span class="font-medium"><?php echo 4 - $tutor['current_tutees']; ?></span> place(s) disponible(s)
                </p>
            </div>
        </div>
    </div>
    <?php
}
?>