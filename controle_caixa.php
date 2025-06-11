<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';
require_once 'check_login.php';

// Definir o fuso horário para Brasil
date_default_timezone_set('America/Sao_Paulo');

// Verificar se existe um caixa aberto para o usuário
$usuario_id = $_SESSION['usuario_id'];
$caixa_numero = $_SESSION['caixa_numero'];

// Inicializar variáveis de filtro
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

// Buscar informações do caixa aberto atual
$sql_caixa_atual = "SELECT id FROM controle_caixa 
                    WHERE usuario_id = ? 
                    AND caixa_numero = ? 
                    AND status = 'aberto' 
                    ORDER BY data_abertura DESC 
                    LIMIT 1";

$stmt_caixa = mysqli_prepare($conn, $sql_caixa_atual);
mysqli_stmt_bind_param($stmt_caixa, "ii", $usuario_id, $caixa_numero);
mysqli_stmt_execute($stmt_caixa);
$result_caixa = mysqli_stmt_get_result($stmt_caixa);
$caixa_atual = mysqli_fetch_assoc($result_caixa);

if (!$caixa_atual) {
    // Se não houver caixa aberto, redirecionar para a página apropriada
    header("Location: abrir_caixa.php");
    exit;
}

// Construir a consulta SQL base
$sql_base = "SELECT 
    v.*, 
    DATE_FORMAT(v.data_hora, '%d/%m/%Y %H:%i') as data_formatada,
    DATE(v.data_hora) as data_venda,
    p.nome as produto_nome,
    iv.quantidade,
    iv.preco_unitario
FROM vendas v 
LEFT JOIN itens_venda iv ON v.id = iv.venda_id
LEFT JOIN produtos p ON iv.produto_id = p.id
WHERE DATE(v.data_hora) BETWEEN ? AND ?";

// Se não for administrador, filtrar apenas pelo usuário atual
if ($_SESSION['nivel'] !== 'administrador') {
    $sql_base .= " AND v.usuario_id = ? AND v.caixa = ?";
}

$sql_base .= " ORDER BY v.data_hora DESC";

// Preparar parâmetros
$params = array($data_inicio, $data_fim);
$types = "ss";

if ($_SESSION['nivel'] !== 'administrador') {
    $params[] = $usuario_id;
    $params[] = $caixa_numero;
    $types .= "ii";
}

// Preparar e executar a consulta
$stmt = mysqli_prepare($conn, $sql_base);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Inicializar totalizadores
$total_vendas = 0;
$total_valor = 0;
$total_itens = 0;
$vendas_por_pagamento = array(
    'Dinheiro' => 0,
    'Pix' => 0,
    'Cartão' => 0
);
$produtos_mais_vendidos = array();
$vendas_por_dia = array();
$vendas_por_hora = array_fill(0, 24, 0);

// Processar resultados
while ($row = mysqli_fetch_assoc($result)) {
    $total_vendas++;
    $total_valor += $row['valor_total'];
    $total_itens += $row['quantidade'];
    $vendas_por_pagamento[$row['forma_pagamento']] += $row['valor_total'];
    
    // Contabilizar produtos mais vendidos
    if (!empty($row['produto_nome'])) {
        if (!isset($produtos_mais_vendidos[$row['produto_nome']])) {
            $produtos_mais_vendidos[$row['produto_nome']] = 0;
        }
        $produtos_mais_vendidos[$row['produto_nome']] += $row['quantidade'];
    }
    
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

// Ordenar produtos mais vendidos e vendas por dia
arsort($produtos_mais_vendidos);
ksort($vendas_por_dia);

include 'header.php';
?>

<div class="container-fluid px-4">
    <!-- Botões de Ação -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="registrar_sangria.php" class="btn btn-danger">
                    <i class="fas fa-money-bill-wave me-2"></i>Realizar Sangria
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#fecharCaixaModal">
                    <i class="fas fa-lock me-2"></i>Fechar Caixa
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Período</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="data_inicio" value="<?php echo $data_inicio; ?>">
                        <span class="input-group-text">até</span>
                        <input type="date" class="form-control" name="data_fim" value="<?php echo $data_fim; ?>">
                    </div>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
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
                                <i class="fas fa-receipt text-white"></i>
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

    <div class="row g-4">
        <!-- Vendas por Forma de Pagamento -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Vendas por Forma de Pagamento</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-12">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-money-bill text-success fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">Dinheiro</div>
                                    <div class="fw-bold">R$ <?php echo number_format($vendas_por_pagamento['Dinheiro'], 2, ',', '.'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-qrcode text-info fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">PIX</div>
                                    <div class="fw-bold">R$ <?php echo number_format($vendas_por_pagamento['Pix'], 2, ',', '.'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-credit-card text-warning fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">Cartão</div>
                                    <div class="fw-bold">R$ <?php echo number_format($vendas_por_pagamento['Cartão'], 2, ',', '.'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produtos Mais Vendidos -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Produtos Mais Vendidos</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="height: 250px;">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-end">Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $count = 0;
                                foreach ($produtos_mais_vendidos as $produto => $quantidade):
                                    if ($count++ < 10): // Limitar aos 10 mais vendidos
                                ?>
                                <tr>
                                    <td><?php echo $produto; ?></td>
                                    <td class="text-end"><?php echo $quantidade; ?></td>
                                </tr>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Vendas por Dia -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Vendas por Dia</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="vendasDiaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Vendas por Hora -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0">Vendas por Hora</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="vendasHoraChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Fechamento de Caixa -->
    <div class="modal fade" id="fecharCaixaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Fechamento de Caixa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="processar_fechamento_caixa.php" method="post" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Confirme os valores antes de fechar o caixa
                        </div>

                        <!-- Campo oculto com o ID do caixa -->
                        <input type="hidden" name="caixa_id" value="<?php echo $caixa_atual['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Valor em Dinheiro</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       class="form-control" 
                                       name="valor_dinheiro" 
                                       value="<?php echo number_format($vendas_por_pagamento['Dinheiro'] ?? 0, 2, '.', ''); ?>"
                                       required>
                                <div class="invalid-feedback">
                                    Informe o valor em dinheiro.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total em PIX</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="text" class="form-control" value="<?php echo number_format($vendas_por_pagamento['Pix'] ?? 0, 2, ',', '.'); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total em Cartão</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="text" class="form-control" value="<?php echo number_format($vendas_por_pagamento['Cartão'] ?? 0, 2, ',', '.'); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos ocultos com os valores de PIX e Cartão -->
                        <input type="hidden" name="valor_pix" value="<?php echo number_format($vendas_por_pagamento['Pix'] ?? 0, 2, '.', ''); ?>">
                        <input type="hidden" name="valor_cartao" value="<?php echo number_format($vendas_por_pagamento['Cartão'] ?? 0, 2, '.', ''); ?>">

                        <div class="mb-3">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-lock me-2"></i>Fechar Caixa
                        </button>
                    </div>
                </form>
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

.bg-primary, .bg-success, .bg-info, .bg-warning {
    background-image: linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);
    background-size: 1rem 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuração comum para todos os gráficos
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: true,
        animation: false
    };

    // Gráfico de vendas por dia
    new Chart(document.getElementById('vendasDiaChart'), {
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
            plugins: {
                legend: {
                    display: false
                }
            },
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

    // Gráfico de vendas por hora
    new Chart(document.getElementById('vendasHoraChart'), {
        type: 'bar',
        data: {
            labels: Array.from({length: 24}, (_, i) => i.toString().padStart(2, '0') + 'h'),
            datasets: [{
                data: <?php echo json_encode(array_values($vendas_por_hora)); ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.5)',
                borderColor: 'rgb(13, 110, 253)',
                borderWidth: 1
            }]
        },
        options: {
            ...chartOptions,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'R$ ' + Number(context.raw).toFixed(2);
                        }
                    }
                }
            },
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

// Adicionar validação do formulário
const forms = document.querySelectorAll('.needs-validation');
Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>

<?php include 'footer.php'; ?> 