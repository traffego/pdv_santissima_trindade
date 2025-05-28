<?php
// Iniciar sessão apenas se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['usuario_id'])) {
    // User is not logged in, redirect to login page
    header('Location: login.php');
    exit;
}

// Check if session is expired (optional - set to 8 hours)
$timeout = 28800; // 8 hours in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    // Session expired
    session_unset();
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();
?> 