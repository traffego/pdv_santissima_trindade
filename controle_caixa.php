<?php
require_once 'db.php';
require_once 'check_login.php';

// Verificar se existe um caixa aberto para o usuário
$usuario_id = $_SESSION['usuario_id'];
$caixa_numero = $_SESSION['caixa_numero'];

$sql = "SELECT * FROM controle_caixa 
        WHERE usuario_id = ? 
        AND caixa_numero = ? 
        AND status = 'aberto' 
        ORDER BY data_abertura DESC 
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $caixa_numero);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$caixa_aberto = mysqli_fetch_assoc($result);

include 'header.php';
?>

<div class="container-fluid px-4">
    <?php if (!$caixa_aberto): ?>
    <!-- Formulário de Abertura de Caixa -->
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-cash-register text-primary fa-2x mb-3"></i>
                    <h5 class="card-title">Abertura de Caixa</h5>
                    <form action="processar_abertura_caixa.php" method="post" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-money-bill-alt"></i></span>
                                <input type="number" step="0.01" min="0" class="form-control" id="valor_inicial" 
                                       name="valor_inicial" placeholder="Valor Inicial (R$)" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" id="observacoes" name="observacoes" 
                                      rows="2" placeholder="Observações"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-lock-open me-2"></i>Abrir Caixa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Dashboard Condensado -->
    <div class="row g-3">
        <!-- Cabeçalho do Caixa -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center">
                        <div class="status-indicator active me-2"></div>
                        <div>
                            <h6 class="mb-0">Caixa <?php echo $caixa_numero; ?></h6>
                            <small class="text-muted">Aberto em: <?php echo date('d/m/Y H:i', strtotime($caixa_aberto['data_abertura'])); ?></small>
                        </div>
                    </div>
                    <div class="btn-group">
                        <a href="registrar_sangria.php" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-money-bill-wave me-1"></i>Sangria
                        </a>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#fecharCaixaModal">
                            <i class="fas fa-lock me-1"></i>Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de Valores -->
        <div class="col-12">
            <div class="row g-3">
                <!-- Resumo Financeiro -->
                <div class="col-md-8">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Resumo Financeiro</h6>
                            <div class="row g-3">
                                <div class="col-sm-6 col-lg-3">
                                    <div class="d-flex align-items-center h-100">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-money-bill text-success fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="small text-muted">Inicial</div>
                                            <div class="fw-bold">R$ <?php echo number_format($caixa_aberto['valor_inicial'], 2, ',', '.'); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="d-flex align-items-center h-100">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-shopping-cart text-primary fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="small text-muted">Vendas</div>
                                            <div class="fw-bold">R$ <?php echo number_format($caixa_aberto['valor_vendas'], 2, ',', '.'); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="d-flex align-items-center h-100">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-hand-holding-usd text-danger fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="small text-muted">Sangrias</div>
                                            <div class="fw-bold">R$ <?php echo number_format($caixa_aberto['valor_sangrias'], 2, ',', '.'); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="d-flex align-items-center h-100">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-calculator text-info fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="small text-muted">Total</div>
                                            <div class="fw-bold">R$ <?php echo number_format(($caixa_aberto['valor_inicial'] + $caixa_aberto['valor_vendas'] - $caixa_aberto['valor_sangrias']), 2, ',', '.'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formas de Pagamento -->
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Formas de Pagamento</h6>
                            <div class="payment-bars h-100 d-flex flex-column justify-content-between">
                                <div class="payment-bar">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Dinheiro</small>
                                        <small class="fw-bold">R$ <?php echo number_format($caixa_aberto['valor_vendas_dinheiro'], 2, ',', '.'); ?></small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo ($caixa_aberto['valor_vendas'] > 0 ? ($caixa_aberto['valor_vendas_dinheiro'] / $caixa_aberto['valor_vendas'] * 100) : 0); ?>%"></div>
                                    </div>
                                </div>
                                <div class="payment-bar">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>PIX</small>
                                        <small class="fw-bold">R$ <?php echo number_format($caixa_aberto['valor_vendas_pix'], 2, ',', '.'); ?></small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-info" style="width: <?php echo ($caixa_aberto['valor_vendas'] > 0 ? ($caixa_aberto['valor_vendas_pix'] / $caixa_aberto['valor_vendas'] * 100) : 0); ?>%"></div>
                                    </div>
                                </div>
                                <div class="payment-bar">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Cartão</small>
                                        <small class="fw-bold">R$ <?php echo number_format($caixa_aberto['valor_vendas_cartao'], 2, ',', '.'); ?></small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" style="width: <?php echo ($caixa_aberto['valor_vendas'] > 0 ? ($caixa_aberto['valor_vendas_cartao'] / $caixa_aberto['valor_vendas'] * 100) : 0); ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal de Fechamento de Caixa -->
<div class="modal fade" id="fecharCaixaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Fechamento de Caixa</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="processar_fechamento_caixa.php" method="post">
                <div class="modal-body">
                    <div class="alert alert-warning py-2 mb-3">
                        <small><i class="fas fa-exclamation-triangle me-1"></i>Confirme os valores antes de fechar o caixa</small>
                    </div>
                    
                    <input type="hidden" name="caixa_id" value="<?php echo $caixa_aberto['id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small">Valor em Dinheiro</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">R$</span>
                            <input type="number" step="0.01" class="form-control" name="valor_dinheiro" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label small">Total em PIX</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control" value="<?php echo number_format($caixa_aberto['valor_vendas_pix'], 2, ',', '.'); ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label small">Total em Cartão</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control" value="<?php echo number_format($caixa_aberto['valor_vendas_cartao'], 2, ',', '.'); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small">Observações</label>
                        <textarea class="form-control form-control-sm" name="observacoes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-lock me-1"></i>Fechar Caixa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #dc3545;
}

.status-indicator.active {
    background-color: #198754;
    box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.2);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.4);
    }
    70% {
        box-shadow: 0 0 0 4px rgba(25, 135, 84, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
    }
}

.payment-bars .progress {
    background-color: rgba(0,0,0,0.05);
}

.payment-bar {
    margin-bottom: 1.5rem;
}

.payment-bar:last-child {
    margin-bottom: 0;
}

.card {
    height: 100%;
}

.card-body {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.payment-bars {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($caixa_aberto): ?>
    // Configurar o gráfico de formas de pagamento
    const ctx = document.getElementById('paymentChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Dinheiro', 'PIX', 'Cartão'],
            datasets: [{
                data: [
                    <?php echo $caixa_aberto['valor_vendas_dinheiro']; ?>,
                    <?php echo $caixa_aberto['valor_vendas_pix']; ?>,
                    <?php echo $caixa_aberto['valor_vendas_cartao']; ?>
                ],
                backgroundColor: ['#198754', '#0dcaf0', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    <?php endif; ?>
    
    // Validação do formulário de abertura
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});
</script>

<?php include 'footer.php'; ?> 