<?php
require_once 'db.php';
require_once 'check_login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $caixa_numero = $_SESSION['caixa_numero'];
    $valor_inicial = floatval($_POST['valor_inicial']);
    $observacoes = mysqli_real_escape_string($conn, $_POST['observacoes']);
    
    // Verificar se já existe um caixa aberto
    $sql_check = "SELECT id FROM controle_caixa 
                  WHERE usuario_id = ? 
                  AND caixa_numero = ? 
                  AND status = 'aberto'";
    
    $stmt = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $caixa_numero);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['message'] = "Já existe um caixa aberto para este usuário!";
        $_SESSION['message_type'] = "danger";
        header("Location: controle_caixa.php");
        exit;
    }
    
    // Inserir novo registro de abertura de caixa
    $sql = "INSERT INTO controle_caixa (
                usuario_id, 
                caixa_numero, 
                data_abertura, 
                valor_inicial, 
                observacoes, 
                status
            ) VALUES (?, ?, NOW(), ?, ?, 'aberto')";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iids", $usuario_id, $caixa_numero, $valor_inicial, $observacoes);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Caixa aberto com sucesso!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Erro ao abrir o caixa: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
    
    header("Location: controle_caixa.php");
    exit;
} else {
    header("Location: controle_caixa.php");
    exit;
} 