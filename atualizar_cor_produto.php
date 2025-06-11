<?php
require_once 'db.php';
require_once 'check_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produto_id']) && isset($_POST['cor'])) {
    $produto_id = intval($_POST['produto_id']);
    $cor = '#' . $_POST['cor'];
    
    // Validar formato da cor (hexadecimal)
    if (!preg_match('/^#[a-fA-F0-9]{6}$/', $cor)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Formato de cor inválido'
        ]);
        exit;
    }
    
    try {
        $sql = "UPDATE produtos SET cor = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $cor, $produto_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Cor atualizada com sucesso'
            ]);
        } else {
            throw new Exception("Erro ao atualizar cor");
        }
        
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