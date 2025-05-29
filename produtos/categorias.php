<?php
require_once '../db.php';
require_once '../check_admin.php';

// Processar formulário de categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    
    if (!empty($nome)) {
        $sql = "INSERT INTO categorias (nome) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nome);
        
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Categoria adicionada com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao adicionar categoria.";
            $_SESSION['tipo_mensagem'] = "danger";
        }
        
        header("Location: categorias.php");
        exit();
    }
}

// Buscar todas as categorias
$sql = "SELECT id, nome FROM categorias ORDER BY nome";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once '../header.php'; ?>
    
    <div class="container mt-4">
        <h2>Gerenciar Categorias</h2>
        
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-<?= $_SESSION['tipo_mensagem'] ?>" role="alert">
                <?= $_SESSION['mensagem'] ?>
            </div>
            <?php 
            unset($_SESSION['mensagem']);
            unset($_SESSION['tipo_mensagem']);
            ?>
        <?php endif; ?>

        <!-- Formulário de Adição de Categoria -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Nova Categoria</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="categorias.php">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Categoria</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Adicionar Categoria</button>
                </form>
            </div>
        </div>

        <!-- Lista de Categorias -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Categorias Existentes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($categoria = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($categoria['nome']) ?></td>
                                <td>
                                    <a href="editar_categoria.php?id=<?= $categoria['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="excluir_categoria.php?id=<?= $categoria['id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Tem certeza que deseja excluir esta categoria? Se houver produtos vinculados a ela, a exclusão não será permitida.');">
                                        <i class="fas fa-trash"></i> Excluir
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 