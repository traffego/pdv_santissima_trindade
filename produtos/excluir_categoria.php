<?php
require_once '../db.php';
require_once '../check_admin.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem'] = "ID da categoria não fornecido.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: categorias.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Verificar se existem produtos usando esta categoria
$sql_check = "SELECT COUNT(*) as total FROM produtos WHERE categoria_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$result = $stmt_check->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    $_SESSION['mensagem'] = "Não é possível excluir esta categoria pois existem produtos vinculados a ela.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: categorias.php");
    exit;
}

// Se não houver produtos, proceder com a exclusão
$sql = "DELETE FROM categorias WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['mensagem'] = "Categoria excluída com sucesso!";
    $_SESSION['tipo_mensagem'] = "success";
} else {
    $_SESSION['mensagem'] = "Erro ao excluir categoria: " . $conn->error;
    $_SESSION['tipo_mensagem'] = "danger";
}

header("Location: categorias.php");
exit; 