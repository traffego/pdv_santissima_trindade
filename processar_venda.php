<?php
require_once 'db.php';
require_once 'check_login.php';

// Verificar e definir número de caixa para administradores se não estiver definido
if ($_SESSION['nivel'] === 'administrador' && (!isset($_SESSION['caixa_numero']) || empty($_SESSION['caixa_numero']))) {
    $_SESSION['caixa_numero'] = 999; // Número padrão para admin
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: vender.php');
    exit;
}

// Verificar se o caixa está aberto
$sql_check_caixa = "SELECT id FROM controle_caixa 
                    WHERE usuario_id = ? 
                    AND caixa_numero = ? 
                    AND status = 'aberto'";

$stmt = mysqli_prepare($conn, $sql_check_caixa);
mysqli_stmt_bind_param($stmt, "ii", $_SESSION['usuario_id'], $_SESSION['caixa_numero']);
mysqli_stmt_execute($stmt);
$result_caixa = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result_caixa) === 0) {
    $_SESSION['message'] = "Não é possível realizar vendas com o caixa fechado!";
    $_SESSION['message_type'] = "danger";
    header("Location: vender.php");
    exit;
}

$caixa_atual = mysqli_fetch_assoc($result_caixa);
$controle_caixa_id = $caixa_atual['id'];

// Get form data
$valor_total = isset($_POST['valor_total']) ? mysqli_real_escape_string($conn, $_POST['valor_total']) : '';
$forma_pagamento = isset($_POST['forma_pagamento']) ? mysqli_real_escape_string($conn, $_POST['forma_pagamento']) : '';
$caixa = isset($_POST['caixa']) ? mysqli_real_escape_string($conn, $_POST['caixa']) : '';
$usuario_id = $_SESSION['usuario_id']; // Get user ID from session
$produto_ids = isset($_POST['produto_id']) ? $_POST['produto_id'] : [];
$quantidades = isset($_POST['quantidade']) ? $_POST['quantidade'] : [];
$precos = isset($_POST['preco']) ? $_POST['preco'] : [];

// Debug information
$missing_fields = [];
if (empty($valor_total) || $valor_total <= 0) $missing_fields[] = 'Total da venda';
if (empty($forma_pagamento)) $missing_fields[] = 'Forma de pagamento';
if (empty($caixa)) $missing_fields[] = 'Número do caixa';
if (empty($produto_ids)) $missing_fields[] = 'Produtos no carrinho';

// Validate form data
if (!empty($missing_fields)) {
    $_SESSION['message'] = '<strong>ATENÇÃO!</strong> Os seguintes campos são obrigatórios: <br><ul class="mb-0 ps-3"><li>' . implode('</li><li>', $missing_fields) . '</li></ul>';
    $_SESSION['message_type'] = 'danger';
    header('Location: vender.php');
    exit;
}

// Outra verificação de segurança
if (count($produto_ids) == 0) {
    $_SESSION['message'] = '<strong>ATENÇÃO!</strong> Nenhum produto foi adicionado ao carrinho.';
    $_SESSION['message_type'] = 'danger';
    header('Location: vender.php');
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Insert sale
    $sql = "INSERT INTO vendas (data_venda, valor_total, forma_pagamento, caixa, usuario_id, controle_caixa_id) VALUES (NOW(), ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "dsiii", $valor_total, $forma_pagamento, $caixa, $usuario_id, $controle_caixa_id);
    mysqli_stmt_execute($stmt);
    
    // Get the sale ID
    $venda_id = mysqli_insert_id($conn);
    
    // Insert sale items and update stock
    foreach ($produto_ids as $index => $produto_id) {
        $quantidade = isset($quantidades[$produto_id]) ? $quantidades[$produto_id] : 0;
        $preco = $precos[$index];
        
        if ($quantidade > 0) {
            // Check if there's enough stock
            $check_sql = "SELECT quantidade_estoque FROM produtos WHERE id = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, 'i', $produto_id);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            $produto = mysqli_fetch_assoc($check_result);
            
            if (!$produto || $produto['quantidade_estoque'] < $quantidade) {
                throw new Exception('Estoque insuficiente para um ou mais produtos.');
            }
            
            // Insert sale item
            $item_sql = "INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)";
            $item_stmt = mysqli_prepare($conn, $item_sql);
            mysqli_stmt_bind_param($item_stmt, 'iiid', $venda_id, $produto_id, $quantidade, $preco);
            mysqli_stmt_execute($item_stmt);
            
            // Update product stock
            $update_sql = "UPDATE produtos SET quantidade_estoque = quantidade_estoque - ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, 'ii', $quantidade, $produto_id);
            mysqli_stmt_execute($update_stmt);
            
            mysqli_stmt_close($item_stmt);
            mysqli_stmt_close($update_stmt);
        }
    }
    
    // Update totals in control of cash
    $valor_total = floatval($valor_total);
    $sql_update_caixa = "UPDATE controle_caixa SET 
                        valor_vendas = valor_vendas + ?
                        WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql_update_caixa);
    mysqli_stmt_bind_param($stmt, "di", $valor_total, $controle_caixa_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao atualizar controle de caixa: " . mysqli_error($conn));
    }
    
    // Commit the transaction
    mysqli_commit($conn);
    
    // Preparar mensagem de sucesso formatada
    $_SESSION['message'] = '<strong>Venda Realizada!</strong> A venda #' . $venda_id . ' foi processada com sucesso.';
    $_SESSION['message_type'] = 'success';
    $_SESSION['venda_concluida'] = true;
    $_SESSION['venda_id'] = $venda_id;
    
    // Redirecionar de volta para a página de vendas
    header('Location: vender.php');
    exit;
    
} catch (Exception $e) {
    // Rollback the transaction on error
    mysqli_rollback($conn);
    
    $_SESSION['message'] = 'Erro ao processar venda: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    
    header('Location: vender.php');
    exit;
}
?> 