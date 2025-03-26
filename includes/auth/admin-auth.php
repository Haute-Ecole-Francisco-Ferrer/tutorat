<?php
function checkAdminAuth() {
    // Add debug logging
    error_log("Checking admin auth - User ID: " . ($_SESSION['user_id'] ?? 'not set') . 
              ", User Type: " . ($_SESSION['user_type'] ?? 'not set'));
              
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
        error_log("Admin auth failed - redirecting to login");
        header('Location: /login.php');
        exit;
    }
    
    error_log("Admin auth successful");
}