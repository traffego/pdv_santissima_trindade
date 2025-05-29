<?php
require_once 'db.php';
// Include check_admin.php para garantir que apenas administradores acessem o dashboard
require_once 'check_admin.php';

// Definir o fuso horário para Brasil
date_default_timezone_set('America/Sao_Paulo');

// Get current date for queries
$today = date('Y-m-d');

// Fetch all needed data with optimized queries
$data = [
    'vendas_hoje' => 0,
    'pagamentos' => ['Dinheiro' => 0, 'Pix' => 0, 'Cartão' => 0],
    'produtos' => [],
    'estoque' => [],
    'caixas' => [],
    'usuarios' => [],
    'potencial' => 0
];

// Total sales today and payment method breakdown
$sql_vendas = "SELECT 
                SUM(valor_total) as total_vendas,
                forma_pagamento,
                SUM(CASE WHEN forma_pagamento = 'Dinheiro' THEN valor_total ELSE 0 END) as total_dinheiro,
                SUM(CASE WHEN forma_pagamento = 'Pix' THEN valor_total ELSE 0 END) as total_pix,
                SUM(CASE WHEN forma_pagamento = 'Cartão' THEN valor_total ELSE 0 END) as total_cartao
               FROM vendas 
               WHERE DATE(data_hora) = '$today'";
$result_vendas = mysqli_query($conn, $sql_vendas);
$row_vendas = mysqli_fetch_assoc($result_vendas);
$data['vendas_hoje'] = $row_vendas['total_vendas'] ?? 0;
$data['pagamentos'] = [
    'Dinheiro' => $row_vendas['total_dinheiro'] ?? 0,
    'Pix' => $row_vendas['total_pix'] ?? 0,
    'Cartão' => $row_vendas['total_cartao'] ?? 0
];

// Top selling products today
$sql_produtos = "SELECT p.nome, SUM(iv.quantidade) as quantidade
               FROM itens_venda iv
               JOIN produtos p ON iv.produto_id = p.id
               JOIN vendas v ON iv.venda_id = v.id
               WHERE DATE(v.data_hora) = '$today'
               GROUP BY p.id
               ORDER BY quantidade DESC
               LIMIT 5";
$result_produtos = mysqli_query($conn, $sql_produtos);
while($row = mysqli_fetch_assoc($result_produtos)) {
    $data['produtos'][$row['nome']] = $row['quantidade'];
}

// Stock levels - lowest 5
$sql_estoque = "SELECT nome, quantidade_estoque FROM produtos ORDER BY quantidade_estoque ASC LIMIT 5";
$result_estoque = mysqli_query($conn, $sql_estoque);
while($row = mysqli_fetch_assoc($result_estoque)) {
    $data['estoque'][$row['nome']] = $row['quantidade_estoque'];
}

// Sales by register
$sql_caixas = "SELECT caixa, COUNT(*) as vendas, SUM(valor_total) as total
               FROM vendas WHERE DATE(data_hora) = '$today' GROUP BY caixa";
$result_caixas = mysqli_query($conn, $sql_caixas);
while($row = mysqli_fetch_assoc($result_caixas)) {
    $data['caixas'][$row['caixa']] = ['vendas' => $row['vendas'], 'total' => $row['total']];
}

// Sales by user/cashier
try {
    $sql_usuarios = "SELECT u.nome, COUNT(v.id) as vendas, SUM(v.valor_total) as total
                FROM vendas v
                JOIN usuarios u ON v.usuario_id = u.id
                WHERE DATE(v.data_hora) = '$today'
                GROUP BY v.usuario_id
                ORDER BY total DESC";
    $result_usuarios = mysqli_query($conn, $sql_usuarios);
    while($row = mysqli_fetch_assoc($result_usuarios)) {
        $data['usuarios'][$row['nome']] = ['vendas' => $row['vendas'], 'total' => $row['total']];
    }
} catch (Exception $e) {
    // Se houver erro, inicia com array vazio
    $data['usuarios'] = [];
}

// Potential revenue
$sql_potential = "SELECT SUM(preco * quantidade_estoque) as potential FROM produtos";
$result_potential = mysqli_query($conn, $sql_potential);
$row_potential = mysqli_fetch_assoc($result_potential);
$data['potencial'] = $row_potential['potential'] ?? 0;

include 'header.php';
?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-3">
                <h5 class="m-0">Total de Vendas Hoje</h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center text-center">
                <h2 class="text-primary mb-2">R$ <?php echo number_format($data['vendas_hoje'], 2, ',', '.'); ?></h2>
                <p class="text-muted mb-0"><?php echo date('d/m/Y'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-3">
                <h5 class="m-0">Vendas por Pagamento</h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                    <canvas id="payment-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="m-0">Faturamento Potencial</h5>
                    <img src="logo.jpeg" alt="Logo" class="img-fluid" style="max-height: 30px;">
                </div>
            </div>
            <div class="card-body d-flex flex-column justify-content-center text-center">
                <div class="gauge-container flex-grow-1 d-flex align-items-center justify-content-center">
                    <canvas id="gauge-chart"></canvas>
                </div>
                <p class="mt-2 mb-0">R$ <?php echo number_format($data['potencial'], 2, ',', '.'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header py-3">
                <h5 class="m-0">Produtos Mais Vendidos</h5>
            </div>
            <div class="card-body">
                <canvas id="products-chart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header py-3">
                <h5 class="m-0">Níveis de Estoque</h5>
            </div>
            <div class="card-body">
                <canvas id="stock-chart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header py-3">
                <h5 class="m-0">Vendas por Caixa</h5>
            </div>
            <div class="card-body">
                <canvas id="register-chart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header py-3">
                <h5 class="m-0">Vendas por Operador</h5>
            </div>
            <div class="card-body">
                <canvas id="users-chart"></canvas>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 0;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header h5 {
    color: #495057;
    font-size: 1rem;
    font-weight: 500;
}

.card-body {
    min-height: 250px;
    padding: 1.25rem;
}

canvas {
    width: 100% !important;
    height: 100% !important;
}

.gauge-container canvas {
    max-height: 150px;
}
</style>

<script>
// Helper function to create charts with consistent options
function createChart(id, type, data, options = {}) {
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { 
                    boxWidth: 12,
                    padding: 15
                }
            }
        }
    };

    return new Chart(document.getElementById(id), {
        type: type,
        data: data,
        options: { ...defaultOptions, ...options }
    });
}

// Payment chart
createChart('payment-chart', 'pie', {
    labels: ['Dinheiro', 'Pix', 'Cartão'],
    datasets: [{
        data: [
            <?php echo $data['pagamentos']['Dinheiro']; ?>,
            <?php echo $data['pagamentos']['Pix']; ?>,
            <?php echo $data['pagamentos']['Cartão']; ?>
        ],
        backgroundColor: ['#28a745', '#17a2b8', '#ffc107']
    }]
});

// Products chart
createChart('products-chart', 'pie', {
    labels: [<?php echo implode(',', array_map(function($key) { return "'$key'"; }, array_keys($data['produtos']))); ?>],
    datasets: [{
        data: [<?php echo implode(',', array_values($data['produtos'])); ?>],
        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
    }]
}, {
    plugins: {
        legend: {
            position: 'right'
        }
    }
});

// Stock chart
createChart('stock-chart', 'bar', {
    labels: [<?php echo implode(',', array_map(function($key) { return "'$key'"; }, array_keys($data['estoque']))); ?>],
    datasets: [{
        label: 'Em Estoque',
        data: [<?php echo implode(',', array_values($data['estoque'])); ?>],
        backgroundColor: '#007bff'
    }]
}, {
    indexAxis: 'y',
    plugins: {
        legend: { display: false }
    }
});

// Register chart
const registerLabels = [<?php 
    echo implode(',', array_map(function($key) { 
        return "'Caixa $key'"; 
    }, array_keys($data['caixas']))); 
?>];

const registerData = [<?php 
    echo implode(',', array_map(function($data) { 
        return $data['total']; 
    }, $data['caixas'])); 
?>];

createChart('register-chart', 'bar', {
    labels: registerLabels,
    datasets: [{
        label: 'Total de Vendas (R$)',
        data: registerData,
        backgroundColor: '#20c997'
    }]
});

// Users chart
const userLabels = [<?php 
    echo implode(',', array_map(function($key) { 
        return "'$key'"; 
    }, array_keys($data['usuarios']))); 
?>];

const userData = [<?php 
    echo implode(',', array_map(function($data) { 
        return $data['total']; 
    }, $data['usuarios'])); 
?>];

createChart('users-chart', 'bar', {
    labels: userLabels,
    datasets: [{
        label: 'Total de Vendas (R$)',
        data: userData,
        backgroundColor: '#6f42c1'
    }]
});

// Gauge chart
createChart('gauge-chart', 'doughnut', {
    datasets: [{
        data: [<?php echo $data['potencial']; ?>, 100000 - <?php echo $data['potencial']; ?>],
        backgroundColor: ['rgba(75, 192, 192, 0.8)', 'rgba(220, 220, 220, 0.5)'],
        circumference: 180,
        rotation: 270
    }]
}, {
    responsive: true,
    maintainAspectRatio: true,
    cutout: '70%',
    plugins: {
        legend: { display: false },
        tooltip: { enabled: false }
    }
});
</script>

<?php include 'footer.php'; ?> 