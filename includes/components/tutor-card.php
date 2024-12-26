<?php
function renderTutorCard($tutor, $availability_by_tutor = []) {
    $days_fr = [
        'Monday' => 'Lundi',
        'Tuesday' => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday' => 'Jeudi',
        'Friday' => 'Vendredi',
        'Saturday' => 'Samedi',
        'Sunday' => 'Dimanche'
    ];
    ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-[1.02] transition-transform duration-200">
        <div class="p-4">
            <div class="flex gap-4">
                <!-- Left column with photo and basic info -->
                <div class="flex flex-col items-center w-1/3">
                    <div class="w-24 h-24 rounded-full overflow-hidden mb-3">
                        <img src="uploads/<?php echo $tutor['photo'] ? htmlspecialchars($tutor['photo']) : 'avatar.jpg'; ?>" 
                             alt="Photo de <?php echo htmlspecialchars($tutor['username']); ?>"
                             class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 text-center">
                        <?php echo htmlspecialchars($tutor['username']); ?>
                    </h3>
                    <p class="text-sm text-gray-600 text-center">
                        <?php echo htmlspecialchars($tutor['department_name']); ?>
                    </p>
                </div>

                <!-- Right column with subjects and availability -->
                <div class="flex-1">
                    <?php if (isset($tutor['subjects']) && $tutor['subjects']): ?>
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Matières :</h4>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach (explode(',', $tutor['subjects']) as $subject): ?>
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                        <?php echo htmlspecialchars($subject); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Disponibilités :</h4>
                        <?php if (isset($availability_by_tutor[$tutor['id']]) && !empty($availability_by_tutor[$tutor['id']])): ?>
                            <div class="space-y-1 text-sm">
                                <?php foreach ($availability_by_tutor[$tutor['id']] as $availability): ?>
                                    <div class="flex justify-between text-gray-600">
                                        <span class="font-medium"><?php echo $days_fr[$availability['day_of_week']]; ?></span>
                                        <span><?php echo $availability['start_time']; ?> - <?php echo $availability['end_time']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 italic">Aucune disponibilité renseignée</p>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-between items-center mt-4">
                        <div class="text-sm">
                            <?php if ($tutor['current_tutees'] >= 4): ?>
                                <span class="text-red-600 font-medium">Complet</span>
                            <?php else: ?>
                                <span class="text-green-600 font-medium">
                                    <?php echo 4 - $tutor['current_tutees']; ?> place(s) disponible(s)
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($tutor['current_tutees'] < 4): ?>
                            <a href="contact-tutor.php?id=<?php echo $tutor['id']; ?>" 
                               class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors text-sm">
                                Contacter
                            </a>
                        <?php else: ?>
                            <span class="inline-block bg-gray-300 text-gray-600 px-4 py-2 rounded text-sm">
                                Complet
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>