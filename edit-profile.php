<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté et est un tuteur
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tutor') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();
$error_message = '';
$success_message = '';

// Récupérer les informations actuelles de l'utilisateur
$query = "SELECT u.*, d.name as department_name 
          FROM users u 
          JOIN departments d ON u.department_id = d.id 
          WHERE u.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Récupérer la liste des départements
$departments = get_departments($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Valider et nettoyer les entrées
        $firstname = sanitize_input($_POST['firstname']);
        $lastname = sanitize_input($_POST['lastname']);
        $phone = sanitize_input($_POST['phone']);
        $study_level = $_POST['study_level'];
        $section = sanitize_input($_POST['section']);
        $department_id = intval($_POST['department_id']);

        // Vérifier si un nouveau fichier photo a été uploadé
        $photo = $user['photo']; // Garder l'ancienne photo par défaut
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_photo($_FILES['photo']);
            if (isset($upload_result['error'])) {
                throw new Exception($upload_result['error']);
            }
            $photo = $upload_result['filename'];
            
            // Supprimer l'ancienne photo si elle existe
            if ($user['photo'] && file_exists(__DIR__ . '/uploads/' . $user['photo'])) {
                unlink(__DIR__ . '/uploads/' . $user['photo']);
            }
        }

        // Mettre à jour le profil
        $query = "UPDATE users SET 
                  firstname = ?, 
                  lastname = ?, 
                  phone = ?, 
                  photo = ?, 
                  study_level = ?, 
                  department_id = ?, 
                  section = ?
                  WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $firstname,
            $lastname,
            $phone,
            $photo,
            $study_level,
            $department_id,
            $section,
            $user_id
        ]);

        $success_message = "Votre profil a été mis à jour avec succès.";
        
        // Mettre à jour les informations en session si nécessaire
        $_SESSION['username'] = $username;
        
        // Rafraîchir les données de l'utilisateur
        $stmt = $db->prepare("SELECT u.*, d.name as department_name 
                             FROM users u 
                             JOIN departments d ON u.department_id = d.id 
                             WHERE u.id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

    } catch (Exception $e) {
        $error_message = "Une erreur est survenue : " . $e->getMessage();
    }
}

// Définir les variables pour le header
$currentPage = 'edit-profile';
$pageTitle = 'Modifier mon profil';

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Modifier mon profil</h1>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Photo de profil -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Photo de profil actuelle
                    </label>
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
                            <label class="block text-sm font-medium text-gray-700">
                                Changer la photo
                            </label>
                            <input type="file" 
                                   name="photo" 
                                   accept=".jpg,.jpeg,.png"
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
                        <input type="text" 
                               id="firstname" 
                               name="firstname" 
                               value="<?php echo htmlspecialchars($user['firstname']); ?>" 
                               required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="lastname" class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" 
                               id="lastname" 
                               name="lastname" 
                               value="<?php echo htmlspecialchars($user['lastname']); ?>" 
                               required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="<?php echo htmlspecialchars($user['phone']); ?>" 
                               pattern="[0-9]{10}" 
                               required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="study_level" class="block text-sm font-medium text-gray-700">Niveau d'études</label>
                        <select id="study_level" 
                                name="study_level" 
                                required 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="Bloc 1" <?php echo $user['study_level'] === 'Bloc 1' ? 'selected' : ''; ?>>Bloc 1</option>
                            <option value="Bloc 2 - poursuite d'études" <?php echo $user['study_level'] === "Bloc 2 - poursuite d'études" ? 'selected' : ''; ?>>Bloc 2 - poursuite d'études</option>
                            <option value="Bloc 2 - année diplômante" <?php echo $user['study_level'] === "Bloc 2 - année diplômante" ? 'selected' : ''; ?>>Bloc 2 - année diplômante</option>
                            <option value="Master" <?php echo $user['study_level'] === 'Master' ? 'selected' : ''; ?>>Master</option>
                        </select>
                    </div>

                    <div>
                        <label for="department_id" class="block text-sm font-medium text-gray-700">Département</label>
                        <select id="department_id" 
                                name="department_id" 
                                required 
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
                        <input type="text" 
                               id="section" 
                               name="section" 
                               value="<?php echo htmlspecialchars($user['section']); ?>" 
                               required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Boutons -->
                <div class="flex justify-end space-x-4">
                    <a href="tutor-profile.php" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>