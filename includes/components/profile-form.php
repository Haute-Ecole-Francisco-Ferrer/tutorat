<?php
function renderProfileForm($user, $departments) {
    ?>
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <!-- Photo de profil -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Photo de profil actuelle</label>
            <div class="flex items-center">
                <div class="w-24 h-24 rounded-full overflow-hidden bg-gray-200">
                    <?php if ($user['photo']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                             alt="Photo de profil" 
                             class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <span class="text-3xl text-gray-500">
                                <?php echo strtoupper(substr($user['firstname'], 0, 1) . substr($user['lastname'], 0, 1)); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="ml-4">
                    <input type="file" name="photo" accept=".jpg,.jpeg,.png"
                           class="mt-1 block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100">
                    <p class="mt-1 text-sm text-gray-500">JPG ou PNG. Max 5 MB.</p>
                </div>
            </div>
        </div>

        <!-- Informations personnelles -->
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label for="firstname" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" id="firstname" name="firstname" 
                       value="<?php echo htmlspecialchars($user['firstname']); ?>" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="lastname" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="lastname" name="lastname" 
                       value="<?php echo htmlspecialchars($user['lastname']); ?>" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                <input type="tel" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($user['phone']); ?>" required 
                       pattern="[0-9]{10}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="study_level" class="block text-sm font-medium text-gray-700">Niveau d'études</label>
                <select id="study_level" name="study_level" required 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="Bloc 1" <?php echo $user['study_level'] === 'Bloc 1' ? 'selected' : ''; ?>>Bloc 1</option>
                    <option value="Bloc 2 - poursuite d'études" <?php echo $user['study_level'] === "Bloc 2 - poursuite d'études" ? 'selected' : ''; ?>>Bloc 2 - poursuite d'études</option>
                    <option value="Bloc 2 - année diplômante" <?php echo $user['study_level'] === "Bloc 2 - année diplômante" ? 'selected' : ''; ?>>Bloc 2 - année diplômante</option>
                    <option value="Master" <?php echo $user['study_level'] === 'Master' ? 'selected' : ''; ?>>Master</option>
                </select>
            </div>

            <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700">Département</label>
                <select id="department_id" name="department_id" required 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo $department['id']; ?>" 
                                <?php echo $user['department_id'] == $department['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="section" class="block text-sm font-medium text-gray-700">Section</label>
                <input type="text" id="section" name="section" 
                       value="<?php echo htmlspecialchars($user['section']); ?>" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Boutons -->
        <div class="flex justify-end space-x-4">
            <a href="<?php echo $_SESSION['user_type'] === 'tutor' ? 'tutor-profile.php' : 'tutee-profile.php'; ?>" 
               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                Annuler
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Enregistrer les modifications
            </button>
        </div>
    </form>
    <?php
}

function renderMessages($error_message, $success_message) {
    if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error_message; ?>
        </div>
    <?php endif;
    
    if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $success_message; ?>
        </div>
    <?php endif;
}
?>