<?php
// Ativar exibição de erros apenas se estiver em ambiente de desenvolvimento
if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Log do erro
$error = error_get_last();
if ($error !== null) {
    error_log("Erro 500: " . print_r($error, true));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro Interno do Servidor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 90%;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.5rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }
        .error-details {
            margin-top: 2rem;
            text-align: left;
            font-family: monospace;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <div class="error-message">Erro Interno do Servidor</div>
        <p class="text-muted">Ocorreu um erro inesperado. Nossa equipe técnica foi notificada.</p>
        
        <a href="/" class="btn btn-primary mt-3">Voltar para a Página Inicial</a>
        
        <?php if (isset($error) && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1')): ?>
        <div class="error-details">
            <strong>Detalhes do Erro (apenas em desenvolvimento):</strong><br>
            <?php echo "Tipo: " . $error['type'] . "<br>"; ?>
            <?php echo "Mensagem: " . $error['message'] . "<br>"; ?>
            <?php echo "Arquivo: " . $error['file'] . "<br>"; ?>
            <?php echo "Linha: " . $error['line']; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 