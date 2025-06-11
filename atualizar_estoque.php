<?php
require_once 'db.php';
require_once 'check_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produto_id']) && isset($_POST['quantidade'])) {
    $produto_id = intval($_POST['produto_id']);
    $quantidade = intval($_POST['quantidade']);
    
    try {
        // Buscar quantidade atual
        $sql_atual = "SELECT quantidade_estoque FROM produtos WHERE id = ?";
        $stmt_atual = mysqli_prepare($conn, $sql_atual);
        mysqli_stmt_bind_param($stmt_atual, "i", $produto_id);
        mysqli_stmt_execute($stmt_atual);
        $result_atual = mysqli_stmt_get_result($stmt_atual);
        $row = mysqli_fetch_assoc($result_atual);
        
        if (!$row) {
            throw new Exception("Produto não encontrado");
        }
        
        // Calcular nova quantidade
        $nova_quantidade = $row['quantidade_estoque'] + $quantidade;
        
        if ($nova_quantidade < 0) {
            throw new Exception("Quantidade resultante não pode ser negativa");
        }
        
        // Atualizar estoque
        $sql = "UPDATE produtos SET quantidade_estoque = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $nova_quantidade, $produto_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erro ao atualizar estoque");
        }
        
        // Retornar resposta de sucesso
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Estoque atualizado com sucesso',
            'nova_quantidade' => $nova_quantidade
        ]);
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Se não for POST, redirecionar para produtos.php
header("Location: produtos.php");
exit;
?> 