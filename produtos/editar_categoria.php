<?php
require_once '../db.php';
require_once '../check_admin.php';

$message = '';
$messageType = '';
$categoria = null;

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: categorias.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    
    // Validate inputs
    if (empty($nome)) {
        $message = 'Por favor, preencha o nome da categoria.';
        $messageType = 'danger';
    } else {
        $sql = "UPDATE categorias SET nome = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nome, $id);
                
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = 'Categoria atualizada com sucesso!';
            $_SESSION['tipo_mensagem'] = 'success';
            header('Location: categorias.php');
            exit();
        } else {
            $message = 'Erro ao atualizar categoria: ' . $conn->error;
            $messageType = 'danger';
        }
    }
}

// Get category data
$sql = "SELECT id, nome FROM categorias WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $categoria = $result->fetch_assoc();
} else {
    header("Location: categorias.php");
    exit;
}

include_once '../header.php';
?>

<div class="container mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Editar Categoria</h5>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome da Categoria *</label>
                    <input type="text" class="form-control" id="nome" name="nome" 
                           value="<?php echo htmlspecialchars($categoria['nome']); ?>" required>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="categorias.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../footer.php'; ?> 