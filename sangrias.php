<?php
require_once 'db.php';
require_once 'check_login.php';

// Get sangrias from database with user information
$sql = "SELECT s.*, u.nome as usuario_nome 
        FROM sangrias s 
        LEFT JOIN usuarios u ON s.usuario_id = u.id 
        ORDER BY s.data DESC";
$result = mysqli_query($conn, $sql);

// Calculate total
$sql_total = "SELECT SUM(valor) as total FROM sangrias";
$result_total = mysqli_query($conn, $sql_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_sangrias = $row_total['total'] ? $row_total['total'] : 0;

include 'header.php';

// Display messages if any
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

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3>Sangrias</h3>
        <p class="text-muted">Total de sangrias: R$ <?php echo number_format($total_sangrias, 2, ',', '.'); ?></p>
    </div>
    <a href="registrar_sangria.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nova Sangria
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Valor</th>
                        <th>Observação</th>
                        <th>Operador</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['data'])); ?></td>
                                <td>R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($row['observacao'])); ?></td>
                                <td><?php echo $row['usuario_nome'] ? $row['usuario_nome'] : 'N/A'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhuma sangria registrada</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 