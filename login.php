<?php
require_once 'db.php';

// Iniciar sessão apenas se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to index
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

// Process the login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = mysqli_real_escape_string($conn, $_POST['usuario']);
    $senha = $_POST['senha'];
    
    // Find the user
    $query = "SELECT id, nome, usuario, senha, caixa_numero, nivel FROM usuarios WHERE usuario = ? AND ativo = 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Verify password
        if (password_verify($senha, $row['senha'])) {
            // Save user data to session
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario_nome'] = $row['nome'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['caixa_numero'] = $row['caixa_numero'];
            $_SESSION['nivel'] = $row['nivel'];
            
            // Update last login timestamp
            $update = "UPDATE usuarios SET ultimo_login = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($update_stmt, 'i', $row['id']);
            mysqli_stmt_execute($update_stmt);
            
            // Redirecionar baseado no nível do usuário
            if ($row['nivel'] === 'administrador') {
                header('Location: index.php');
            } else {
                header('Location: vender.php');
            }
            exit;
        } else {
            $erro = 'Senha incorreta.';
        }
    } else {
        $erro = 'Usuário não encontrado ou inativo.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema PDV</title>
    <!-- Favicon -->
    <link rel="icon" href="logo.jpeg" type="image/jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 15px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            font-size: 2.5rem;
            color: #343a40;
        }
        .login-header p {
            color: #6c757d;
        }
        .form-floating {
            margin-bottom: 15px;
        }
        .btn-login {
            font-size: 1.1rem;
            padding: 10px 0;
            font-weight: 500;
        }
        .cashier-icons {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        .cashier-icon {
            text-align: center;
            cursor: pointer;
            padding: 10px;
            border-radius: 10px;
        }
        .cashier-icon:hover {
            background-color: #e9ecef;
        }
        .cashier-icon i {
            font-size: 2rem;
            margin-bottom: 5px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card shadow">
            <div class="card-body p-4">
                <div class="login-header">
                    <img src="logo.jpeg" alt="Logo" class="img-fluid mb-3" style="max-height: 100px;">
                    <h4>Sistema PDV</h4>
                    <p class="text-muted">Login de Operador</p>
                </div>
                
                <?php if (!empty($erro)): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuário" required>
                        <label for="usuario">Usuário</label>
                    </div>
                    
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                        <label for="senha">Senha</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i> Entrar
                    </button>
                </form>
                
                <div class="cashier-icons mt-4">
                    <div class="cashier-icon" onclick="setCredentials('caixa1')">
                        <i class="fas fa-cash-register"></i>
                        <div>Caixa 1</div>
                    </div>
                    <div class="cashier-icon" onclick="setCredentials('caixa2')">
                        <i class="fas fa-cash-register"></i>
                        <div>Caixa 2</div>
                    </div>
                    <div class="cashier-icon" onclick="setCredentials('caixa3')">
                        <i class="fas fa-cash-register"></i>
                        <div>Caixa 3</div>
                    </div>
                </div>
                
                <div class="text-center mt-3 small text-muted">
                    Para demonstração: usuário e senha são iguais ao nome do caixa
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function setCredentials(username) {
        document.getElementById('usuario').value = username;
        document.getElementById('senha').value = username;
    }
    </script>
</body>
</html> 