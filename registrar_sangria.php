<?php
require_once 'db.php';
require_once 'check_login.php';

$message = '';
$messageType = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor = mysqli_real_escape_string($conn, $_POST['valor']);
    $observacao = mysqli_real_escape_string($conn, $_POST['observacao']);
    $usuario_id = $_SESSION['usuario_id'];
    
    // Replace comma with dot for decimal
    $valor = str_replace(',', '.', $valor);
    
    // Validate inputs
    if (empty($valor) || $valor <= 0) {
        $message = 'Por favor, informe um valor válido para a sangria.';
        $messageType = 'danger';
    } else {
        $sql = "INSERT INTO sangrias (valor, observacao, usuario_id) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'dsi', $valor, $observacao, $usuario_id);
                
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Sangria registrada com sucesso!';
            $_SESSION['message_type'] = 'success';
            header('Location: sangrias.php');
            exit;
        } else {
            $message = 'Erro ao registrar sangria: ' . mysqli_error($conn);
            $messageType = 'danger';
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
        <h5 class="card-title">Registrar Nova Sangria</h5>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <div class="mb-3">
                <label for="valor" class="form-label">Valor (R$) *</label>
                <input type="text" class="form-control" id="valor" name="valor" required
                       placeholder="0,00" pattern="[0-9]+([,\.][0-9]{0,2})?">
            </div>
            
            <div class="mb-3">
                <label for="observacao" class="form-label">Observação</label>
                <textarea class="form-control" id="observacao" name="observacao" rows="3"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Operador</label>
                <input type="text" class="form-control" value="<?php echo $_SESSION['usuario_nome']; ?> (Caixa <?php echo $_SESSION['caixa_numero']; ?>)" readonly>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="sangrias.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?> 