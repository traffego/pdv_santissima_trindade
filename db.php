<?php
// Database configuration
$host = 'localhost';
$user = 'platafo5_pdv';
$password = 'kqtnJ9laxto.';
$database = 'platafo5_pdv';

// Create connection with error handling
try {
    $conn = mysqli_connect($host, $user, $password, $database);

    // Check connection
    if (!$conn) {
        throw new Exception("Conexão falhou: " . mysqli_connect_error());
    }

    // Set character set
    if (!mysqli_set_charset($conn, "utf8")) {
        throw new Exception("Erro ao configurar charset: " . mysqli_error($conn));
    }

} catch (Exception $e) {
    // Log error to file instead of displaying it
    error_log("Erro de conexão com banco de dados: " . $e->getMessage(), 0);
    
    // Show generic error message
    die("Erro ao conectar com o banco de dados. Por favor, contate o administrador.");
}
?> 