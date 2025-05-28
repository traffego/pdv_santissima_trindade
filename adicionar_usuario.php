<?php
require_once 'db.php';
require_once 'check_admin.php';

$message = '';
$messageType = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $usuario = mysqli_real_escape_string($conn, $_POST['usuario']);
    $senha = $_POST['senha'];
    $caixa_numero = mysqli_real_escape_string($conn, $_POST['caixa_numero']);
    $nivel = mysqli_real_escape_string($conn, $_POST['nivel']);
    
    // Validate inputs
    if (empty($nome) || empty($usuario) || empty($senha) || empty($caixa_numero) || empty($nivel)) {
        $message = 'Por favor, preencha todos os campos.';
        $messageType = 'danger';
    } else {
        // Check if username already exists
        $check_sql = "SELECT id FROM usuarios WHERE usuario = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, 's', $usuario);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $message = 'Este nome de usuário já está sendo utilizado.';
            $messageType = 'danger';
        } else {
            // Hash the password
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO usuarios (nome, usuario, senha, caixa_numero, nivel) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'sssis', $nome, $usuario, $senha_hash, $caixa_numero, $nivel);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = 'Usuário adicionado com sucesso!';
                $_SESSION['message_type'] = 'success';
                header('Location: usuarios.php');
                exit;
            } else {
                $message = 'Erro ao adicionar usuário: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    }
}

include 'header.php';
?>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Adicionar Novo Usuário</h5>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome Completo *</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            
            <div class="mb-3">
                <label for="usuario" class="form-label">Nome de Usuário *</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required>
                <div class="form-text">O usuário deve ser único no sistema.</div>
            </div>
            
            <div class="mb-3">
                <label for="senha" class="form-label">Senha *</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            
            <div class="mb-3">
                <label for="caixa_numero" class="form-label">Número do Caixa *</label>
                <input type="number" class="form-control" id="caixa_numero" name="caixa_numero" required min="0">
                <div class="form-text">Use 0 para usuários administrativos que não operam um caixa específico.</div>
            </div>
            
            <div class="mb-3">
                <label for="nivel" class="form-label">Nível de Acesso *</label>
                <select class="form-select" id="nivel" name="nivel" required>
                    <option value="">Selecione um nível</option>
                    <option value="administrador">Administrador</option>
                    <option value="operador">Operador de Caixa</option>
                </select>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Adicionar</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?> 