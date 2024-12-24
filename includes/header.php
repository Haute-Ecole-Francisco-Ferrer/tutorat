<?php
if(!isset($currentPage)) {
    $currentPage = '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . " - " : "" ?>Plateforme de Tutorat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .dropdown-wrapper:hover .dropdown-menu,
        .dropdown-menu:hover {
            display: block;
        }
        .dropdown-wrapper::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 10px;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header avec Navigation -->
    <header class="bg-slate-800">
        <nav class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="text-white">
                    <h1 class="text-xl font-bold">Plateforme de Tutorat</h1>
                </div>
                
                <!-- Menu de navigation -->
                <div class="hidden md:flex space-x-6">
                    <a href="index.php" class="text-white px-3 py-2 rounded-md text-sm font-medium <?= $currentPage === 'home' ? 'bg-slate-700' : 'hover:text-gray-300' ?>">Accueil</a>
                    
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <!-- Menu déroulant pour l'inscription -->
                        <div class="relative dropdown-wrapper">
                            <button class="text-white px-3 py-2 rounded-md text-sm font-medium <?= in_array($currentPage, ['register-tutor', 'register-tutee']) ? 'bg-slate-700' : 'hover:text-gray-300' ?>">
                                Inscription ▼
                            </button>
                            <div class="dropdown-menu absolute left-0 hidden w-48 bg-white rounded-md shadow-lg py-1 z-10">
                                <a href="register-tutor.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?= $currentPage === 'register-tutor' ? 'bg-gray-100' : '' ?>">Devenir Tuteur</a>
                                <a href="register-tutee.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 <?= $currentPage === 'register-tutee' ? 'bg-gray-100' : '' ?>">Devenir Tutoré</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Pages communes -->
                    <a href="all-tutors.php" class="text-white px-3 py-2 rounded-md text-sm font-medium <?= $currentPage === 'all-tutors' ? 'bg-slate-700' : 'hover:text-gray-300' ?>">Les Tuteurs</a>
                    <a href="departments.php" class="text-white px-3 py-2 rounded-md text-sm font-medium <?= $currentPage === 'departments' ? 'bg-slate-700' : 'hover:text-gray-300' ?>">Départements</a>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Menu utilisateur connecté -->
                        <?php if ($_SESSION['user_type'] === 'tutor'): ?>
                            <a href="my-tutees.php" class="text-white px-3 py-2 rounded-md text-sm font-medium <?= $currentPage === 'my-tutees' ? 'bg-slate-700' : 'hover:text-gray-300' ?>">Mes Tutorés</a>
                            <a href="tutor-profile.php" class="text-white px-3 py-2 rounded-md text-sm font-medium <?= $currentPage === 'tutor-profile' ? 'bg-slate-700' : 'hover:text-gray-300' ?>">Mon Profil Tuteur</a>
                        <?php else: ?>
                            <a href="my-tutors.php" class="text-white px-3 py-2 rounded-md text-sm font-medium <?= $currentPage === 'my-tutors' ? 'bg-slate-700' : 'hover:text-gray-300' ?>">Mes Tuteurs</a>
                            <a href="tutee-profile.php" class="text-white px-3 py-2 rounded-md text-sm font-medium <?= $currentPage === 'tutee-profile' ? 'bg-slate-700' : 'hover:text-gray-300' ?>">Mon Profil Tutoré</a>
                        <?php endif; ?>
                        <a href="messages.php" class="text-white px-3 py-2 rounded-md text-sm font-medium <?= $currentPage === 'messages' ? 'bg-slate-700' : 'hover:text-gray-300' ?>">Messages</a>
                        <a href="logout.php" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:text-gray-300">Déconnexion</a>
                    <?php else: ?>
                        <a href="login.php" class="text-white px-3 py-2 rounded-md text-sm font-medium <?= $currentPage === 'login' ? 'bg-slate-700' : 'hover:text-gray-300' ?>">Connexion</a>
                    <?php endif; ?>
                    
                    <a href="faq.php" class="text-white px-3 py-2 rounded-md text-sm font-medium <?= $currentPage === 'faq' ? 'bg-slate-700' : 'hover:text-gray-300' ?>">FAQ</a>
                </div>

                <!-- Menu burger pour mobile -->
                <div class="md:hidden">
                    <button class="mobile-menu-button p-2 text-white hover:text-gray-300 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Menu mobile -->
            <div class="mobile-menu hidden md:hidden mt-4">
                <a href="index.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'home' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">Accueil</a>
                <a href="all-tutors.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'all-tutors' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">Les Tuteurs</a>
                <a href="departments.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'departments' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">Départements</a>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="register-tutor.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'register-tutor' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">Devenir Tuteur</a>
                    <a href="register-tutee.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'register-tutee' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">Devenir Tutoré</a>
                    <a href="login.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'login' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">Connexion</a>
                <?php else: ?>
                    <?php if ($_SESSION['user_type'] === 'tutor'): ?>
                        <a href="my-tutees.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'my-tutees' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">Mes Tutorés</a>
                        <a href="tutor-profile.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'tutor-profile' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">Mon Profil Tuteur</a>
                    <?php else: ?>
                        <a href="my-tutors.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'my-tutors' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">Mes Tuteurs</a>
                        <a href="tutee-profile.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'tutee-profile' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">Mon Profil Tutoré</a>
                    <?php endif; ?>
                    <a href="messages.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'messages' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">Messages</a>
                    <a href="logout.php" class="block text-white px-3 py-2 rounded-md text-base font-medium hover:bg-gray-700">Déconnexion</a>
                <?php endif; ?>
                
                <a href="faq.php" class="block text-white px-3 py-2 rounded-md text-base font-medium <?= $currentPage === 'faq' ? 'bg-slate-700' : 'hover:bg-gray-700' ?>">FAQ</a>
            </div>
        </nav>
    </header>

    <script>
        // Gestion du menu mobile
        document.querySelector('.mobile-menu-button').addEventListener('click', function() {
            document.querySelector('.mobile-menu').classList.toggle('hidden');
        });
    </script>
