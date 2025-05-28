<?php
require_once 'db.php';
require_once 'check_admin.php';

$message = '';
$messageType = '';
$produto = null;

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: produtos.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $preco = mysqli_real_escape_string($conn, $_POST['preco']);
    $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
    $quantidade = mysqli_real_escape_string($conn, $_POST['quantidade_estoque']);
    
    // Replace comma with dot for decimal
    $preco = str_replace(',', '.', $preco);
    
    // Validate inputs
    if (empty($nome) || empty($preco) || empty($quantidade)) {
        $message = 'Por favor, preencha todos os campos obrigatórios.';
        $messageType = 'danger';
    } else {
        $sql = "UPDATE produtos 
                SET nome = '$nome', 
                    preco = $preco, 
                    categoria = '$categoria', 
                    quantidade_estoque = $quantidade 
                WHERE id = $id";
                
        if (mysqli_query($conn, $sql)) {
            // Armazena informações do produto para uso na modal
            $_SESSION['message'] = 'Produto atualizado com sucesso!';
            $_SESSION['message_type'] = 'success';
            $_SESSION['produto_nome'] = $nome;
            $_SESSION['produto_preco'] = number_format((float)$preco, 2, ',', '.');
            $_SESSION['produto_categoria'] = $categoria;
            $_SESSION['produto_quantidade'] = $quantidade;
            $_SESSION['show_modal'] = true;
            $_SESSION['produto_editado'] = true;
            
            // Redirecionar para a página de produtos
            header('Location: produtos.php');
            exit();
        } else {
            $message = 'Erro ao atualizar produto: ' . mysqli_error($conn);
            $messageType = 'danger';
        }
    }
}

// Get product data
$sql = "SELECT * FROM produtos WHERE id = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $produto = mysqli_fetch_assoc($result);
} else {
    header("Location: produtos.php");
    exit;
}

include 'header.php';
?>

<?php if (isset($message) && !empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Editar Produto</h5>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome *</label>
                <input type="text" class="form-control" id="nome" name="nome" 
                       value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="preco" class="form-label">Preço (R$) *</label>
                <input type="text" class="form-control" id="preco" name="preco" required
                       value="<?php echo str_replace('.', ',', $produto['preco']); ?>"
                       placeholder="0,00" pattern="[0-9]+([,\.][0-9]{0,2})?">
            </div>
            
            <div class="mb-3">
                <label for="categoria" class="form-label">Categoria</label>
                <input type="text" class="form-control" id="categoria" name="categoria"
                       value="<?php echo htmlspecialchars($produto['categoria']); ?>">
            </div>
            
            <div class="mb-3">
                <label for="quantidade_estoque" class="form-label">Quantidade em Estoque *</label>
                <input type="number" class="form-control" id="quantidade_estoque" name="quantidade_estoque" 
                       value="<?php echo $produto['quantidade_estoque']; ?>" required min="0">
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Atualizar</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?> 