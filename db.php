<?php
// Database configuration
// $host = 'localhost';
// $user = 'platafo5_pdv';
// $password = 'kqtnJ9laxto.';
// $database = 'platafo5_pdv';

// Database configuration
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'pdv';

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Conexão falhou: " . mysqli_connect_error());
}

// Set character set
mysqli_set_charset($conn, "utf8");
?> 