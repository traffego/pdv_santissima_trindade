<?php
require_once 'db.php';
require_once 'check_login.php';

// Definir título da página
$pageTitle = "Gestão de Caixas";

// Inicializar variáveis de filtro
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Construir a consulta SQL base
$sql_base = "SELECT 
    cc.*,
    DATE_FORMAT(cc.data_abertura, '%d/%m/%Y %H:%i') as data_abertura_formatada,
    DATE_FORMAT(cc.data_fechamento, '%d/%m/%Y %H:%i') as data_fechamento_formatada,
    u.nome as nome_usuario
FROM controle_caixa cc
LEFT JOIN usuarios u ON cc.usuario_id = u.id
WHERE DATE(cc.data_abertura) BETWEEN ? AND ?";

$params = array($data_inicio, $data_fim);
$types = "ss";

// Se não for administrador, filtrar apenas pelo caixa do usuário
if ($_SESSION['nivel'] !== 'administrador') {
    $sql_base .= " AND cc.caixa_numero = ? AND cc.usuario_id = ?";
    $params[] = $_SESSION['caixa_numero'];
    $params[] = $_SESSION['usuario_id'];
    $types .= "ii";
}

// Filtrar por status se especificado
if (!empty($status)) {
    $sql_base .= " AND cc.status = ?";
    $params[] = $status;
    $types .= "s";
}

// Ordenar por data de abertura decrescente
$sql_base .= " ORDER BY cc.data_abertura DESC";

// Preparar e executar a consulta
$stmt = mysqli_prepare($conn, $sql_base);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

include 'header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php if ($_SESSION['nivel'] === 'administrador'): ?>
                <i class="fas fa-cash-register me-2"></i>Gestão de Todos os Caixas
            <?php else: ?>
                <i class="fas fa-cash-register me-2"></i>Gestão do Caixa <?php echo $_SESSION['caixa_numero']; ?>
            <?php endif; ?>
        </h4>
        <a href="controle_caixa.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Novo Movimento
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header py-2">
            <h6 class="card-title mb-0"><i class="fas fa-filter me-2"></i>Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Período</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="data_inicio" value="<?php echo $data_inicio; ?>">
                        <span class="input-group-text">até</span>
                        <input type="date" class="form-control" name="data_fim" value="<?php echo $data_fim; ?>">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Todos</option>
                        <option value="aberto" <?php echo ($status == 'aberto' ? 'selected' : ''); ?>>Aberto</option>
                        <option value="fechado" <?php echo ($status == 'fechado' ? 'selected' : ''); ?>>Fechado</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Movimentos -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <?php if ($_SESSION['nivel'] === 'administrador'): ?>
                                <th>Caixa</th>
                                <th>Operador</th>
                            <?php endif; ?>
                            <th>Abertura</th>
                            <th>Fechamento</th>
                            <th>Valor Inicial</th>
                            <th>Vendas</th>
                            <th>Sangrias</th>
                            <th>Valor Final</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <?php if ($_SESSION['nivel'] === 'administrador'): ?>
                                    <td>Caixa <?php echo $row['caixa_numero']; ?></td>
                                    <td><?php echo $row['nome_usuario']; ?></td>
                                <?php endif; ?>
                                <td><?php echo $row['data_abertura_formatada']; ?></td>
                                <td><?php echo $row['data_fechamento_formatada'] ?? '-'; ?></td>
                                <td>R$ <?php echo number_format($row['valor_inicial'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($row['valor_vendas'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($row['valor_sangrias'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($row['valor_final'] ?? 0, 2, ',', '.'); ?></td>
                                <td>
                                    <?php if ($row['status'] === 'aberto'): ?>
                                        <span class="badge bg-success">Aberto</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Fechado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" 
                                            onclick="verDetalhes(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="detalhesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Movimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalhesConteudo">
                    <!-- Conteúdo será carregado via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function verDetalhes(id) {
    const modal = new bootstrap.Modal(document.getElementById('detalhesModal'));
    const conteudo = document.getElementById('detalhesConteudo');
    
    // Mostrar loading
    conteudo.innerHTML = `
        <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mb-0 text-muted">Carregando detalhes...</p>
            </div>
        </div>
    `;
    modal.show();
    
    // Carregar detalhes via AJAX
    fetch('get_detalhes_caixa.php?id=' + id)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao carregar detalhes: ' + response.status);
            }
            return response.text();
        })
        .then(html => {
            conteudo.innerHTML = html;
            
            // Inicializar tooltips se houver
            const tooltips = conteudo.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));
            
            // Ajustar altura das tabelas para scroll
            const tables = conteudo.querySelectorAll('.table-responsive');
            tables.forEach(table => {
                const headerHeight = table.querySelector('thead').offsetHeight;
                const maxHeight = 300 - headerHeight;
                table.querySelector('tbody').style.maxHeight = maxHeight + 'px';
                table.querySelector('tbody').style.overflowY = 'auto';
            });
        })
        .catch(error => {
            conteudo.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${error.message}
                </div>
            `;
        });
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));
});
</script>

<?php include 'footer.php'; ?> 