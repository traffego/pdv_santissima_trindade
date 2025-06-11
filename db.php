<?php
// Habilitar exibição de erros em desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$host = '187.33.241.40';
$user = 'platafo5_pdv';
$password = 'kqtnJ9laxto.';
$database = 'platafo5_pdv';

// Create connection with error handling
try {
    // Aumentar o tempo limite da conexão
    $conn = mysqli_init();
    mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);
    
    if (!mysqli_real_connect($conn, $host, $user, $password, $database)) {
        throw new Exception("Conexão falhou: " . mysqli_connect_error());
    }

    // Set character set
    if (!mysqli_set_charset($conn, "utf8")) {
        throw new Exception("Erro ao configurar charset: " . mysqli_error($conn));
    }

    // Configurar modo de erro para exceções
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

} catch (Exception $e) {
    // Log error to file
    error_log("Erro de conexão com banco de dados: " . $e->getMessage(), 0);
    
    // Em desenvolvimento, mostrar erro detalhado
    if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost') {
        die("Erro de conexão: " . $e->getMessage());
    } else {
        // Em produção, mostrar mensagem genérica
        die("Erro ao conectar com o banco de dados. Por favor, contate o administrador.");
    }
}
?> 