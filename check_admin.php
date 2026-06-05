<?php
require_once 'check_login.php';

// Check if user has admin privileges
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] !== 'administrador') {
    // User is not an admin, redirect to the main page with an error message
    $_SESSION['message'] = 'Acesso restrito. Você não tem permissão para acessar esta área.';
    $_SESSION['message_type'] = 'danger';
    
    $base_path = '';
    if (strpos($_SERVER['SCRIPT_NAME'], '/produtos/') !== false) {
        $base_path = '../';
    }
    
    header('Location: ' . $base_path . 'vender.php');
    exit;
}
?> 