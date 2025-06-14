<?php
require_once 'db.php';
// Include check_admin.php para garantir que apenas administradores acessem o dashboard
require_once 'check_admin.php';

// Definir o fuso horário para Brasil
date_default_timezone_set('America/Sao_Paulo');

// Get current date for queries
$today = date('Y-m-d');

// Inicializar variáveis de filtro
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$caixa_filtro = isset($_GET['caixa']) ? $_GET['caixa'] : '';
$forma_pagamento = isset($_GET['forma_pagamento']) ? $_GET['forma_pagamento'] : '';
$usuario_filtro = isset($_GET['usuario']) ? $_GET['usuario'] : '';

// Construir a consulta SQL base
$sql_base = "SELECT 
    v.id,
    v.data_hora,
    v.valor_total,
    v.forma_pagamento,
    DATE(v.data_hora) as data_venda,
    u.nome as nome_usuario
FROM vendas v 
LEFT JOIN usuarios u ON v.usuario_id = u.id
WHERE DATE(v.data_hora) BETWEEN ? AND ?";

$params = array($data_inicio, $data_fim);
$types = "ss";

// Adicionar filtros à consulta
if (!empty($caixa_filtro)) {
    $sql_base .= " AND v.caixa = ?";
    $params[] = $caixa_filtro;
    $types .= "i";
}

if (!empty($forma_pagamento)) {
    $sql_base .= " AND v.forma_pagamento = ?";
    $params[] = $forma_pagamento;
    $types .= "s";
}

if (!empty($usuario_filtro)) {
    $sql_base .= " AND v.usuario_id = ?";
    $params[] = $usuario_filtro;
    $types .= "i";
}

// Ordenar por data/hora decrescente
$sql_base .= " ORDER BY v.data_hora DESC";

// Preparar e executar a consulta
$stmt = mysqli_prepare($conn, $sql_base);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Inicializar totalizadores
$total_vendas = 0;
$total_valor = 0;
$vendas_por_pagamento = array(
    'Dinheiro' => 0,
    'Pix' => 0,
    'Cartão' => 0
);
$vendas_por_dia = array();
$vendas_por_hora = array_fill(0, 24, 0);

// Processar resultados das vendas
while ($row = mysqli_fetch_assoc($result)) {
    $total_vendas++;
    $total_valor += $row['valor_total'];
    $vendas_por_pagamento[$row['forma_pagamento']] += $row['valor_total'];
    
    // Contabilizar vendas por dia
    $data_venda = $row['data_venda'];
    if (!isset($vendas_por_dia[$data_venda])) {
        $vendas_por_dia[$data_venda] = 0;
    }
    $vendas_por_dia[$data_venda] += $row['valor_total'];
    
    // Contabilizar vendas por hora
    $hora = date('G', strtotime($row['data_hora']));
    $vendas_por_hora[$hora] += $row['valor_total'];
}

// Buscar total de itens e produtos mais vendidos em uma consulta separada
$sql_itens = "SELECT 
    p.nome as produto_nome,
    SUM(iv.quantidade) as quantidade_total
FROM itens_venda iv
JOIN vendas v ON iv.venda_id = v.id
JOIN produtos p ON iv.produto_id = p.id
WHERE DATE(v.data_hora) BETWEEN ? AND ?";

$params_itens = array($data_inicio, $data_fim);
$types_itens = "ss";

if (!empty($caixa_filtro)) {
    $sql_itens .= " AND v.caixa = ?";
    $params_itens[] = $caixa_filtro;
    $types_itens .= "i";
}

if (!empty($forma_pagamento)) {
    $sql_itens .= " AND v.forma_pagamento = ?";
    $params_itens[] = $forma_pagamento;
    $types_itens .= "s";
}

if (!empty($usuario_filtro)) {
    $sql_itens .= " AND v.usuario_id = ?";
    $params_itens[] = $usuario_filtro;
    $types_itens .= "i";
}

$sql_itens .= " GROUP BY p.nome ORDER BY quantidade_total DESC";

$stmt_itens = mysqli_prepare($conn, $sql_itens);
mysqli_stmt_bind_param($stmt_itens, $types_itens, ...$params_itens);
mysqli_stmt_execute($stmt_itens);
$result_itens = mysqli_stmt_get_result($stmt_itens);

$total_itens = 0;
$produtos_mais_vendidos = array();

while ($row = mysqli_fetch_assoc($result_itens)) {
    $total_itens += $row['quantidade_total'];
    $produtos_mais_vendidos[$row['produto_nome']] = $row['quantidade_total'];
}

// Ordenar produtos mais vendidos
arsort($produtos_mais_vendidos);
ksort($vendas_por_dia);

// Buscar lista de caixas e usuários para o filtro
$sql_caixas = "SELECT DISTINCT caixa FROM vendas ORDER BY caixa";
$result_caixas = mysqli_query($conn, $sql_caixas);
$caixas = array();
while ($row = mysqli_fetch_assoc($result_caixas)) {
    $caixas[] = $row['caixa'];
}

$sql_usuarios = "SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome";
$result_usuarios = mysqli_query($conn, $sql_usuarios);
$usuarios = array();
while ($row = mysqli_fetch_assoc($result_usuarios)) {
    $usuarios[$row['id']] = $row['nome'];
}

include 'header.php';
?>

<div class="container-fluid px-4">
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Período</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="data_inicio" value="<?php echo $data_inicio; ?>">
                        <span class="input-group-text">até</span>
                        <input type="date" class="form-control" name="data_fim" value="<?php echo $data_fim; ?>">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Caixa</label>
                    <select class="form-select" name="caixa">
                        <option value="">Todos</option>
                        <?php foreach ($caixas as $caixa): ?>
                            <option value="<?php echo $caixa; ?>" <?php echo ($caixa_filtro == $caixa ? 'selected' : ''); ?>>
                                Caixa <?php echo $caixa; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Forma de Pagamento</label>
                    <select class="form-select" name="forma_pagamento">
                        <option value="">Todas</option>
                        <option value="Dinheiro" <?php echo ($forma_pagamento == 'Dinheiro' ? 'selected' : ''); ?>>Dinheiro</option>
                        <option value="Pix" <?php echo ($forma_pagamento == 'Pix' ? 'selected' : ''); ?>>PIX</option>
                        <option value="Cartão" <?php echo ($forma_pagamento == 'Cartão' ? 'selected' : ''); ?>>Cartão</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Usuário</label>
                    <select class="form-select" name="usuario">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $id => $nome): ?>
                            <option value="<?php echo $id; ?>" <?php echo ($usuario_filtro == $id ? 'selected' : ''); ?>>
                                <?php echo $nome; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="#" onclick="exportarVendas(event)" class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(45deg, #28a745, #20c997); box-shadow: 0 2px 6px rgba(40, 167, 69, 0.4); border: none;">
                        <i class="fas fa-file-excel me-2"></i>
                        <span>Exportar Excel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="header-icon bg-primary">
                                <i class="fas fa-shopping-cart text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total de Vendas</h6>
                            <h4 class="mb-0"><?php echo $total_vendas; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="header-icon bg-success">
                                <i class="fas fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Valor Total</h6>
                            <h4 class="mb-0">R$ <?php echo number_format($total_valor, 2, ',', '.'); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="header-icon bg-info">
                                <i class="fas fa-box text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total de Itens</h6>
                            <h4 class="mb-0"><?php echo $total_itens; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="header-icon bg-warning">
                                <i class="fas fa-chart-pie text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Ticket Médio</h6>
                            <h4 class="mb-0">R$ <?php echo $total_vendas > 0 ? number_format($total_valor / $total_vendas, 2, ',', '.') : '0,00'; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos e Tabelas -->
    <div class="row g-4">
        <!-- Gráfico de Vendas por Forma de Pagamento -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Vendas por Forma de Pagamento</h6>
                </div>
                <div class="card-body p-2" style="height: 300px;">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Produtos Mais Vendidos -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Produtos Mais Vendidos</h6>
                </div>
                <div class="card-body p-2" style="height: 300px;">
                    <canvas id="productsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Vendas por Dia -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Vendas por Dia</h6>
                </div>
                <div class="card-body p-2" style="height: 300px;">
                    <canvas id="dailySalesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Vendas por Hora -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Vendas por Hora</h6>
                </div>
                <div class="card-body p-2" style="height: 300px;">
                    <canvas id="hourlySalesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.header-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-icon i {
    font-size: 24px;
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.table-sm td, .table-sm th {
    padding: 0.3rem;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.6) !important;
    background: linear-gradient(45deg, #218838, #1ca38b) !important;
    transition: all 0.3s ease;
}

.btn-success:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.4) !important;
}

@keyframes pulseExport {
    0% {
        box-shadow: 0 2px 6px rgba(40, 167, 69, 0.4);
    }
    50% {
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.6);
    }
    100% {
        box-shadow: 0 2px 6px rgba(40, 167, 69, 0.4);
    }
}

.btn-success {
    animation: pulseExport 2s infinite;
    font-weight: 500;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuração comum para todos os gráficos
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    padding: 10
                }
            }
        }
    };

    // Gráfico de Vendas por Forma de Pagamento
    const ctxPayment = document.getElementById('paymentChart').getContext('2d');
    new Chart(ctxPayment, {
        type: 'doughnut',
        data: {
            labels: ['Dinheiro', 'PIX', 'Cartão'],
            datasets: [{
                data: [
                    <?php echo $vendas_por_pagamento['Dinheiro']; ?>,
                    <?php echo $vendas_por_pagamento['Pix']; ?>,
                    <?php echo $vendas_por_pagamento['Cartão']; ?>
                ],
                backgroundColor: ['#198754', '#0dcaf0', '#ffc107']
            }]
        },
        options: chartOptions
    });

    // Gráfico de Produtos Mais Vendidos
    const ctxProducts = document.getElementById('productsChart').getContext('2d');
    new Chart(ctxProducts, {
        type: 'bar',
        data: {
            labels: [
                <?php 
                $count = 0;
                foreach ($produtos_mais_vendidos as $produto => $quantidade) {
                    if ($count++ < 5) {
                        echo "'" . addslashes($produto) . "',";
                    }
                }
                ?>
            ],
            datasets: [{
                label: 'Quantidade Vendida',
                data: [
                    <?php 
                    $count = 0;
                    foreach ($produtos_mais_vendidos as $quantidade) {
                        if ($count++ < 5) {
                            echo $quantidade . ",";
                        }
                    }
                    ?>
                ],
                backgroundColor: '#0d6efd'
            }]
        },
        options: {
            ...chartOptions,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Gráfico de Vendas por Dia
    const ctxDaily = document.getElementById('dailySalesChart').getContext('2d');
    new Chart(ctxDaily, {
        type: 'line',
        data: {
            labels: [<?php 
                foreach ($vendas_por_dia as $data => $valor) {
                    echo "'" . date('d/m', strtotime($data)) . "',";
                }
            ?>],
            datasets: [{
                label: 'Valor Total (R$)',
                data: [<?php 
                    foreach ($vendas_por_dia as $valor) {
                        echo $valor . ",";
                    }
                ?>],
                borderColor: '#198754',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Vendas por Hora
    const ctxHourly = document.getElementById('hourlySalesChart').getContext('2d');
    new Chart(ctxHourly, {
        type: 'bar',
        data: {
            labels: Array.from({length: 24}, (_, i) => i + 'h'),
            datasets: [{
                label: 'Valor Total (R$)',
                data: <?php echo json_encode(array_values($vendas_por_hora)); ?>,
                backgroundColor: '#6f42c1'
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
});

function exportarVendas(e) {
    e.preventDefault();
    const params = new URLSearchParams(new FormData(document.querySelector('form')));
    window.location.href = 'exportar_vendas.php?' + params.toString();
}
</script>

<?php include 'footer.php'; ?> 