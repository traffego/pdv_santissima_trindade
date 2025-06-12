<?php
require_once 'db.php';
require_once 'check_login.php';

if (!isset($_GET['id'])) {
    die('ID não fornecido');
}

$id = intval($_GET['id']);

// Verificar permissão: administrador vê tudo, operador vê apenas seu próprio caixa
$sql_base = "SELECT 
    cc.*,
    DATE_FORMAT(cc.data_abertura, '%d/%m/%Y %H:%i') as data_abertura_formatada,
    DATE_FORMAT(cc.data_fechamento, '%d/%m/%Y %H:%i') as data_fechamento_formatada,
    u.nome as nome_usuario
FROM controle_caixa cc
LEFT JOIN usuarios u ON cc.usuario_id = u.id
WHERE cc.id = ?";

// Se não for administrador, adicionar restrição de caixa
if ($_SESSION['nivel'] !== 'administrador') {
    $sql_base .= " AND cc.caixa_numero = ? AND cc.usuario_id = ?";
}

$stmt = mysqli_prepare($conn, $sql_base);

if ($_SESSION['nivel'] === 'administrador') {
    mysqli_stmt_bind_param($stmt, "i", $id);
} else {
    mysqli_stmt_bind_param($stmt, "iii", $id, $_SESSION['caixa_numero'], $_SESSION['usuario_id']);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Buscar vendas relacionadas
    $sql_vendas = "SELECT 
        v.*,
        DATE_FORMAT(v.data_hora, '%d/%m/%Y %H:%i') as data_formatada,
        GROUP_CONCAT(CONCAT(p.nome, ' (', iv.quantidade, ')') SEPARATOR ', ') as produtos
    FROM vendas v
    LEFT JOIN itens_venda iv ON v.id = iv.venda_id
    LEFT JOIN produtos p ON iv.produto_id = p.id
    WHERE v.controle_caixa_id = ?
    GROUP BY v.id
    ORDER BY v.data_hora";
    
    $stmt_vendas = mysqli_prepare($conn, $sql_vendas);
    mysqli_stmt_bind_param($stmt_vendas, "i", $id);
    mysqli_stmt_execute($stmt_vendas);
    $result_vendas = mysqli_stmt_get_result($stmt_vendas);
    
    // Buscar sangrias relacionadas
    $sql_sangrias = "SELECT 
        s.*,
        DATE_FORMAT(s.data, '%d/%m/%Y %H:%i') as data_formatada
    FROM sangrias s
    WHERE s.controle_caixa_id = ?
    ORDER BY s.data";
    
    $stmt_sangrias = mysqli_prepare($conn, $sql_sangrias);
    mysqli_stmt_bind_param($stmt_sangrias, "i", $id);
    mysqli_stmt_execute($stmt_sangrias);
    $result_sangrias = mysqli_stmt_get_result($stmt_sangrias);
    ?>
    
    <div class="row g-4">
        <!-- Informações Gerais -->
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Informações Gerais</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Caixa:</strong> <?php echo $row['caixa_numero']; ?></p>
                            <p class="mb-1"><strong>Operador:</strong> <?php echo $row['nome_usuario']; ?></p>
                            <p class="mb-1"><strong>Abertura:</strong> <?php echo $row['data_abertura_formatada']; ?></p>
                            <p class="mb-1"><strong>Fechamento:</strong> <?php echo $row['data_fechamento_formatada'] ?? '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Valor Inicial:</strong> R$ <?php echo number_format($row['valor_inicial'], 2, ',', '.'); ?></p>
                            <p class="mb-1"><strong>Total Vendas:</strong> R$ <?php echo number_format($row['valor_vendas'], 2, ',', '.'); ?></p>
                            <p class="mb-1"><strong>Total Sangrias:</strong> R$ <?php echo number_format($row['valor_sangrias'], 2, ',', '.'); ?></p>
                            <p class="mb-1"><strong>Valor Final:</strong> R$ <?php echo number_format($row['valor_final'] ?? 0, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                    <?php if (!empty($row['observacoes'])): ?>
                        <div class="mt-3">
                            <strong>Observações:</strong><br>
                            <?php echo nl2br($row['observacoes']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Vendas -->
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Vendas do Período</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 300px;">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Valor</th>
                                    <th>Pagamento</th>
                                    <th>Produtos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($venda = mysqli_fetch_assoc($result_vendas)): ?>
                                    <tr>
                                        <td><?php echo $venda['data_formatada']; ?></td>
                                        <td>R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></td>
                                        <td><?php echo $venda['forma_pagamento']; ?></td>
                                        <td><?php echo $venda['produtos']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sangrias -->
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Sangrias do Período</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 300px;">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Valor</th>
                                    <th>Observação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($sangria = mysqli_fetch_assoc($result_sangrias)): ?>
                                    <tr>
                                        <td><?php echo $sangria['data_formatada']; ?></td>
                                        <td>R$ <?php echo number_format($sangria['valor'], 2, ',', '.'); ?></td>
                                        <td><?php echo $sangria['observacao']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    echo '<div class="alert alert-danger">Movimento de caixa não encontrado ou sem permissão para visualizar.</div>';
}
?> 