<?php
require_once 'db.php';
require_once 'check_login.php';

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log para arquivo
function logError($message) {
    $logFile = 'caixa_error.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: controle_caixa.php');
    exit;
}

// Obter dados do formulário
$caixa_id = $_POST['caixa_id'] ?? null;
$valor_dinheiro = str_replace(',', '.', $_POST['valor_dinheiro'] ?? '0');
$valor_pix = str_replace(',', '.', $_POST['valor_pix'] ?? '0');
$valor_cartao = str_replace(',', '.', $_POST['valor_cartao'] ?? '0');
$observacoes = $_POST['observacoes'] ?? '';

// Log dos valores recebidos
logError("Dados recebidos: caixa_id=$caixa_id, valor_dinheiro=$valor_dinheiro, valor_pix=$valor_pix, valor_cartao=$valor_cartao");

// Validar dados
if (!$caixa_id || !is_numeric($valor_dinheiro) || !is_numeric($valor_pix) || !is_numeric($valor_cartao)) {
    $error = "Dados inválidos para fechamento do caixa. caixa_id=" . ($caixa_id ?? 'null') . 
             ", valor_dinheiro=" . ($valor_dinheiro ?? 'null') .
             ", valor_pix=" . ($valor_pix ?? 'null') .
             ", valor_cartao=" . ($valor_cartao ?? 'null');
    logError($error);
    $_SESSION['error'] = $error;
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
        throw new Exception("Caixa não encontrado ou já está fechado. ID: $caixa_id");
    }

    // Log do caixa encontrado
    logError("Caixa encontrado: " . json_encode($caixa));

    // Verificar se o usuário tem permissão para fechar este caixa
    if ($caixa['usuario_id'] != $_SESSION['usuario_id']) {
        throw new Exception("Você não tem permissão para fechar este caixa. Usuario atual: " . $_SESSION['usuario_id'] . ", Usuario do caixa: " . $caixa['usuario_id']);
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

    // Log dos totais calculados
    logError("Totais calculados: " . json_encode($totais));

    // Buscar total de sangrias
    $sql_sangrias = "SELECT COALESCE(SUM(valor), 0) as total_sangrias 
                     FROM sangrias 
                     WHERE controle_caixa_id = ?";
    $stmt = mysqli_prepare($conn, $sql_sangrias);
    mysqli_stmt_bind_param($stmt, "i", $caixa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $sangrias = mysqli_fetch_assoc($result);
    $total_sangrias = floatval($sangrias['total_sangrias']);

    // Log das sangrias
    logError("Total sangrias: " . json_encode($sangrias));

    // Calcular diferença em dinheiro
    $valor_esperado_dinheiro = $caixa['valor_inicial'] + $totais['total_dinheiro'] - $total_sangrias;
    $diferenca = $valor_dinheiro - $valor_esperado_dinheiro;

    // Log dos cálculos
    logError("Valor esperado: $valor_esperado_dinheiro, Diferença: $diferenca");

    // Calcular valor final (soma de todas as formas de pagamento)
    $valor_final = floatval($valor_dinheiro) + floatval($valor_pix) + floatval($valor_cartao);

    // Log dos valores finais
    logError("Valor final: $valor_final");

    // Atualizar caixa
    $sql_update = "UPDATE controle_caixa SET 
        status = 'fechado',
        data_fechamento = NOW(),
        valor_final = ?,
        valor_sangrias = ?,
        valor_vendas = ?,
        valor_vendas_dinheiro = ?,
        valor_vendas_pix = ?,
        valor_vendas_cartao = ?,
        observacoes = ?
    WHERE id = ? AND status = 'aberto'";
    
    $stmt = mysqli_prepare($conn, $sql_update);
    if (!$stmt) {
        throw new Exception("Erro ao preparar query de atualização: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ddddddsi", 
        $valor_final,
        $total_sangrias,
        $totais['total_geral'],
        $totais['total_dinheiro'],
        $totais['total_pix'],
        $totais['total_cartao'],
        $observacoes,
        $caixa_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao executar atualização: " . mysqli_stmt_error($stmt));
    }

    // Log da atualização
    logError("Caixa atualizado com sucesso. Affected rows: " . mysqli_stmt_affected_rows($stmt));

    // Verificar se a atualização funcionou
    $sql_check = "SELECT status FROM controle_caixa WHERE id = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "i", $caixa_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $status_atual = mysqli_fetch_assoc($result_check);
    logError("Status após atualização: " . json_encode($status_atual));

    // Confirmar transação
    mysqli_commit($conn);

    $_SESSION['success'] = "Caixa fechado com sucesso!";
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    // Reverter transação em caso de erro
    mysqli_rollback($conn);
    
    $error = "Erro ao fechar o caixa: " . $e->getMessage();
    logError($error);
    $_SESSION['error'] = $error;
    header('Location: controle_caixa.php');
    exit;
} 