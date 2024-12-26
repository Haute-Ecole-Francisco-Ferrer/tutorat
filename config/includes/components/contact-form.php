<?php
function renderContactForm($tutor, $subjects) {
    ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-6">Envoyer une demande de tutorat</h3>

        <form method="POST" class="space-y-6">
            <div>
                <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Matière souhaitée :
                </label>
                <select name="subject_id" id="subject_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Sélectionnez une matière</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>">
                            <?php echo htmlspecialchars($subject['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                    Message pour le tuteur :
                </label>
                <textarea name="message" id="message" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Expliquez brièvement vos besoins et vos attentes..."></textarea>
            </div>

            <div class="flex justify-between items-center">
                <a href="all-tutors.php" 
                   class="text-gray-600 hover:text-gray-800">
                    Retour à la liste
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Envoyer la demande
                </button>
            </div>
        </form>
    </div>
    <?php
}
?>