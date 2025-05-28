<?php
require_once 'db.php';
require_once 'check_admin.php';

// Get products from the database
$sql = "SELECT * FROM produtos ORDER BY nome";
$result = mysqli_query($conn, $sql);

include 'header.php';

// Display session messages if any
if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
    echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">
            ' . $_SESSION['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    
    // Clear session messages
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<div class="d-flex justify-content-between mb-3">
    <h3>Listagem de Produtos</h3>
    <a href="adicionar_produto.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Produto
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Categoria</th>
                        <th>Estoque</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['nome']; ?></td>
                                <td>R$ <?php echo number_format($row['preco'], 2, ',', '.'); ?></td>
                                <td><?php echo $row['categoria']; ?></td>
                                <td>
                                    <?php if ($row['quantidade_estoque'] <= 5): ?>
                                        <span class="badge bg-danger"><?php echo $row['quantidade_estoque']; ?></span>
                                    <?php elseif ($row['quantidade_estoque'] <= 10): ?>
                                        <span class="badge bg-warning"><?php echo $row['quantidade_estoque']; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo $row['quantidade_estoque']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="editar_produto.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="excluir_produto.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Nenhum produto cadastrado</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Feedback de Produto -->
<?php if (isset($_SESSION['show_modal']) && $_SESSION['show_modal']): ?>
<div class="modal fade" id="produtoModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header <?php echo isset($_SESSION['produto_editado']) ? 'bg-warning' : 'bg-success'; ?> text-white">
                <h5 class="modal-title" id="modalLabel">
                    <i class="fas fa-<?php echo isset($_SESSION['produto_editado']) ? 'edit' : 'check-circle'; ?> me-2"></i> 
                    Produto <?php echo isset($_SESSION['produto_editado']) ? 'Atualizado' : 'Adicionado'; ?> com Sucesso!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card border-<?php echo isset($_SESSION['produto_editado']) ? 'warning' : 'success'; ?> mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $_SESSION['produto_nome']; ?></h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span><strong>Preço:</strong></span>
                                <span>R$ <?php echo $_SESSION['produto_preco']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><strong>Categoria:</strong></span>
                                <span><?php echo $_SESSION['produto_categoria'] ? $_SESSION['produto_categoria'] : 'Não definida'; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><strong>Quantidade em Estoque:</strong></span>
                                <span><?php echo $_SESSION['produto_quantidade']; ?> unidades</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fechar</button>
                <?php if (!isset($_SESSION['produto_editado'])): ?>
                <a href="adicionar_produto.php" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i> Adicionar Outro Produto
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('produtoModal'));
        modal.show();
    });
</script>

<?php
    // Limpar variáveis de sessão após exibir o modal
    unset($_SESSION['show_modal']);
    unset($_SESSION['produto_nome']);
    unset($_SESSION['produto_preco']);
    unset($_SESSION['produto_categoria']);
    unset($_SESSION['produto_quantidade']);
    unset($_SESSION['produto_editado']);
?>
<?php endif; ?>

<?php include 'footer.php'; ?> 