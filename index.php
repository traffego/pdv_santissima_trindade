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

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Total de Vendas Hoje</h5>
                <h2 class="text-primary">R$ <?php echo number_format($data['vendas_hoje'], 2, ',', '.'); ?></h2>
                <p class="text-muted"><?php echo date('d/m/Y'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Vendas por Pagamento</h5>
                <canvas id="payment-chart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-light">
                <div class="text-center">
                    <img src="logo.jpeg" alt="Logo" class="img-fluid" style="max-height: 60px;">
                </div>
            </div>
            <div class="card-body text-center">
                <h5 class="card-title">Faturamento Potencial</h5>
                <div class="gauge-container">
                    <canvas id="gauge-chart"></canvas>
                </div>
                <p class="mt-2">R$ <?php echo number_format($data['potencial'], 2, ',', '.'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Produtos Mais Vendidos</h5>
            </div>
            <div class="card-body">
                <canvas id="products-chart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Níveis de Estoque</h5>
            </div>
            <div class="card-body">
                <canvas id="stock-chart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="m-0">Vendas por Caixa</h5>
            </div>
            <div class="card-body">
                <canvas id="register-chart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="m-0">Vendas por Operador</h5>
            </div>
            <div class="card-body">
                <canvas id="users-chart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Helper function to create charts
function createChart(id, type, data, options) {
    return new Chart(document.getElementById(id), {
        type: type,
        data: data,
        options: options
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
}, {
    responsive: true,
    plugins: {
        legend: {
            position: 'bottom',
            labels: { boxWidth: 12 }
        }
    }
});

// Products chart
createChart('products-chart', 'pie', {
    labels: [<?php echo implode(',', array_map(function($key) { return "'$key'"; }, array_keys($data['produtos']))); ?>],
    datasets: [{
        data: [<?php echo implode(',', array_values($data['produtos'])); ?>],
        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
    }]
}, {
    responsive: true,
    plugins: {
        legend: {
            position: 'right',
            labels: { boxWidth: 12 }
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
    responsive: true,
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

const registerSales = [<?php 
    echo implode(',', array_map(function($item) { 
        return $item['vendas']; 
    }, array_values($data['caixas']))); 
?>];

const registerValues = [<?php 
    echo implode(',', array_map(function($item) { 
        return $item['total']; 
    }, array_values($data['caixas']))); 
?>];

createChart('register-chart', 'bar', {
    labels: registerLabels,
    datasets: [
        {
            label: 'Número de Vendas',
            data: registerSales,
            backgroundColor: '#6c757d',
            borderWidth: 1,
            yAxisID: 'y'
        },
        {
            label: 'Valor Total (R$)',
            data: registerValues,
            backgroundColor: '#007bff',
            borderWidth: 1,
            yAxisID: 'y1'
        }
    ]
}, {
    responsive: true,
    plugins: { legend: { position: 'top' } },
    scales: {
        y: {
            type: 'linear',
            display: true,
            position: 'left',
            title: { display: true, text: 'Número de Vendas' }
        },
        y1: {
            type: 'linear',
            display: true,
            position: 'right',
            title: { display: true, text: 'Valor Total (R$)' },
            grid: { drawOnChartArea: false }
        }
    }
});

// Users chart
const userLabels = [<?php 
    echo implode(',', array_map(function($key) { 
        return "'$key'"; 
    }, array_keys($data['usuarios']))); 
?>];

const userSales = [<?php 
    echo implode(',', array_map(function($item) { 
        return $item['vendas']; 
    }, array_values($data['usuarios']))); 
?>];

const userValues = [<?php 
    echo implode(',', array_map(function($item) { 
        return $item['total']; 
    }, array_values($data['usuarios']))); 
?>];

createChart('users-chart', 'bar', {
    labels: userLabels,
    datasets: [
        {
            label: 'Vendas',
            data: userSales,
            backgroundColor: '#fd7e14',
            borderWidth: 1,
        },
        {
            label: 'Valor (R$)',
            data: userValues,
            backgroundColor: '#20c997',
            borderWidth: 1,
        }
    ]
}, {
    responsive: true,
    plugins: { legend: { position: 'top' } },
    indexAxis: 'y',
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