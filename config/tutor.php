<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
session_start();

if(!isset($_GET['id'])) {
    header('Location: tutors.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_tutor = 1");
$stmt->execute([$_GET['id']]);
$tutor = $stmt->fetch();

if(!$tutor) {
    header('Location: tutors.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profil Tuteur</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($tutor['name']) ?></h1>
        
        <?php if(isset($_SESSION['user_id']) && !$tutor['is_tutor']): ?>
            <div class="contact-form">
                <textarea id="tutor-message" 
                    placeholder="Votre message pour le tuteur"></textarea>
                <button onclick="contactTutor(<?= $tutor['id'] ?>)">
                    Contacter
                </button>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="js/contact-tutor.js"></script>
</body>
</html>