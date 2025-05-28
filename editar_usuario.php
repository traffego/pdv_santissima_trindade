<?php
require_once 'db.php';
require_once 'check_admin.php';

$message = '';
$messageType = '';
$usuario = null;

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: usuarios.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $usuario_login = mysqli_real_escape_string($conn, $_POST['usuario']);
    $caixa_numero = mysqli_real_escape_string($conn, $_POST['caixa_numero']);
    $nivel = mysqli_real_escape_string($conn, $_POST['nivel']);
    $senha = $_POST['senha']; // Optional, may be empty if not changing
    
    // Validate inputs
    if (empty($nome) || empty($usuario_login) || empty($caixa_numero) || empty($nivel)) {
        $message = 'Por favor, preencha todos os campos obrigatórios.';
        $messageType = 'danger';
    } else {
        // Check if username already exists for another user
        $check_sql = "SELECT id FROM usuarios WHERE usuario = ? AND id != ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, 'si', $usuario_login, $id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $message = 'Este nome de usuário já está sendo utilizado por outro usuário.';
            $messageType = 'danger';
        } else {
            // Update user
            if (!empty($senha)) {
                // Update with new password
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nome = ?, usuario = ?, senha = ?, caixa_numero = ?, nivel = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'sssisi', $nome, $usuario_login, $senha_hash, $caixa_numero, $nivel, $id);
            } else {
                // Update without changing password
                $sql = "UPDATE usuarios SET nome = ?, usuario = ?, caixa_numero = ?, nivel = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, 'ssisi', $nome, $usuario_login, $caixa_numero, $nivel, $id);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = 'Usuário atualizado com sucesso!';
                $_SESSION['message_type'] = 'success';
                
                // If current user was updated, update the session
                if ($id == $_SESSION['usuario_id']) {
                    $_SESSION['usuario_nome'] = $nome;
                    $_SESSION['usuario'] = $usuario_login;
                    $_SESSION['caixa_numero'] = $caixa_numero;
                    $_SESSION['nivel'] = $nivel;
                }
                
                header('Location: usuarios.php');
                exit;
            } else {
                $message = 'Erro ao atualizar usuário: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    }
}

// Get user data
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $usuario = mysqli_fetch_assoc($result);
} else {
    header("Location: usuarios.php");
    exit;
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
        <h5 class="card-title">Editar Usuário</h5>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome Completo *</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="usuario" class="form-label">Nome de Usuário *</label>
                <input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
                <div class="form-text">O usuário deve ser único no sistema.</div>
            </div>
            
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha">
                <div class="form-text">Deixe em branco para manter a senha atual.</div>
            </div>
            
            <div class="mb-3">
                <label for="caixa_numero" class="form-label">Número do Caixa *</label>
                <input type="number" class="form-control" id="caixa_numero" name="caixa_numero" value="<?php echo $usuario['caixa_numero']; ?>" required min="0">
                <div class="form-text">Use 0 para usuários administrativos que não operam um caixa específico.</div>
            </div>
            
            <div class="mb-3">
                <label for="nivel" class="form-label">Nível de Acesso *</label>
                <select class="form-select" id="nivel" name="nivel" required>
                    <option value="">Selecione um nível</option>
                    <option value="administrador" <?php echo $usuario['nivel'] === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                    <option value="operador" <?php echo $usuario['nivel'] === 'operador' ? 'selected' : ''; ?>>Operador de Caixa</option>
                </select>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Atualizar</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?> 