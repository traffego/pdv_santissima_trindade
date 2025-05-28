<?php
require_once 'db.php';
require_once 'check_admin.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: produtos.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Check if the product is being used in any sale
$sql_check = "SELECT COUNT(*) as count FROM itens_venda WHERE produto_id = $id";
$result_check = mysqli_query($conn, $sql_check);
$row_check = mysqli_fetch_assoc($result_check);

if ($row_check['count'] > 0) {
    // Cannot delete, product has sales
    $_SESSION['message'] = 'Não é possível excluir este produto pois ele está associado a vendas.';
    $_SESSION['message_type'] = 'danger';
    header("Location: produtos.php");
    exit;
}

// Delete the product
$sql = "DELETE FROM produtos WHERE id = $id";
if (mysqli_query($conn, $sql)) {
    $_SESSION['message'] = 'Produto excluído com sucesso!';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Erro ao excluir produto: ' . mysqli_error($conn);
    $_SESSION['message_type'] = 'danger';
}

header("Location: produtos.php");
exit;
?> 