<?php
require_once 'db.php';
require_once 'check_login.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: controle_caixa.php');
    exit;
}

// Obter dados do formulário
$caixa_id = $_POST['caixa_id'] ?? null;
$valor_dinheiro = str_replace(',', '.', $_POST['valor_dinheiro']);
$valor_pix = str_replace(',', '.', $_POST['valor_pix']);
$valor_cartao = str_replace(',', '.', $_POST['valor_cartao']);
$observacoes = $_POST['observacoes'] ?? '';

// Validar dados
if (!$caixa_id || !is_numeric($valor_dinheiro)) {
    $_SESSION['error'] = "Dados inválidos para fechamento do caixa.";
    header('Location: controle_caixa.php');
    exit;
}

try {
    // Iniciar transação
    mysqli_begin_transaction($conn);

    // Buscar informações do caixa
    $sql_caixa = "SELECT * FROM controle_caixa WHERE id = ? AND status = 'aberto' LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql_caixa);
    mysqli_stmt_bind_param($stmt, "i", $caixa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $caixa = mysqli_fetch_assoc($result);

    if (!$caixa) {
        throw new Exception("Caixa não encontrado ou já está fechado.");
    }

    // Verificar se o usuário tem permissão para fechar este caixa
    if ($caixa['usuario_id'] != $_SESSION['usuario_id']) {
        throw new Exception("Você não tem permissão para fechar este caixa.");
    }

    // Calcular totais
    $sql_totais = "SELECT 
        COALESCE(SUM(CASE WHEN forma_pagamento = 'Dinheiro' THEN valor_total ELSE 0 END), 0) as total_dinheiro,
        COALESCE(SUM(CASE WHEN forma_pagamento = 'Pix' THEN valor_total ELSE 0 END), 0) as total_pix,
        COALESCE(SUM(CASE WHEN forma_pagamento = 'Cartão' THEN valor_total ELSE 0 END), 0) as total_cartao,
        COALESCE(SUM(valor_total), 0) as total_geral
    FROM vendas 
    WHERE controle_caixa_id = ?";

    $stmt = mysqli_prepare($conn, $sql_totais);
    mysqli_stmt_bind_param($stmt, "i", $caixa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $totais = mysqli_fetch_assoc($result);

    // Buscar total de sangrias
    $sql_sangrias = "SELECT COALESCE(SUM(valor), 0) as total_sangrias 
                     FROM sangrias 
                     WHERE controle_caixa_id = ?";
    $stmt = mysqli_prepare($conn, $sql_sangrias);
    mysqli_stmt_bind_param($stmt, "i", $caixa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $sangrias = mysqli_fetch_assoc($result);

    // Calcular diferença em dinheiro
    $valor_esperado_dinheiro = $caixa['valor_inicial'] + $totais['total_dinheiro'] - $sangrias['total_sangrias'];
    $diferenca = $valor_dinheiro - $valor_esperado_dinheiro;

    // Atualizar caixa
    $sql_update = "UPDATE controle_caixa SET 
        status = 'fechado',
        data_fechamento = NOW(),
        valor_dinheiro = ?,
        valor_pix = ?,
        valor_cartao = ?,
        valor_total = ?,
        diferenca = ?,
        observacoes_fechamento = ?
    WHERE id = ?";

    $valor_total = $valor_dinheiro + $valor_pix + $valor_cartao;
    
    $stmt = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt, "dddddsi", 
        $valor_dinheiro,
        $valor_pix,
        $valor_cartao,
        $valor_total,
        $diferenca,
        $observacoes,
        $caixa_id
    );
    mysqli_stmt_execute($stmt);

    // Confirmar transação
    mysqli_commit($conn);

    $_SESSION['success'] = "Caixa fechado com sucesso!";
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    // Reverter transação em caso de erro
    mysqli_rollback($conn);
    
    $_SESSION['error'] = "Erro ao fechar o caixa: " . $e->getMessage();
    header('Location: controle_caixa.php');
    exit;
} 