<?php
require_once 'db.php';
require_once 'check_login.php';

// Definir título da página
$pageTitle = "Lista de Vendas";

// Inicializar variáveis de filtro
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$caixa_filtro = isset($_GET['caixa']) ? $_GET['caixa'] : '';
$forma_pagamento = isset($_GET['forma_pagamento']) ? $_GET['forma_pagamento'] : '';
$produto_filtro = isset($_GET['produto']) ? $_GET['produto'] : '';

include 'header.php';

// Construir a consulta SQL base
$sql_base = "SELECT DISTINCT v.*, DATE_FORMAT(v.data_hora, '%d/%m/%Y %H:%i') as data_formatada, 
             u.nome as nome_usuario 
             FROM vendas v 
             LEFT JOIN usuarios u ON v.usuario_id = u.id";

// Adicionar JOIN com itens_venda e produtos se houver filtro de produto
if (!empty($produto_filtro)) {
    $sql_base .= " LEFT JOIN itens_venda iv ON v.id = iv.venda_id 
                   LEFT JOIN produtos p ON iv.produto_id = p.id";
}

$sql_base .= " WHERE DATE(v.data_hora) BETWEEN ? AND ?";

$params = array($data_inicio, $data_fim);
$types = "ss";

// Filtrar por caixa específico
if (!empty($caixa_filtro)) {
    $sql_base .= " AND v.caixa = ?";
    $params[] = $caixa_filtro;
    $types .= "i";
}

// Filtrar por forma de pagamento
if (!empty($forma_pagamento)) {
    $sql_base .= " AND v.forma_pagamento = ?";
    $params[] = $forma_pagamento;
    $types .= "s";
}

// Filtrar por produto
if (!empty($produto_filtro)) {
    $sql_base .= " AND p.id = ?";
    $params[] = $produto_filtro;
    $types .= "i";
}

// Restringir por permissão: Caixas só veem suas próprias vendas
if ($_SESSION['nivel'] !== 'administrador') {
    $sql_base .= " AND v.usuario_id = ?";
    $params[] = $_SESSION['usuario_id'];
    $types .= "i";
}

// Ordenar por data/hora decrescente (mais recentes primeiro)
$sql_base .= " ORDER BY v.data_hora DESC";

// Preparar e executar a consulta
$stmt = mysqli_prepare($conn, $sql_base);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Obter totais
$total_vendas = 0;
$total_valor = 0;
$vendas = [];

while ($row = mysqli_fetch_assoc($result)) {
    $vendas[] = $row;
    $total_vendas++;
    $total_valor += $row['valor_total'];
}

// Obter lista de caixas disponíveis para filtro (apenas para administradores)
$caixas = [];
if ($_SESSION['nivel'] === 'administrador') {
    $sql_caixas = "SELECT DISTINCT caixa_numero as caixa FROM usuarios WHERE nivel = 'operador' AND ativo = 1 
                   UNION 
                   SELECT DISTINCT caixa FROM vendas 
                   ORDER BY caixa";
    $result_caixas = mysqli_query($conn, $sql_caixas);
    while ($row = mysqli_fetch_assoc($result_caixas)) {
        $caixas[] = $row['caixa'];
    }
}

// Buscar lista de produtos para o filtro
$sql_produtos = "SELECT id, nome FROM produtos WHERE quantidade_estoque > 0 ORDER BY nome";
$result_produtos = mysqli_query($conn, $sql_produtos);
$produtos = array();
while ($row = mysqli_fetch_assoc($result_produtos)) {
    $produtos[$row['id']] = $row['nome'];
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shopping-cart me-2"></i><?php echo $pageTitle; ?>
                </h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filtros">
                        <i class="fas fa-filter me-1"></i> Filtros
                    </button>
                </div>
            </div>
            
            <div class="collapse" id="filtros">
                <div class="card-body border-bottom bg-light">
                    <form method="get" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Data Inicial</label>
                            <input type="date" class="form-control" name="data_inicio" value="<?php echo $data_inicio; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data Final</label>
                            <input type="date" class="form-control" name="data_fim" value="<?php echo $data_fim; ?>">
                        </div>
                        
                        <?php if ($_SESSION['nivel'] === 'administrador'): ?>
                        <div class="col-md-2">
                            <label class="form-label">Caixa</label>
                            <select class="form-select" name="caixa">
                                <option value="">Todos</option>
                                <?php foreach ($caixas as $num_caixa): ?>
                                <option value="<?php echo $num_caixa; ?>" <?php echo ($caixa_filtro == $num_caixa) ? 'selected' : ''; ?>>
                                    <?php echo ($num_caixa == 999) ? 'Admin (999)' : 'Caixa ' . $num_caixa; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-2">
                            <label class="form-label">Forma de Pagamento</label>
                            <select class="form-select" name="forma_pagamento">
                                <option value="">Todas</option>
                                <option value="Dinheiro" <?php echo ($forma_pagamento == 'Dinheiro') ? 'selected' : ''; ?>>Dinheiro</option>
                                <option value="Pix" <?php echo ($forma_pagamento == 'Pix') ? 'selected' : ''; ?>>Pix</option>
                                <option value="Cartão" <?php echo ($forma_pagamento == 'Cartão') ? 'selected' : ''; ?>>Cartão</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Produto</label>
                            <select class="form-select" name="produto">
                                <option value="">Todos</option>
                                <?php foreach ($produtos as $id => $nome): ?>
                                    <option value="<?php echo $id; ?>" <?php echo ($produto_filtro == $id ? 'selected' : ''); ?>>
                                        <?php echo $nome; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 text-muted">Total de Vendas</h6>
                                        <h4 class="mb-0"><?php echo $total_vendas; ?></h4>
                                    </div>
                                    <div class="text-primary">
                                        <i class="fas fa-file-invoice fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 text-muted">Valor Total</h6>
                                        <h4 class="mb-0">R$ <?php echo number_format($total_valor, 2, ',', '.'); ?></h4>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 text-muted">Ticket Médio</h6>
                                        <h4 class="mb-0">R$ <?php echo $total_vendas > 0 ? number_format($total_valor / $total_vendas, 2, ',', '.') : '0,00'; ?></h4>
                                    </div>
                                    <div class="text-info">
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($total_vendas > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Data/Hora</th>
                                <th>Valor</th>
                                <th>Pagamento</th>
                                <th>Caixa</th>
                                <?php if ($_SESSION['nivel'] === 'administrador'): ?>
                                <th>Operador</th>
                                <th>Tipo</th>
                                <?php endif; ?>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendas as $venda): ?>
                            <tr>
                                <td><?php echo $venda['id']; ?></td>
                                <td><?php echo $venda['data_formatada']; ?></td>
                                <td class="fw-bold">R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php if ($venda['forma_pagamento'] == 'Dinheiro'): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-money-bill-alt me-1"></i> Dinheiro
                                        </span>
                                    <?php elseif ($venda['forma_pagamento'] == 'Pix'): ?>
                                        <span class="badge bg-info">
                                            <i class="fas fa-qrcode me-1"></i> Pix
                                        </span>
                                    <?php elseif ($venda['forma_pagamento'] == 'Cartão'): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-credit-card me-1"></i> Cartão
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <?php echo $venda['forma_pagamento']; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($venda['caixa'] == 999): ?>
                                        <span class="badge bg-dark">Admin (999)</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Caixa <?php echo $venda['caixa']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($_SESSION['nivel'] === 'administrador'): ?>
                                <td><?php echo $venda['nome_usuario']; ?></td>
                                <td>
                                    <?php
                                    $tipo_badge_class = '';
                                    $tipo_icon = '';
                                    switch($venda['tipo']) {
                                        case 'comum':
                                            $tipo_badge_class = 'bg-primary';
                                            $tipo_icon = 'fa-shopping-basket';
                                            break;
                                        case 'doacao':
                                            $tipo_badge_class = 'bg-success';
                                            $tipo_icon = 'fa-hand-holding-heart';
                                            break;
                                        case 'perda':
                                            $tipo_badge_class = 'bg-danger';
                                            $tipo_icon = 'fa-times-circle';
                                            break;
                                        case 'devolucao':
                                            $tipo_badge_class = 'bg-warning text-dark';
                                            $tipo_icon = 'fa-undo';
                                            break;
                                        default:
                                            $tipo_badge_class = 'bg-secondary';
                                            $tipo_icon = 'fa-question-circle';
                                    }
                                    ?>
                                    <span class="badge <?php echo $tipo_badge_class; ?>">
                                        <i class="fas <?php echo $tipo_icon; ?> me-1"></i>
                                        <?php echo ucfirst($venda['tipo']); ?>
                                    </span>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="detalhes_venda.php?id=<?php echo $venda['id']; ?>" class="btn btn-info" title="Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="imprimir_venda.php?id=<?php echo $venda['id']; ?>" class="btn btn-secondary" title="Imprimir" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Nenhuma venda encontrada com os filtros selecionados.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-expand filtros se algum filtro estiver ativo além das datas padrão
    if (
        '<?php echo $caixa_filtro; ?>' !== '' || 
        '<?php echo $forma_pagamento; ?>' !== '' ||
        '<?php echo $data_inicio; ?>' !== '<?php echo date('Y-m-d', strtotime('-30 days')); ?>' ||
        '<?php echo $data_fim; ?>' !== '<?php echo date('Y-m-d'); ?>'
    ) {
        document.getElementById('filtros').classList.add('show');
    }
});
</script>

<?php include 'footer.php'; ?> 