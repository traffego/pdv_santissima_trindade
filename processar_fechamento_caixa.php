<?php
require_once 'db.php';
require_once 'check_login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caixa_id = intval($_POST['caixa_id']);
    $valor_dinheiro = floatval($_POST['valor_dinheiro']);
    $observacoes = mysqli_real_escape_string($conn, $_POST['observacoes']);
    
    // Verificar se o caixa existe e está aberto
    $sql_check = "SELECT * FROM controle_caixa WHERE id = ? AND status = 'aberto'";
    $stmt = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt, "i", $caixa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        $_SESSION['message'] = "Caixa não encontrado ou já está fechado!";
        $_SESSION['message_type'] = "danger";
        header("Location: controle_caixa.php");
        exit;
    }
    
    $caixa = mysqli_fetch_assoc($result);
    
    // Calcular diferença entre valor informado e esperado em dinheiro
    $valor_esperado = $caixa['valor_inicial'] + $caixa['valor_vendas_dinheiro'] - $caixa['valor_sangrias'];
    $diferenca = $valor_dinheiro - $valor_esperado;
    
    // Atualizar registro do caixa
    $sql = "UPDATE controle_caixa SET 
            status = 'fechado',
            data_fechamento = NOW(),
            valor_final = ?,
            observacoes = CONCAT(observacoes, '\n\nObservações do fechamento: ', ?)
            WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "dsi", $valor_dinheiro, $observacoes, $caixa_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Caixa fechado com sucesso!";
        if ($diferenca != 0) {
            $_SESSION['message'] .= sprintf(
                " %s de R$ %.2f %s", 
                abs($diferenca) > 0 ? "Sobra" : "Falta",
                abs($diferenca),
                $diferenca > 0 ? "no caixa" : "em caixa"
            );
        }
        $_SESSION['message_type'] = "success";
        
        // Atualizar vendas e sangrias com o ID do caixa
        $sql_update_vendas = "UPDATE vendas SET controle_caixa_id = ? 
                             WHERE usuario_id = ? 
                             AND caixa = ? 
                             AND controle_caixa_id IS NULL";
        
        $stmt = mysqli_prepare($conn, $sql_update_vendas);
        mysqli_stmt_bind_param($stmt, "iii", $caixa_id, $caixa['usuario_id'], $caixa['caixa_numero']);
        mysqli_stmt_execute($stmt);
        
        $sql_update_sangrias = "UPDATE sangrias SET controle_caixa_id = ? 
                               WHERE usuario_id = ? 
                               AND caixa = ? 
                               AND controle_caixa_id IS NULL";
        
        $stmt = mysqli_prepare($conn, $sql_update_sangrias);
        mysqli_stmt_bind_param($stmt, "iii", $caixa_id, $caixa['usuario_id'], $caixa['caixa_numero']);
        mysqli_stmt_execute($stmt);
    } else {
        $_SESSION['message'] = "Erro ao fechar o caixa: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
    
    header("Location: controle_caixa.php");
    exit;
} else {
    header("Location: controle_caixa.php");
    exit;
} 