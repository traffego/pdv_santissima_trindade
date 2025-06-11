<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';
require_once 'check_login.php';

// Verificar se as variáveis de sessão necessárias existem
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['caixa_numero'])) {
    header("Location: login.php");
    exit;
}

try {
    // Verificar se já existe um caixa aberto para o usuário
    $usuario_id = $_SESSION['usuario_id'];
    $caixa_numero = $_SESSION['caixa_numero'];

    $sql = "SELECT id FROM controle_caixa 
            WHERE usuario_id = ? 
            AND caixa_numero = ? 
            AND status = 'aberto'";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Erro na preparação da consulta: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ii", $usuario_id, $caixa_numero);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro na execução da consulta: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);

    // Se já existe um caixa aberto, redirecionar para controle_caixa.php
    if (mysqli_num_rows($result) > 0) {
        header("Location: controle_caixa.php");
        exit;
    }

    include 'header.php';
    ?>

    <div class="container-fluid px-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-cash-register me-2"></i>
                            Abertura de Caixa
                        </h5>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form action="processar_abertura_caixa.php" method="post" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Valor Inicial</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0" 
                                           class="form-control" 
                                           name="valor_inicial" 
                                           required>
                                    <div class="invalid-feedback">
                                        Informe o valor inicial do caixa.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Observações</label>
                                <textarea class="form-control" 
                                          name="observacoes" 
                                          rows="3" 
                                          placeholder="Observações sobre a abertura do caixa (opcional)"></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-lock-open me-2"></i>
                                    Abrir Caixa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Validação do formulário
    (function () {
        'use strict'

        var forms = document.querySelectorAll('.needs-validation')

        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
    })()
    </script>

    <?php 
    include 'footer.php';

} catch (Exception $e) {
    // Log do erro
    error_log("Erro em abrir_caixa.php: " . $e->getMessage());
    
    // Exibir mensagem amigável
    include 'header.php';
    ?>
    <div class="container-fluid px-4">
        <div class="alert alert-danger">
            <h4 class="alert-heading">Erro!</h4>
            <p>Ocorreu um erro ao carregar a página. Por favor, tente novamente mais tarde.</p>
            <hr>
            <p class="mb-0">Se o problema persistir, contate o suporte técnico.</p>
        </div>
    </div>
    <?php
    include 'footer.php';
}
?> 