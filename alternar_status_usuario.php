<?php
require_once 'db.php';
require_once 'check_admin.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: usuarios.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Get current status
$sql = "SELECT ativo FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $novo_status = $row['ativo'] ? 0 : 1; // Toggle status
    
    // Update user status
    $update_sql = "UPDATE usuarios SET ativo = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, 'ii', $novo_status, $id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        $_SESSION['message'] = 'Status do usuário alterado com sucesso!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Erro ao alterar status do usuário: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'danger';
    }
} else {
    $_SESSION['message'] = 'Usuário não encontrado.';
    $_SESSION['message_type'] = 'danger';
}

header("Location: usuarios.php");
exit;
?> 