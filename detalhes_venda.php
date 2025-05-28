<?php
require_once 'db.php';
require_once 'check_login.php';

// Verificar se o ID da venda foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = '<strong>Erro!</strong> ID da venda não especificado.';
    $_SESSION['message_type'] = 'danger';
    header('Location: lista_vendas.php');
    exit;
}

$venda_id = mysqli_real_escape_string($conn, $_GET['id']);

// Consultar informações da venda
$sql = "SELECT v.*, DATE_FORMAT(v.data_hora, '%d/%m/%Y %H:%i:%s') as data_formatada, 
        u.nome as nome_usuario 
        FROM vendas v 
        LEFT JOIN usuarios u ON v.usuario_id = u.id 
        WHERE v.id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $venda_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Verificar se a venda existe
if (mysqli_num_rows($result) == 0) {
    $_SESSION['message'] = '<strong>Erro!</strong> Venda não encontrada.';
    $_SESSION['message_type'] = 'danger';
    header('Location: lista_vendas.php');
    exit;
}

$venda = mysqli_fetch_assoc($result);

// Verificar permissão: apenas admin pode ver vendas de outros usuários
if ($_SESSION['nivel'] !== 'administrador' && $venda['usuario_id'] != $_SESSION['usuario_id']) {
    $_SESSION['message'] = '<strong>Erro!</strong> Você não tem permissão para visualizar esta venda.';
    $_SESSION['message_type'] = 'danger';
    header('Location: lista_vendas.php');
    exit;
}

// Consultar itens da venda
$sql_itens = "SELECT i.*, p.nome, p.codigo_barras 
              FROM itens_venda i 
              LEFT JOIN produtos p ON i.produto_id = p.id 
              WHERE i.venda_id = ?";

$stmt_itens = mysqli_prepare($conn, $sql_itens);
mysqli_stmt_bind_param($stmt_itens, 'i', $venda_id);
mysqli_stmt_execute($stmt_itens);
$result_itens = mysqli_stmt_get_result($stmt_itens);

// Contar itens da venda
$total_itens = mysqli_num_rows($result_itens);

// Definir título da página
$pageTitle = "Detalhes da Venda #" . $venda_id;

include 'header.php';
?>

<div class="row mb-4">
    <div class="col-md-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-receipt me-2"></i><?php echo $pageTitle; ?>
            </h4>
            <div>
                <a href="lista_vendas.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Voltar
                </a>
                <a href="imprimir_venda.php?id=<?php echo $venda_id; ?>" class="btn btn-outline-primary" target="_blank">
                    <i class="fas fa-print me-1"></i> Imprimir
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações da Venda</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th class="ps-0 text-muted">ID da Venda:</th>
                                <td><strong><?php echo $venda['id']; ?></strong></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Data/Hora:</th>
                                <td><?php echo $venda['data_formatada']; ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Forma de Pagamento:</th>
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
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th class="ps-0 text-muted">Valor Total:</th>
                                <td class="text-success fw-bold">R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Caixa:</th>
                                <td>
                                    <?php if ($venda['caixa'] == 999): ?>
                                        <span class="badge bg-dark">Admin (999)</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Caixa <?php echo $venda['caixa']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted">Operador:</th>
                                <td><?php echo $venda['nome_usuario']; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Itens da Venda</h5>
                <span class="badge bg-primary rounded-pill"><?php echo $total_itens; ?> item(ns)</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Produto</th>
                                <th>Cód. Barras</th>
                                <th class="text-center">Qtd</th>
                                <th class="text-end">Preço Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Resetar o ponteiro do resultado para o início
                            mysqli_data_seek($result_itens, 0);
                            
                            // Exibir todos os itens
                            while ($item = mysqli_fetch_assoc($result_itens)): 
                                $subtotal = $item['quantidade'] * $item['preco_unitario'];
                            ?>
                            <tr>
                                <td>
                                    <span class="fw-medium"><?php echo $item['nome']; ?></span>
                                </td>
                                <td>
                                    <span class="text-muted"><?php echo $item['codigo_barras'] ?: '--'; ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill"><?php echo $item['quantidade']; ?></span>
                                </td>
                                <td class="text-end">
                                    R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?>
                                </td>
                                <td class="text-end fw-bold">
                                    R$ <?php echo number_format($subtotal, 2, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold fs-5 text-success">
                                    R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 