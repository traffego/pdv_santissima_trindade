<?php
require_once 'db.php';
require_once 'check_login.php';

// Verificar se o ID da venda foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: vender.php');
    exit;
}

$venda_id = mysqli_real_escape_string($conn, $_GET['id']);

// Buscar os dados da venda
$sql_venda = "SELECT v.*, u.nome as nome_usuario, DATE_FORMAT(v.data, '%d/%m/%Y %H:%i') as data_formatada 
              FROM vendas v 
              LEFT JOIN usuarios u ON v.usuario_id = u.id 
              WHERE v.id = ?";
$stmt_venda = mysqli_prepare($conn, $sql_venda);
mysqli_stmt_bind_param($stmt_venda, 'i', $venda_id);
mysqli_stmt_execute($stmt_venda);
$result_venda = mysqli_stmt_get_result($stmt_venda);

if (mysqli_num_rows($result_venda) == 0) {
    header('Location: vender.php');
    exit;
}

$venda = mysqli_fetch_assoc($result_venda);

// Buscar os itens da venda
$sql_itens = "SELECT i.*, p.nome as nome_produto 
              FROM itens_venda i 
              JOIN produtos p ON i.produto_id = p.id 
              WHERE i.venda_id = ?";
$stmt_itens = mysqli_prepare($conn, $sql_itens);
mysqli_stmt_bind_param($stmt_itens, 'i', $venda_id);
mysqli_stmt_execute($stmt_itens);
$result_itens = mysqli_stmt_get_result($stmt_itens);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Venda #<?php echo $venda_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            max-width: 80mm;
            margin: 0 auto;
            padding: 5mm;
        }
        .logo {
            text-align: center;
            margin-bottom: 3mm;
        }
        .logo img {
            max-width: 60mm;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 3mm;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 2mm 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }
        .item-left {
            width: 60%;
        }
        .item-right {
            width: 40%;
            text-align: right;
        }
        .total {
            font-weight: bold;
            margin-top: 2mm;
        }
        .no-print {
            margin-top: 10mm;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="logo.jpeg" alt="Logo">
    </div>
    
    <div class="header">
        <h3>COMPROVANTE DE VENDA</h3>
        <p>VENDA #<?php echo $venda_id; ?></p>
        <p>Data: <?php echo $venda['data_formatada']; ?></p>
        <p>Operador: <?php echo $venda['nome_usuario'] ?? 'N/A'; ?></p>
        <p>Caixa: <?php echo $venda['caixa']; ?></p>
    </div>
    
    <div class="divider"></div>
    
    <div class="items">
        <div class="item">
            <div class="item-left"><strong>Produto</strong></div>
            <div class="item-right"><strong>Qtd x Preço = Subtotal</strong></div>
        </div>
        <div class="divider"></div>
        
        <?php 
        $total = 0;
        while ($item = mysqli_fetch_assoc($result_itens)): 
            $subtotal = $item['quantidade'] * $item['preco_unitario'];
            $total += $subtotal;
        ?>
        <div class="item">
            <div class="item-left"><?php echo $item['nome_produto']; ?></div>
            <div class="item-right">
                <?php echo $item['quantidade']; ?> x 
                R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?> = 
                R$ <?php echo number_format($subtotal, 2, ',', '.'); ?>
            </div>
        </div>
        <?php endwhile; ?>
        
        <div class="divider"></div>
        <div class="item total">
            <div class="item-left">TOTAL</div>
            <div class="item-right">R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></div>
        </div>
        <div class="item">
            <div class="item-left">Forma de Pagamento</div>
            <div class="item-right"><?php echo $venda['forma_pagamento']; ?></div>
        </div>
    </div>
    
    <div class="divider"></div>
    
    <div class="footer">
        <p>Obrigado pela preferência!</p>
        <p>Sistema PDV</p>
    </div>
    
    <div class="no-print text-center">
        <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
        <a href="vender.php" class="btn btn-secondary">Voltar</a>
    </div>
</body>
</html> 