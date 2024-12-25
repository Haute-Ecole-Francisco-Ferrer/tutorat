<?php
/**
 * Common registration fields component
 */
?>
<!-- Photo -->
<div>
    <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
    <input type="file" id="photo" name="photo" accept="image/*" 
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
    <p class="mt-1 text-xs text-gray-500">Format accepté : JPG, PNG (max 5MB)</p>
</div>

<!-- Phone -->
<div>
    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
    <input type="tel" id="phone" name="phone" required pattern="[0-9]{10}"
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>

<!-- Study Level -->
<div>
    <label for="study_level" class="block text-sm font-medium text-gray-700 mb-1">Niveau d'études</label>
    <select id="study_level" name="study_level" required 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Sélectionnez votre niveau</option>
        <option value="Bloc 1">Bloc 1</option>
        <option value="Bloc 2 - poursuite d'études">Bloc 2 - poursuite d'études</option>
        <option value="Bloc 2 - année diplômante">Bloc 2 - année diplômante</option>
        <option value="Master">Master</option>
    </select>
</div>

<!-- Department -->
<div>
    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Département</label>
    <select id="department_id" name="department_id" required 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Sélectionnez votre département</option>
        <?php foreach ($departments as $department): ?>
            <option value="<?php echo $department['id']; ?>">
                <?php echo htmlspecialchars($department['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- Section -->
<div>
    <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
    <input type="text" id="section" name="section" required 
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>

<?php if ($currentPage === 'register-tutor'): ?>
    <!-- Subjects (for tutors only) -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">Matières souhaitées (5 maximum) :</label>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($subjects as $subject): ?>
                <div class="flex items-center">
                    <input type="checkbox" name="subjects[]" value="<?php echo $subject['id']; ?>" 
                           id="subject_<?php echo $subject['id']; ?>" class="subject-select h-4 w-4 text-blue-600">
                    <label for="subject_<?php echo $subject['id']; ?>" class="ml-2 text-sm text-gray-700">
                        <?php echo htmlspecialchars($subject['name']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Availabilities -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-3">Disponibilités :</label>
    <div class="space-y-4">
        <?php
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        foreach ($days as $index => $day):
        ?>
            <div class="border border-gray-200 rounded-md p-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="days[]" value="<?php echo $index + 1; ?>" 
                           class="h-4 w-4 text-blue-600">
                    <span class="ml-2 font-medium"><?php echo $day; ?></span>
                </label>
                <div class="time-slots mt-3 grid grid-cols-2 gap-4" style="display: none;">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Début :</label>
                        <select name="start_time_<?php echo $index + 1; ?>" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <?php
                            for ($hour = 7; $hour <= 23; $hour++) {
                                for ($min = 0; $min < 60; $min += 30) {
                                    $time = sprintf("%02d:%02d", $hour, $min);
                                    echo "<option value=\"{$time}\">{$time}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Fin :</label>
                        <select name="end_time_<?php echo $index + 1; ?>" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <?php
                            for ($hour = 7; $hour <= 23; $hour++) {
                                for ($min = 0; $min < 60; $min += 30) {
                                    $time = sprintf("%02d:%02d", $hour, $min);
                                    echo "<option value=\"{$time}\">{$time}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>