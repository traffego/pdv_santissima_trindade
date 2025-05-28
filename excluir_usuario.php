<?php
require_once 'db.php';
require_once 'check_admin.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: usuarios.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Prevent self-deletion
if ($id == $_SESSION['usuario_id']) {
    $_SESSION['message'] = 'Você não pode excluir sua própria conta.';
    $_SESSION['message_type'] = 'danger';
    header("Location: usuarios.php");
    exit;
}

// Check if user has associated sales or withdrawals
$check_sales = "SELECT COUNT(*) as count FROM vendas WHERE usuario_id = ?";
$stmt_sales = mysqli_prepare($conn, $check_sales);
mysqli_stmt_bind_param($stmt_sales, 'i', $id);
mysqli_stmt_execute($stmt_sales);
$result_sales = mysqli_stmt_get_result($stmt_sales);
$row_sales = mysqli_fetch_assoc($result_sales);

$check_withdrawals = "SELECT COUNT(*) as count FROM sangrias WHERE usuario_id = ?";
$stmt_withdrawals = mysqli_prepare($conn, $check_withdrawals);
mysqli_stmt_bind_param($stmt_withdrawals, 'i', $id);
mysqli_stmt_execute($stmt_withdrawals);
$result_withdrawals = mysqli_stmt_get_result($stmt_withdrawals);
$row_withdrawals = mysqli_fetch_assoc($result_withdrawals);

if ($row_sales['count'] > 0 || $row_withdrawals['count'] > 0) {
    // Cannot delete, user has associated records
    $_SESSION['message'] = 'Não é possível excluir este usuário pois ele possui vendas ou sangrias registradas.';
    $_SESSION['message_type'] = 'danger';
    header("Location: usuarios.php");
    exit;
}

// Delete the user
$sql = "DELETE FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['message'] = 'Usuário excluído com sucesso!';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Erro ao excluir usuário: ' . mysqli_error($conn);
    $_SESSION['message_type'] = 'danger';
}

header("Location: usuarios.php");
exit;
?> 