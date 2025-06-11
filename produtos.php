<?php
require_once 'db.php';
require_once 'check_admin.php';

// Buscar informações de estoque
$sql_estoque = "SELECT 
    COUNT(CASE WHEN quantidade_estoque <= 5 THEN 1 END) as estoque_critico,
    COUNT(CASE WHEN quantidade_estoque > 5 AND quantidade_estoque <= 10 THEN 1 END) as estoque_baixo,
    COUNT(CASE WHEN quantidade_estoque > 10 THEN 1 END) as estoque_normal,
    COUNT(*) as total_produtos,
    SUM(quantidade_estoque) as total_itens,
    SUM(quantidade_estoque * preco) as valor_total_estoque
FROM produtos";

$result_estoque = mysqli_query($conn, $sql_estoque);
$estoque = mysqli_fetch_assoc($result_estoque);

// Buscar produtos com estoque crítico ou baixo
$sql_criticos = "SELECT id, nome, quantidade_estoque, preco 
                 FROM produtos 
                 WHERE quantidade_estoque <= 10 
                 ORDER BY quantidade_estoque ASC 
                 LIMIT 10";
$result_criticos = mysqli_query($conn, $sql_criticos);

// Get products from the database with category names
$sql = "SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        ORDER BY p.nome";
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

<!-- Área de Gestão de Estoque -->
<div class="row g-4 mb-4">
    <!-- Card de Visão Geral -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Visão Geral do Estoque
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-boxes fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">Total de Produtos</div>
                                    <div class="fw-bold"><?php echo $estoque['total_produtos']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-cubes fa-2x text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">Total de Itens</div>
                                    <div class="fw-bold"><?php echo $estoque['total_itens']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-dollar-sign fa-2x text-warning"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">Valor Total em Estoque</div>
                                    <div class="fw-bold">R$ <?php echo number_format($estoque['valor_total_estoque'], 2, ',', '.'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card de Alertas -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Alertas de Estoque
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Estoque Crítico (≤ 5)</span>
                        <span class="badge bg-danger"><?php echo $estoque['estoque_critico']; ?> produtos</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-danger" style="width: <?php echo ($estoque['estoque_critico'] / $estoque['total_produtos']) * 100; ?>%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Estoque Baixo (6-10)</span>
                        <span class="badge bg-warning"><?php echo $estoque['estoque_baixo']; ?> produtos</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: <?php echo ($estoque['estoque_baixo'] / $estoque['total_produtos']) * 100; ?>%"></div>
                    </div>
                </div>

                <?php if (mysqli_num_rows($result_criticos) > 0): ?>
                    <div class="mt-4">
                        <h6 class="border-bottom pb-2">Produtos com Estoque Baixo</h6>
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th class="text-center">Estoque</th>
                                        <th class="text-end">Valor</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($produto = mysqli_fetch_assoc($result_criticos)): ?>
                                        <tr id="produto-<?php echo $produto['id']; ?>">
                                            <td><?php echo $produto['nome']; ?></td>
                                            <td class="text-center">
                                                <span class="badge <?php echo $produto['quantidade_estoque'] <= 5 ? 'bg-danger' : 'bg-warning'; ?>">
                                                    <?php echo $produto['quantidade_estoque']; ?>
                                                </span>
                                            </td>
                                            <td class="text-end">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success atualizar-estoque" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEstoque" 
                                                        data-produto-id="<?php echo $produto['id']; ?>"
                                                        data-produto-nome="<?php echo htmlspecialchars($produto['nome']); ?>"
                                                        data-produto-estoque="<?php echo $produto['quantidade_estoque']; ?>">
                                                    <i class="fas fa-plus-circle"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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
                        <th>Cor da Ficha</th>
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
                                <td><?php echo $row['categoria_nome'] ?? 'Sem categoria'; ?></td>
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
                                    <div class="d-flex align-items-center">
                                        <input type="color" 
                                               class="form-control form-control-color me-2 color-picker" 
                                               value="<?php echo $row['cor'] ?? '#FFFFFF'; ?>"
                                               data-produto-id="<?php echo $row['id']; ?>"
                                               title="Escolha a cor da ficha">
                                        <div class="color-preview" 
                                             style="width: 30px; height: 30px; border: 1px solid #dee2e6; background-color: <?php echo $row['cor'] ?? '#FFFFFF'; ?>">
                                        </div>
                                    </div>
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
                            <td colspan="7" class="text-center">Nenhum produto cadastrado</td>
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

<!-- Modal de Atualização de Estoque -->
<div class="modal fade" id="modalEstoque" tabindex="-1" aria-labelledby="modalEstoqueLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEstoqueLabel">Atualizar Estoque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEstoque">
                    <input type="hidden" id="produto_id" name="produto_id">
                    <div class="mb-3">
                        <label class="form-label">Produto</label>
                        <input type="text" class="form-control" id="produto_nome" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estoque Atual</label>
                        <input type="text" class="form-control" id="estoque_atual" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade a Adicionar</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="decrementarQuantidade()">-</button>
                            <input type="number" class="form-control text-center" id="quantidade" name="quantidade" value="1" min="1">
                            <button type="button" class="btn btn-outline-secondary" onclick="incrementarQuantidade()">+</button>
                        </div>
                        <div class="form-text">Use números positivos para entrada e negativos para saída</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="atualizarEstoque()">Atualizar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Configuração do modal de atualização de estoque
document.addEventListener('DOMContentLoaded', function() {
    const modalEstoque = document.getElementById('modalEstoque');
    modalEstoque.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const produtoId = button.getAttribute('data-produto-id');
        const produtoNome = button.getAttribute('data-produto-nome');
        const produtoEstoque = button.getAttribute('data-produto-estoque');
        
        document.getElementById('produto_id').value = produtoId;
        document.getElementById('produto_nome').value = produtoNome;
        document.getElementById('estoque_atual').value = produtoEstoque;
        document.getElementById('quantidade').value = 1;
    });
});

function incrementarQuantidade() {
    const input = document.getElementById('quantidade');
    input.value = parseInt(input.value) + 1;
}

function decrementarQuantidade() {
    const input = document.getElementById('quantidade');
    const novoValor = parseInt(input.value) - 1;
    if (novoValor >= 1) {
        input.value = novoValor;
    }
}

function atualizarEstoque() {
    const formData = new FormData();
    formData.append('produto_id', document.getElementById('produto_id').value);
    formData.append('quantidade', document.getElementById('quantidade').value);

    fetch('atualizar_estoque.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar a badge de quantidade na tabela
            const produtoId = document.getElementById('produto_id').value;
            const row = document.querySelector(`#produto-${produtoId}`);
            const badge = row.querySelector('.badge');
            badge.textContent = data.nova_quantidade;
            
            // Atualizar a classe da badge baseado na nova quantidade
            badge.classList.remove('bg-danger', 'bg-warning');
            badge.classList.add(data.nova_quantidade <= 5 ? 'bg-danger' : 'bg-warning');
            
            // Fechar o modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEstoque'));
            modal.hide();
            
            // Mostrar mensagem de sucesso
            alert('Estoque atualizado com sucesso!');
            
            // Recarregar a página após 1 segundo para atualizar os dados
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert(data.message || 'Erro ao atualizar estoque');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar a requisição');
    });
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar os color pickers
    document.querySelectorAll('.color-picker').forEach(picker => {
        picker.addEventListener('change', function(e) {
            const produtoId = this.dataset.produtoId;
            const novaCor = this.value;
            const preview = this.nextElementSibling;
            
            // Atualizar preview
            preview.style.backgroundColor = novaCor;
            
            // Enviar atualização para o servidor
            fetch('atualizar_cor_produto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `produto_id=${produtoId}&cor=${novaCor.substring(1)}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Erro ao atualizar a cor');
                    // Reverter para a cor anterior em caso de erro
                    this.value = preview.style.backgroundColor;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar a cor');
            });
        });
    });
});
</script>

<?php include 'footer.php'; ?> 