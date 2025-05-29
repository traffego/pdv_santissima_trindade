<?php
session_start();
require_once 'db.php';
require_once 'check_admin.php'; // Garante que apenas administradores acessem

// Verifica se é uma requisição POST e se o token CSRF é válido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token de segurança inválido');
    }

    try {
        // Inicia uma transação para garantir a integridade dos dados
        mysqli_begin_transaction($conn);

        // Verifica qual ação foi solicitada
        if (isset($_POST['limpar_vendas'])) {
            // Primeiro, exclui os itens de venda (devido à chave estrangeira)
            $sql_delete_itens = "DELETE FROM itens_venda";
            if (!mysqli_query($conn, $sql_delete_itens)) {
                throw new Exception("Erro ao limpar itens de venda: " . mysqli_error($conn));
            }

            // Em seguida, exclui as vendas
            $sql_delete_vendas = "DELETE FROM vendas";
            if (!mysqli_query($conn, $sql_delete_vendas)) {
                throw new Exception("Erro ao limpar vendas: " . mysqli_error($conn));
            }

            $_SESSION['success_msg'] = "Todas as vendas foram removidas com sucesso!";
        }
        elseif (isset($_POST['limpar_produtos'])) {
            // Verifica se existem vendas antes de tentar apagar produtos
            $check_vendas = mysqli_query($conn, "SELECT COUNT(*) as total FROM vendas");
            $vendas_count = mysqli_fetch_assoc($check_vendas)['total'];

            if ($vendas_count > 0) {
                throw new Exception("Não é possível apagar os produtos enquanto existirem vendas no sistema. Limpe as vendas primeiro.");
            }

            // Exclui os produtos
            $sql_delete_produtos = "DELETE FROM produtos";
            if (!mysqli_query($conn, $sql_delete_produtos)) {
                throw new Exception("Erro ao limpar produtos: " . mysqli_error($conn));
            }

            $_SESSION['success_msg'] = "Todos os produtos foram removidos com sucesso!";
        }
        elseif (isset($_POST['limpar_tudo'])) {
            // Primeiro, exclui os itens de venda (devido à chave estrangeira)
            $sql_delete_itens = "DELETE FROM itens_venda";
            if (!mysqli_query($conn, $sql_delete_itens)) {
                throw new Exception("Erro ao limpar itens de venda: " . mysqli_error($conn));
            }

            // Em seguida, exclui as vendas
            $sql_delete_vendas = "DELETE FROM vendas";
            if (!mysqli_query($conn, $sql_delete_vendas)) {
                throw new Exception("Erro ao limpar vendas: " . mysqli_error($conn));
            }

            // Por fim, exclui os produtos
            $sql_delete_produtos = "DELETE FROM produtos";
            if (!mysqli_query($conn, $sql_delete_produtos)) {
                throw new Exception("Erro ao limpar produtos: " . mysqli_error($conn));
            }

            $_SESSION['success_msg'] = "Sistema limpo com sucesso!";
        }

        // Se tudo ocorreu bem, confirma as alterações
        mysqli_commit($conn);
    } catch (Exception $e) {
        // Em caso de erro, desfaz todas as alterações
        mysqli_rollback($conn);
        $_SESSION['error_msg'] = "Erro: " . $e->getMessage();
    }

    header("Location: index.php");
    exit();
}

// Gera um novo token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpar Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">⚠️ ATENÇÃO - Limpar Sistema</h4>
                    </div>
                    <div class="card-body">
                        <!-- Limpar Vendas -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Limpar Vendas</h5>
                                <p class="text-muted">Remove todas as vendas e seus itens do sistema.</p>
                                <form method="POST" onsubmit="return confirmarLimpeza('vendas')">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" name="limpar_vendas" class="btn btn-warning">
                                        <i class="fas fa-receipt"></i> Limpar Apenas Vendas
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Limpar Produtos -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Limpar Produtos</h5>
                                <p class="text-muted">Remove todos os produtos do sistema. Só é possível se não houver vendas.</p>
                                <form method="POST" onsubmit="return confirmarLimpeza('produtos')">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" name="limpar_produtos" class="btn btn-warning">
                                        <i class="fas fa-box"></i> Limpar Apenas Produtos
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Limpar Tudo -->
                        <div class="card mb-4 border-danger">
                            <div class="card-body">
                                <h5 class="card-title text-danger">Limpar Todo o Sistema</h5>
                                <p class="text-muted">Remove todas as vendas e todos os produtos do sistema.</p>
                                <form method="POST" onsubmit="return confirmarLimpeza('tudo')">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" name="limpar_tudo" class="btn btn-danger">
                                        <i class="fas fa-trash-alt"></i> Limpar Todo o Sistema
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="text-center">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmarLimpeza(tipo) {
        let mensagem = '';
        
        switch(tipo) {
            case 'vendas':
                mensagem = 'Você está prestes a apagar TODAS as vendas do sistema.\nEsta ação é IRREVERSÍVEL!';
                break;
            case 'produtos':
                mensagem = 'Você está prestes a apagar TODOS os produtos do sistema.\nEsta ação é IRREVERSÍVEL!';
                break;
            case 'tudo':
                mensagem = 'Você está prestes a apagar TODAS as vendas e TODOS os produtos do sistema.\nEsta ação é IRREVERSÍVEL!';
                break;
        }
        
        return confirm('ATENÇÃO!\n\n' + mensagem + '\n\nTem certeza que deseja continuar?');
    }
    </script>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 