<?php
require_once '../db.php';
require_once '../check_admin.php'; // Apenas administradores podem gerenciar permissões

// Processar o formulário de atualização de permissões
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'])) {
    $usuario_id = intval($_POST['usuario_id']);
    
    // Primeiro, remove todas as permissões existentes do usuário
    mysqli_query($conn, "DELETE FROM permissoes_categorias WHERE usuario_id = $usuario_id");
    
    // Depois, insere as novas permissões selecionadas
    if (isset($_POST['categorias']) && is_array($_POST['categorias'])) {
        $stmt = mysqli_prepare($conn, "INSERT INTO permissoes_categorias (usuario_id, categoria) VALUES (?, ?)");
        
        foreach ($_POST['categorias'] as $categoria) {
            mysqli_stmt_bind_param($stmt, "is", $usuario_id, $categoria);
            mysqli_stmt_execute($stmt);
        }
        
        mysqli_stmt_close($stmt);
        $_SESSION['success_msg'] = "Permissões atualizadas com sucesso!";
    }
    
    header("Location: gerenciar_permissoes.php");
    exit();
}

// Buscar todos os operadores de caixa
$sql_usuarios = "SELECT id, nome, usuario, caixa_numero FROM usuarios WHERE nivel = 'operador' AND ativo = 1 ORDER BY caixa_numero";
$result_usuarios = mysqli_query($conn, $sql_usuarios);

// Buscar todas as categorias
$sql_categorias = "SELECT nome FROM categorias ORDER BY nome";
$result_categorias = mysqli_query($conn, $sql_categorias);
$categorias = [];
while ($row = mysqli_fetch_assoc($result_categorias)) {
    $categorias[] = $row['nome'];
}

// Título da página
$pageTitle = "Gerenciar Permissões de Categorias";
include_once '../header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>Gerenciar Permissões de Categorias por Caixa
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success_msg'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['success_msg'];
                            unset($_SESSION['success_msg']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="accordion" id="accordionPermissoes">
                        <?php while ($usuario = mysqli_fetch_assoc($result_usuarios)): ?>
                            <?php
                            // Buscar permissões atuais do usuário
                            $usuario_id = $usuario['id'];
                            $sql_permissoes = "SELECT categoria FROM permissoes_categorias WHERE usuario_id = $usuario_id";
                            $result_permissoes = mysqli_query($conn, $sql_permissoes);
                            $permissoes_usuario = [];
                            while ($row = mysqli_fetch_assoc($result_permissoes)) {
                                $permissoes_usuario[] = $row['categoria'];
                            }
                            ?>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo $usuario['id']; ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?php echo $usuario['id']; ?>">
                                        Caixa <?php echo $usuario['caixa_numero']; ?> - <?php echo $usuario['nome']; ?>
                                        <span class="badge bg-secondary ms-2">
                                            <?php echo count($permissoes_usuario); ?> categorias permitidas
                                        </span>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $usuario['id']; ?>" class="accordion-collapse collapse" 
                                     data-bs-parent="#accordionPermissoes">
                                    <div class="accordion-body">
                                        <form method="POST" action="">
                                            <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                            
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label class="form-label">Categorias Permitidas:</label>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                onclick="marcarTodas(<?php echo $usuario['id']; ?>)">
                                                            Marcar Todas
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                onclick="desmarcarTodas(<?php echo $usuario['id']; ?>)">
                                                            Desmarcar Todas
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <?php foreach ($categorias as $categoria): ?>
                                                        <div class="col-md-3 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input categoria-check-<?php echo $usuario['id']; ?>" 
                                                                       type="checkbox" 
                                                                       name="categorias[]" 
                                                                       value="<?php echo $categoria; ?>"
                                                                       id="cat_<?php echo $usuario['id']; ?>_<?php echo md5($categoria); ?>"
                                                                       <?php echo in_array($categoria, $permissoes_usuario) ? 'checked' : ''; ?>>
                                                                <label class="form-check-label" 
                                                                       for="cat_<?php echo $usuario['id']; ?>_<?php echo md5($categoria); ?>">
                                                                    <?php echo $categoria; ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-1"></i>Salvar Permissões
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function marcarTodas(usuarioId) {
    document.querySelectorAll('.categoria-check-' + usuarioId).forEach(checkbox => {
        checkbox.checked = true;
    });
}

function desmarcarTodas(usuarioId) {
    document.querySelectorAll('.categoria-check-' + usuarioId).forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>

<?php include_once '../footer.php'; ?> 