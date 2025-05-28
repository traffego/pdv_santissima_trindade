<?php
require_once 'db.php';
require_once 'check_admin.php';

// Get users from the database
$sql = "SELECT * FROM usuarios ORDER BY id";
$result = mysqli_query($conn, $sql);

include 'header.php';

// Display session messages if any
if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
    echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">
            ' . $_SESSION['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    
    // Clear session messages
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<div class="d-flex justify-content-between mb-3">
    <h3>Gerenciamento de Usuários</h3>
    <a href="adicionar_usuario.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Usuário
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Usuário</th>
                        <th>Caixa</th>
                        <th>Status</th>
                        <th>Último Login</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['nome']; ?></td>
                                <td><?php echo $row['usuario']; ?></td>
                                <td>Caixa <?php echo $row['caixa_numero']; ?></td>
                                <td>
                                    <?php if ($row['ativo']): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $row['ultimo_login'] ? date('d/m/Y H:i', strtotime($row['ultimo_login'])) : 'Nunca' ?>
                                </td>
                                <td>
                                    <a href="editar_usuario.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($row['id'] != $_SESSION['usuario_id']): // Prevent deleting own account ?>
                                    <a href="excluir_usuario.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="alternar_status_usuario.php?id=<?php echo $row['id']; ?>" class="btn btn-sm <?php echo $row['ativo'] ? 'btn-secondary' : 'btn-success'; ?>">
                                        <i class="fas <?php echo $row['ativo'] ? 'fa-toggle-off' : 'fa-toggle-on'; ?>"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Nenhum usuário cadastrado</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 