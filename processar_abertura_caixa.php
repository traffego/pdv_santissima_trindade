<?php
require_once 'db.php';
require_once 'check_login.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: abrir_caixa.php');
    exit;
}

// Obter dados do formulário
$valor_inicial = str_replace(',', '.', $_POST['valor_inicial']);
$observacoes = $_POST['observacoes'] ?? '';
$usuario_id = $_SESSION['usuario_id'];
$caixa_numero = $_SESSION['caixa_numero'];

// Validar dados
if (!is_numeric($valor_inicial) || $valor_inicial < 0) {
    $_SESSION['error'] = "Valor inicial inválido.";
    header('Location: abrir_caixa.php');
    exit;
}

try {
    // Iniciar transação
    mysqli_begin_transaction($conn);

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
        throw new Exception("Já existe um caixa aberto para este usuário.");
    }

    // Inserir novo registro de caixa
    $sql_insert = "INSERT INTO controle_caixa (
        usuario_id,
        caixa_numero,
        data_abertura,
        valor_inicial,
        status,
        observacoes
    ) VALUES (?, ?, NOW(), ?, 'aberto', ?)";

    $stmt = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt, "iids", 
        $usuario_id,
        $caixa_numero,
        $valor_inicial,
        $observacoes
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao abrir o caixa: " . mysqli_error($conn));
    }

    $caixa_id = mysqli_insert_id($conn);

    // Confirmar transação
    mysqli_commit($conn);

    $_SESSION['success'] = "Caixa aberto com sucesso!";
    header('Location: controle_caixa.php');
    exit;

} catch (Exception $e) {
    // Reverter transação em caso de erro
    mysqli_rollback($conn);
    
    $_SESSION['error'] = $e->getMessage();
    header('Location: abrir_caixa.php');
    exit;
} 