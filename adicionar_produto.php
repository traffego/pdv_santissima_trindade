<?php
require_once 'db.php';
require_once 'check_admin.php';

$message = '';
$messageType = '';

// Buscar todas as categorias
$sql_categorias = "SELECT * FROM categorias ORDER BY nome";
$result_categorias = mysqli_query($conn, $sql_categorias);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $preco = mysqli_real_escape_string($conn, $_POST['preco']);
    $categoria_id = mysqli_real_escape_string($conn, $_POST['categoria_id']);
    $quantidade = mysqli_real_escape_string($conn, $_POST['quantidade_estoque']);
    
    // Replace comma with dot for decimal
    $preco = str_replace(',', '.', $preco);
    
    // Validate inputs
    if (empty($nome) || empty($preco) || empty($quantidade)) {
        $message = 'Por favor, preencha todos os campos obrigatórios.';
        $messageType = 'danger';
    } else {
        $sql = "INSERT INTO produtos (nome, preco, categoria_id, quantidade_estoque) 
                VALUES ('$nome', $preco, '$categoria_id', $quantidade)";
                
        if (mysqli_query($conn, $sql)) {
            // Get category name for the message
            $sql_cat = "SELECT nome FROM categorias WHERE id = '$categoria_id'";
            $result_cat = mysqli_query($conn, $sql_cat);
            $categoria_nome = '';
            if ($row_cat = mysqli_fetch_assoc($result_cat)) {
                $categoria_nome = $row_cat['nome'];
            }
            
            // Armazena informações do produto para uso na modal
            $_SESSION['message'] = 'Produto adicionado com sucesso!';
            $_SESSION['message_type'] = 'success';
            $_SESSION['produto_nome'] = $nome;
            $_SESSION['produto_preco'] = number_format((float)$preco, 2, ',', '.');
            $_SESSION['produto_categoria'] = $categoria_nome;
            $_SESSION['produto_quantidade'] = $quantidade;
            $_SESSION['show_modal'] = true;
            
            // Redirecionar para a página de produtos
            header('Location: produtos.php');
            exit();
        } else {
            $message = 'Erro ao adicionar produto: ' . mysqli_error($conn);
            $messageType = 'danger';
        }
    }
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
        <h5 class="card-title">Adicionar Novo Produto</h5>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome *</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            
            <div class="mb-3">
                <label for="preco" class="form-label">Preço (R$) *</label>
                <input type="text" class="form-control" id="preco" name="preco" required
                       placeholder="0,00" pattern="[0-9]+([,\.][0-9]{0,2})?">
            </div>
            
            <div class="mb-3">
                <label for="categoria_id" class="form-label">Categoria *</label>
                <div class="input-group">
                    <select class="form-select" id="categoria_id" name="categoria_id" required>
                        <option value="">Selecione uma categoria</option>
                        <?php while ($categoria = mysqli_fetch_assoc($result_categorias)): ?>
                            <option value="<?php echo $categoria['id']; ?>">
                                <?php echo htmlspecialchars($categoria['nome']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <a href="produtos/categorias.php" class="btn btn-outline-secondary" target="_blank">
                        Gerenciar Categorias
                    </a>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="quantidade_estoque" class="form-label">Quantidade em Estoque *</label>
                <input type="number" class="form-control" id="quantidade_estoque" name="quantidade_estoque" required min="0">
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?> 