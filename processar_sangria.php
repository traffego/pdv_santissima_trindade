<?php
require_once 'db.php';
require_once 'check_login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $_SESSION['message'] = "Não é possível realizar sangrias com o caixa fechado!";
        $_SESSION['message_type'] = "danger";
        header("Location: sangrias.php");
        exit;
    }
    
    $caixa_atual = mysqli_fetch_assoc($result_caixa);
    $controle_caixa_id = $caixa_atual['id'];
    
    // Iniciar transação
    mysqli_begin_transaction($conn);
    
    try {
        $valor = floatval($_POST['valor']);
        $observacao = mysqli_real_escape_string($conn, $_POST['observacao']);
        $caixa = $_SESSION['caixa_numero'];
        $usuario_id = $_SESSION['usuario_id'];
        
        // Inserir sangria
        $sql = "INSERT INTO sangrias (valor, data_sangria, observacao, caixa, usuario_id, controle_caixa_id) 
                VALUES (?, NOW(), ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "dsiii", $valor, $observacao, $caixa, $usuario_id, $controle_caixa_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erro ao registrar sangria: " . mysqli_error($conn));
        }
        
        // Atualizar valor de sangrias no controle de caixa
        $sql_update = "UPDATE controle_caixa 
                      SET valor_sangrias = valor_sangrias + ? 
                      WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt, "di", $valor, $controle_caixa_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erro ao atualizar controle de caixa: " . mysqli_error($conn));
        }
        
        mysqli_commit($conn);
        
        $_SESSION['message'] = "Sangria registrada com sucesso!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    header("Location: sangrias.php");
    exit;
} else {
    header("Location: sangrias.php");
    exit;
} 