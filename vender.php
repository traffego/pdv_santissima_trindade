<?php
require_once 'db.php';
require_once 'check_login.php';

// Verificar e definir número de caixa para administradores se não estiver definido
if ($_SESSION['nivel'] === 'administrador' && (!isset($_SESSION['caixa_numero']) || empty($_SESSION['caixa_numero']))) {
    $_SESSION['caixa_numero'] = 999; // Número padrão para admin
}

// Get products - Filtrar por categorias permitidas para o usuário
if ($_SESSION['nivel'] === 'administrador') {
    // Administradores veem todos os produtos
    $sql = "SELECT p.*, c.nome as categoria_nome 
            FROM produtos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            ORDER BY p.nome";
    $result = mysqli_query($conn, $sql);
} else {
    // Operadores veem apenas produtos das categorias permitidas
    $sql = "SELECT p.*, c.nome as categoria_nome 
            FROM produtos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            INNER JOIN permissoes_categorias pc ON c.nome = pc.categoria 
            WHERE pc.usuario_id = ? 
            ORDER BY p.nome";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

include 'header.php';

// Display messages if any
if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
    echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show shadow server-alert" role="alert">
            <div class="d-flex align-items-center">
                <div class="alert-icon me-3">
                    <i class="fas ' . ($_SESSION['message_type'] == 'danger' ? 'fa-exclamation-triangle' : 'fa-info-circle') . ' fa-2x"></i>
                </div>
                <div class="alert-content">
                    ' . $_SESSION['message'] . '
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    
    // Clear session messages
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<!-- Header Section -->
<div class="header-section mb-4">
    <div class="row g-0">
        <div class="col-md-8">
            <div class="main-header p-4 rounded-start" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                <div class="d-flex align-items-center">
                    <div class="header-icon me-4">
                        <i class="fas fa-cash-register fa-2x text-white"></i>
                    </div>
                    <div class="text-white">
                        <h3 class="mb-1 fw-bold">PONTO DE VENDA</h3>
                        <p class="mb-0 opacity-75">
                            <?php if($_SESSION['nivel'] === 'administrador'): ?>
                                <i class="fas fa-user-shield me-1"></i> Admin - Caixa <?php echo $_SESSION['caixa_numero']; ?>
                            <?php else: ?>
                                <i class="fas fa-cash-register me-1"></i> Operador - Caixa <?php echo $_SESSION['caixa_numero']; ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-header h-100 p-4 rounded-end" style="background: #2a5298;">
                <div class="d-flex flex-column justify-content-center h-100">
                    <div class="stat-item mb-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="fas fa-clock fa-lg text-white"></i>
                            </div>
                            <div>
                                <small class="text-white opacity-75 d-block">HORÁRIO ATUAL</small>
                                <span id="current-time" class="text-white fw-bold"></span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon me-3">
                                <i class="fas fa-calendar fa-lg text-white"></i>
                            </div>
                            <div>
                                <small class="text-white opacity-75 d-block">DATA</small>
                                <span id="current-date" class="text-white fw-bold"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Products Column -->
    <div class="col-lg-8 order-2 order-lg-1">
        <div class="card mb-4 products-container shadow">
            <div class="card-header bg-white border-bottom-0 py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <span class="header-icon-sm me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-box-open text-primary"></i>
                        </span>
                        <span class="fw-bold">PRODUTOS DISPONÍVEIS</span>
                    </h5>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-primary d-lg-none me-2" id="toggle-cart-btn">
                            <i class="fas fa-shopping-cart"></i> <span class="cart-counter badge bg-danger">0</span>
                        </button>
                    </div>
                </div>
                <div class="search-container">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" id="search-product" placeholder="Buscar produto...">
                        <button class="btn btn-outline-secondary d-none d-md-block" type="button" id="clear-search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Categories quick filter -->
                <div class="categories-filter d-flex flex-nowrap overflow-auto mb-4 pb-2">
                    <button class="btn btn-sm btn-primary me-2 category-btn active" data-category="todos">
                        <i class="fas fa-th-large me-1"></i>Todos
                    </button>
                    <?php
                    // Get unique categories - filtrar por permissões para operadores
                    if ($_SESSION['nivel'] === 'administrador') {
                        // Administradores veem todas as categorias
                        $sql_categorias = "SELECT DISTINCT c.nome as categoria 
                                         FROM produtos p 
                                         INNER JOIN categorias c ON p.categoria_id = c.id 
                                         WHERE p.quantidade_estoque > 0 
                                         ORDER BY c.nome";
                        $result_categorias = mysqli_query($conn, $sql_categorias);
                    } else {
                        // Operadores veem apenas categorias permitidas
                        $sql_categorias = "SELECT DISTINCT c.nome as categoria 
                                         FROM produtos p 
                                         INNER JOIN categorias c ON p.categoria_id = c.id 
                                         INNER JOIN permissoes_categorias pc ON c.nome = pc.categoria 
                                         WHERE p.quantidade_estoque > 0 
                                         AND pc.usuario_id = ? 
                                         ORDER BY c.nome";
                        $stmt_categorias = mysqli_prepare($conn, $sql_categorias);
                        mysqli_stmt_bind_param($stmt_categorias, "i", $_SESSION['usuario_id']);
                        mysqli_stmt_execute($stmt_categorias);
                        $result_categorias = mysqli_stmt_get_result($stmt_categorias);
                    }
                    
                    while($cat = mysqli_fetch_assoc($result_categorias)) {
                        echo '<button class="btn btn-sm btn-outline-primary me-2 category-btn" 
                                 data-category="' . strtolower($cat['categoria']) . '">' . 
                                 $cat['categoria'] . '</button>';
                    }
                    ?>
                </div>
                
                <div class="row g-3" id="products-container">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <div class="col-12 col-md-4 col-xl-3 product-item" 
                                 data-name="<?php echo strtolower($row['nome']); ?>"
                                 data-category="<?php echo strtolower($row['categoria_nome'] ?? ''); ?>">
                                <div class="card h-100 product-card shadow-sm <?php echo ($row['quantidade_estoque'] <= 0) ? 'out-of-stock' : ''; ?>" 
                                     style="border-left: 10px solid <?php echo $row['cor'] ?? '#eeeeee'; ?>; border-top: 1px solid #dee2e6; border-right: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="product-info flex-grow-1 me-3">
                                                <h6 class="product-name mb-1"><?php echo $row['nome']; ?></h6>
                                                <div class="price-tag fw-bold text-primary">R$ <?php echo number_format($row['preco'], 2, ',', '.'); ?></div>
                                                <?php if ($row['quantidade_estoque'] > 0): ?>
                                                    <div class="stock-info mt-1">
                                                        <small class="<?php echo $row['quantidade_estoque'] <= 5 ? 'text-danger' : 
                                                            ($row['quantidade_estoque'] <= 10 ? 'text-warning' : 'text-success'); ?>">
                                                            <i class="fas fa-box"></i> <?php echo $row['quantidade_estoque']; ?> em estoque
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($row['quantidade_estoque'] <= 0): ?>
                                                <span class="stock-badge bg-danger">
                                                    <i class="fas fa-ban"></i>
                                                </span>
                                            <?php else: ?>
                                                <button type="button" 
                                                    class="btn add-product-btn mobile-square-btn <?php echo ($row['quantidade_estoque'] <= 0) ? 'disabled' : 'add-product'; ?>"
                                                    <?php if ($row['quantidade_estoque'] <= 0): ?>
                                                    disabled title="Produto sem estoque"
                                                    <?php else: ?>
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-nome="<?php echo htmlspecialchars($row['nome']); ?>"
                                                    data-preco="<?php echo $row['preco']; ?>"
                                                    data-estoque="<?php echo $row['quantidade_estoque']; ?>"
                                                    <?php endif; ?>>
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <div class="empty-state">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhum produto em estoque</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cart Column -->
    <div class="col-lg-4 order-1 order-lg-2 mb-3" id="cart-container">
        <div class="card cart-card">
            <!-- Mobile drag handle -->
            <div class="mobile-cart-handle"></div>
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>Carrinho
                </h5>
                <div class="text-muted small">
                    <?php if($_SESSION['nivel'] === 'administrador'): ?>
                    Admin - Caixa <?php echo $_SESSION['caixa_numero']; ?>
                    <?php else: ?>
                    Caixa <?php echo $_SESSION['caixa_numero']; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <form id="cart-form" method="post" action="processar_venda.php">
                    <div id="cart-items" class="cart-items-container mb-3">
                        <p id="empty-cart" class="text-center">O carrinho está vazio</p>
                    </div>
                    
                    <!-- Toggle button for cart items -->
                    <div id="cart-toggle" class="text-center mb-3" style="display: none;">
                        <button type="button" class="btn btn-sm btn-outline-primary toggle-cart-items">
                            <span class="toggle-text">Ver todos</span>
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </button>
                    </div>
                    
                    <div class="total-section">
                        <div class="d-flex justify-content-between align-items-center bg-dark text-white p-2 rounded-pill">
                            <span class="ms-3">Total:</span>
                            <span id="cart-total" class="fs-5 fw-bold me-3">R$ 0,00</span>
                            <input type="hidden" name="valor_total" id="valor_total_input" value="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label d-flex justify-content-between">
                            <span>Forma de Pagamento</span> 
                            <span class="text-danger payment-required fw-bold" style="display: none;">
                                <i class="fas fa-exclamation-circle"></i> SELECIONE UMA OPÇÃO
                            </span>
                        </label>
                        <div class="d-flex gap-2 payment-buttons">
                            <button type="button" class="btn btn-success flex-grow-1 payment-method-btn" data-method="Dinheiro">
                                <i class="fas fa-money-bill-alt"></i><span class="d-none d-sm-inline ms-1">Dinheiro</span>
                            </button>
                            <button type="button" class="btn btn-info flex-grow-1 payment-method-btn" data-method="Pix">
                                <i class="fas fa-qrcode"></i><span class="d-none d-sm-inline ms-1">Pix</span>
                            </button>
                            <button type="button" class="btn btn-warning flex-grow-1 payment-method-btn" data-method="Cartão">
                                <i class="fas fa-credit-card"></i><span class="d-none d-sm-inline ms-1">Cartão</span>
                            </button>
                        </div>
                        <input type="hidden" name="forma_pagamento" id="forma_pagamento" required>
                    </div>
                    
                    <!-- Hidden input for cashier from session -->
                    <input type="hidden" name="caixa" value="<?php echo $_SESSION['caixa_numero']; ?>">
                    <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuario_id']; ?>">
                    
                    <button type="submit" class="btn btn-success w-100 btn-lg d-flex align-items-center justify-content-center gap-2" id="finalizar-venda" disabled>
                        <i class="fas fa-check-circle"></i> Finalizar Venda
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast para mensagens de erro -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1070;">
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span id="errorMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Add mobile spacer -->
<div class="mobile-cart-spacer"></div>

<!-- Add cart backdrop for mobile -->
<div class="cart-backdrop"></div>

<!-- Modal de Venda Concluída -->
<div class="modal fade" id="vendaConcluidaModal" tabindex="-1" aria-labelledby="vendaConcluidaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="vendaConcluidaModalLabel">
            <i class="fas fa-check-circle me-2"></i> Venda Concluída com Sucesso!
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <div class="display-1 text-success mb-3">
            <i class="fas fa-check-circle"></i>
          </div>
          <h4>Venda #<span id="vendaIdDisplay"></span> finalizada!</h4>
          <p class="lead">O cliente foi atendido com sucesso.</p>
        </div>
        
        <div class="d-flex justify-content-center mb-3">
          <a href="#" id="imprimirComprovanteBtn" class="btn btn-outline-secondary mx-2">
            <i class="fas fa-print me-2"></i> Imprimir Comprovante
          </a>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Continuar Vendendo</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Caixa Fechado -->
<div class="modal fade" id="caixaFechadoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="caixaFechadoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="caixaFechadoModalLabel">
            <i class="fas fa-exclamation-triangle me-2"></i> Caixa Fechado
        </h5>
      </div>
      <div class="modal-body text-center">
        <div class="display-1 text-warning mb-3">
          <i class="fas fa-cash-register"></i>
        </div>
        <h4>Não é possível realizar vendas com o caixa fechado!</h4>
        <p class="lead mb-4">Para iniciar as vendas, é necessário abrir o caixa primeiro.</p>
        <a href="controle_caixa.php" class="btn btn-lg btn-warning">
          <i class="fas fa-door-open me-2"></i>ABRIR CAIXA AGORA
        </a>
      </div>
    </div>
  </div>
</div>

<script>
// Atualizar data e hora em tempo real
function updateDateTime() {
    const now = new Date();
    const timeElement = document.getElementById('current-time');
    const dateElement = document.getElementById('current-date');
    
    // Formatar hora
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
    timeElement.textContent = now.toLocaleTimeString('pt-BR', timeOptions);
    
    // Formatar data
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    dateElement.textContent = now.toLocaleDateString('pt-BR', dateOptions);
}

// Atualizar a cada segundo
updateDateTime();
setInterval(updateDateTime, 1000);

document.addEventListener('DOMContentLoaded', function() {
    const cartItems = {};
    const cartTotal = document.getElementById('cart-total');
    const valorTotalInput = document.getElementById('valor_total_input');
    const emptyCartMessage = document.getElementById('empty-cart');
    const finalizarVendaBtn = document.getElementById('finalizar-venda');
    const paymentMethodInput = document.getElementById('forma_pagamento');
    const paymentMethodButtons = document.querySelectorAll('.payment-method-btn');
    const cartContainer = document.getElementById('cart-container');
    const toggleCartBtn = document.getElementById('toggle-cart-btn');
    const paymentRequired = document.querySelector('.payment-required');
    const cartCounter = document.querySelector('.cart-counter');
    const cartToggle = document.getElementById('cart-toggle');
    let isCartExpanded = false;
    
    // Inicializar o Toast de erro
    const errorToastEl = document.getElementById('errorToast');
    let errorToast;
    if (errorToastEl) {
        errorToast = new bootstrap.Toast(errorToastEl, {
            delay: 5000 // Exibir por 5 segundos
        });
    }
    
    // Inicializar a modal de venda concluída
    const vendaConcluidaModalEl = document.getElementById('vendaConcluidaModal');
    let vendaConcluidaModal;
    if (vendaConcluidaModalEl) {
        vendaConcluidaModal = new bootstrap.Modal(vendaConcluidaModalEl);
        
        // Limpar o carrinho quando a modal for fechada
        vendaConcluidaModalEl.addEventListener('hidden.bs.modal', function () {
            // Limpar o carrinho
            for (const id in cartItems) {
                delete cartItems[id];
            }
            
            // Limpar o DOM
            const cartItemsContainer = document.getElementById('cart-items');
            while (cartItemsContainer.firstChild) {
                if (cartItemsContainer.lastChild.id !== 'empty-cart') {
                    cartItemsContainer.removeChild(cartItemsContainer.lastChild);
                } else {
                    break;
                }
            }
            
            // Mostrar mensagem de carrinho vazio
            emptyCartMessage.style.display = 'block';
            
            // Resetar total
            updateCartTotal();
            updateCartCounter();
            
            // Desabilitar botão de finalizar
            finalizarVendaBtn.disabled = true;
            
            // Remover seleção de forma de pagamento
            paymentMethodButtons.forEach(btn => btn.classList.remove('active'));
            paymentMethodInput.value = '';
        });
        
        // Verificar se tem uma venda concluída para mostrar a modal
        <?php if (isset($_SESSION['venda_concluida']) && $_SESSION['venda_concluida']): ?>
            // Mostrar ID da venda na modal
            document.getElementById('vendaIdDisplay').textContent = '<?php echo $_SESSION['venda_id']; ?>';
            
            // Configurar o botão de impressão
            document.getElementById('imprimirComprovanteBtn').href = 'imprimir_venda.php?id=<?php echo $_SESSION['venda_id']; ?>';
            
            // Mostrar a modal automaticamente
            setTimeout(() => vendaConcluidaModal.show(), 500);
            
            <?php 
                // Limpar as flags da sessão
                unset($_SESSION['venda_concluida']);
                unset($_SESSION['venda_id']);
            ?>
        <?php endif; ?>
    }
    
    // Adjust page height for mobile cart
    function adjustPageForMobileCart() {
        if (window.innerWidth < 992) {
            // Set mobile-cart-spacer to be visible
            const mobileCartSpacer = document.querySelector('.mobile-cart-spacer');
            if (mobileCartSpacer) {
                mobileCartSpacer.style.display = 'block';
                
                // Make sure it has adequate height based on viewport
                const viewportHeight = window.innerHeight;
                const cartHeight = Math.min(viewportHeight * 0.7, 500); // 70% of viewport or max 500px
                mobileCartSpacer.style.height = `${cartHeight}px`;
            }
            
            // Add some padding to the bottom of the page
            document.body.style.paddingBottom = '100px';
        } else {
            // Reset for desktop view
            const mobileCartSpacer = document.querySelector('.mobile-cart-spacer');
            if (mobileCartSpacer) {
                mobileCartSpacer.style.display = 'none';
            }
            document.body.style.paddingBottom = '';
        }
    }
    
    // Run initially and on window resize
    adjustPageForMobileCart();
    window.addEventListener('resize', adjustPageForMobileCart);
    
    // Toggle cart visibility with extra space adjustment
    if (toggleCartBtn) {
        toggleCartBtn.addEventListener('click', function() {
            cartContainer.classList.toggle('mobile-cart-visible');
        });
    }
    
    // Category filter
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Update active button
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter products
            const productItems = document.querySelectorAll('.product-item');
            productItems.forEach(item => {
                if (category === 'todos' || item.dataset.category === category) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    
    // Clear search
    const clearSearchBtn = document.getElementById('clear-search');
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            document.getElementById('search-product').value = '';
            const productItems = document.querySelectorAll('.product-item');
            productItems.forEach(item => {
                item.style.display = '';
            });
        });
    }
    
    // Payment method buttons
    paymentMethodButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            paymentMethodButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Set the payment method value
            paymentMethodInput.value = this.dataset.method;
            
            // Hide the required indicator
            if (paymentRequired) {
                paymentRequired.style.display = 'none';
            }
            
            // Enable finish button if cart is not empty
            updateFinishButton();
        });
    });
    
    // Update finish button state
    function updateFinishButton() {
        const cartHasItems = Object.keys(cartItems).length > 0;
        const paymentSelected = paymentMethodInput.value !== '';
        
        if (cartHasItems && !paymentSelected && paymentRequired) {
            paymentRequired.style.display = 'inline';
        }
        
        finalizarVendaBtn.disabled = !(cartHasItems && paymentSelected);
    }
    
    // Search products
    document.getElementById('search-product').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const productItems = document.querySelectorAll('.product-item');
        
        // Reset category filters
        categoryButtons.forEach(btn => {
            if (btn.dataset.category === 'todos') {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Filter products
        productItems.forEach(item => {
            const productName = item.dataset.name;
            if (productName.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Add product to cart
    document.querySelectorAll('.add-product').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const nome = this.dataset.nome;
            const preco = parseFloat(this.dataset.preco);
            const estoque = parseInt(this.dataset.estoque);
            
            if (cartItems[id]) {
                // Check stock before incrementing
                if (cartItems[id].quantidade < estoque) {
                    cartItems[id].quantidade++;
                    updateCartItemDisplay(id);
                    
                    // Provide tactile feedback
                    navigator.vibrate && navigator.vibrate(50);
                } else {
                    alert('Quantidade máxima em estoque atingida!');
                }
            } else {
                cartItems[id] = {
                    id: id,
                    nome: nome,
                    preco: preco,
                    quantidade: 1
                };
                
                // Create cart item element
                createCartItemElement(id);
                
                // Provide tactile feedback
                navigator.vibrate && navigator.vibrate(50);
                
                // On mobile, show a brief indication that item was added
                if (window.innerWidth < 992) {
                    this.innerHTML = '<i class="fas fa-check"></i>';
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-plus"></i>';
                    }, 500);
                }
            }
            
            updateCartTotal();
            updateFinishButton();
            
            // Show cart on mobile when adding first item
            if (Object.keys(cartItems).length === 1 && window.innerWidth < 992) {
                cartContainer.classList.add('mobile-cart-visible');
                
                // Scroll to show the cart
                setTimeout(() => {
                    const mobileCartSpacer = document.querySelector('.mobile-cart-spacer');
                    if (mobileCartSpacer) {
                        mobileCartSpacer.scrollIntoView({ behavior: 'smooth', block: 'end' });
                    }
                }, 100);
            }
        });
    });
    
    // Add toggle functionality
    if (cartToggle) {
        cartToggle.querySelector('.toggle-cart-items').addEventListener('click', function() {
            const cartItemsContainer = document.getElementById('cart-items');
            const cartItemElements = cartItemsContainer.querySelectorAll('.cart-item');
            const toggleText = this.querySelector('.toggle-text');
            const toggleIcon = this.querySelector('.toggle-icon');
            
            isCartExpanded = !isCartExpanded;
            
            cartItemElements.forEach((item, index) => {
                if (index >= 2) {
                    item.style.display = isCartExpanded ? 'block' : 'none';
                }
            });
            
            toggleText.textContent = isCartExpanded ? 'Ver menos' : 'Ver todos';
            toggleIcon.classList.toggle('fa-chevron-up');
            toggleIcon.classList.toggle('fa-chevron-down');
        });
    }
    
    // Create cart item HTML element
    function createCartItemElement(id) {
        const item = cartItems[id];
        
        // Hide empty cart message
        emptyCartMessage.style.display = 'none';
        
        // Create new cart item
        const cartItemEl = document.createElement('div');
        cartItemEl.className = 'cart-item mb-2 border-bottom pb-2';
        cartItemEl.dataset.id = id;
        
        // Check if this is the third or later item
        const existingItems = document.querySelectorAll('.cart-item').length;
        if (existingItems >= 2) {
            cartItemEl.style.display = isCartExpanded ? 'block' : 'none';
        }
        
        cartItemEl.innerHTML = `
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center flex-grow-1">
                    <div class="input-group input-group-sm me-2" style="width: 80px;">
                        <button type="button" class="btn btn-outline-secondary decrease-quantity px-1">-</button>
                        <input type="number" class="form-control text-center item-quantity p-0" 
                               value="${item.quantidade}" min="1" name="quantidade[${id}]">
                        <button type="button" class="btn btn-outline-secondary increase-quantity px-1">+</button>
                    </div>
                    <div class="text-truncate" style="max-width: 130px;">
                        <small class="fw-medium">${item.nome}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="item-price me-2">
                        <small class="fw-bold">R$ ${formatPrice(item.preco * item.quantidade)}</small>
                    </div>
                    <button type="button" class="btn btn-sm text-danger remove-item p-0" style="font-size: 16px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <input type="hidden" name="produto_id[]" value="${id}">
            <input type="hidden" name="preco[]" value="${item.preco}">
        `;
        
        document.getElementById('cart-items').appendChild(cartItemEl);
        updateCartCounter();
        
        // Show/hide toggle button based on number of items
        const totalItems = document.querySelectorAll('.cart-item').length;
        cartToggle.style.display = totalItems > 2 ? 'block' : 'none';

        // Add event listeners for the new item
        const newItem = document.querySelector(`.cart-item[data-id="${id}"]`);
        
        // Increase quantity
        newItem.querySelector('.increase-quantity').addEventListener('click', function() {
            const estoque = parseInt(document.querySelector(`.add-product[data-id="${id}"]`).dataset.estoque);
            if (item.quantidade < estoque) {
                item.quantidade++;
                updateCartItemDisplay(id);
                updateCartTotal();
            } else {
                alert('Quantidade máxima em estoque atingida!');
            }
        });
        
        // Decrease quantity
        newItem.querySelector('.decrease-quantity').addEventListener('click', function() {
            if (item.quantidade > 1) {
                item.quantidade--;
                updateCartItemDisplay(id);
                updateCartTotal();
            }
        });
        
        // Manual input quantity
        newItem.querySelector('.item-quantity').addEventListener('change', function() {
            const newQty = parseInt(this.value);
            const estoque = parseInt(document.querySelector(`.add-product[data-id="${id}"]`).dataset.estoque);
            
            if (newQty < 1) {
                this.value = 1;
                item.quantidade = 1;
            } else if (newQty > estoque) {
                this.value = estoque;
                item.quantidade = estoque;
                alert('Quantidade ajustada para o máximo disponível em estoque!');
            } else {
                item.quantidade = newQty;
            }
            
            updateCartItemDisplay(id);
            updateCartTotal();
        });
        
        // Remove item
        newItem.querySelector('.remove-item').addEventListener('click', function() {
            delete cartItems[id];
            newItem.remove();
            updateCartTotal();
            updateCartCounter();
            
            // Show empty cart message if needed
            if (Object.keys(cartItems).length === 0) {
                emptyCartMessage.style.display = 'block';
            }
            updateFinishButton();
        });
    }
    
    // Update cart counter in mobile view
    function updateCartCounter() {
        const itemCount = Object.keys(cartItems).length;
        cartCounter.textContent = itemCount;
        
        if (itemCount > 0) {
            toggleCartBtn.classList.add('btn-primary');
            toggleCartBtn.classList.remove('btn-outline-primary');
        } else {
            toggleCartBtn.classList.remove('btn-primary');
            toggleCartBtn.classList.add('btn-outline-primary');
        }
    }
    
    // Update cart item display
    function updateCartItemDisplay(id) {
        const item = cartItems[id];
        const cartItemEl = document.querySelector(`.cart-item[data-id="${id}"]`);
        
        cartItemEl.querySelector('.item-quantity').value = item.quantidade;
        cartItemEl.querySelector('.item-price').textContent = `R$ ${formatPrice(item.preco * item.quantidade)}`;
    }
    
    // Update cart total and toggle visibility
    function updateCartTotal() {
        let total = 0;
        
        for (const id in cartItems) {
            const item = cartItems[id];
            total += item.preco * item.quantidade;
        }
        
        cartTotal.textContent = `R$ ${formatPrice(total)}`;
        valorTotalInput.value = total.toFixed(2);
        
        // Se o carrinho estiver vazio, garanta que o valor seja 0
        if (Object.keys(cartItems).length === 0) {
            emptyCartMessage.style.display = 'block';
            valorTotalInput.value = '0';
            cartToggle.style.display = 'none';
        }
        
        // Show/hide toggle button based on number of items
        const totalItems = Object.keys(cartItems).length;
        cartToggle.style.display = totalItems > 2 ? 'block' : 'none';
    }
    
    // Format price
    function formatPrice(price) {
        return price.toFixed(2).replace('.', ',');
    }
    
    // Form validation before submit
    document.getElementById('cart-form').addEventListener('submit', function(e) {
        // Lista de mensagens de erro
        const errors = [];
        
        // Verificar se há produtos no carrinho
        const cartHasItems = Object.keys(cartItems).length > 0;
        if (!cartHasItems) {
            errors.push('Adicione pelo menos um produto ao carrinho');
            // Destacar visualmente a seção do carrinho
            document.getElementById('cart-items').classList.add('highlight-error');
            setTimeout(() => {
                document.getElementById('cart-items').classList.remove('highlight-error');
            }, 3000);
        }
        
        // Verificar se a forma de pagamento foi selecionada
        if (paymentMethodInput.value === '') {
            errors.push('Selecione uma forma de pagamento');
            // Destacar visualmente a seção de pagamento
            document.querySelector('.payment-buttons').classList.add('highlight-error');
            setTimeout(() => {
                document.querySelector('.payment-buttons').classList.remove('highlight-error');
            }, 3000);
        }
        
        // Verificar se o valor total é válido
        const totalValue = parseFloat(valorTotalInput.value);
        if (totalValue <= 0) {
            errors.push('O valor total da venda deve ser maior que zero');
        }
        
        // Se houver erros, impedir o envio do formulário e mostrar mensagem
        if (errors.length > 0) {
            e.preventDefault();
            
            // Exibir a mensagem no toast com cada erro em uma linha separada
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.innerHTML = '<strong>INFORMAÇÕES INCOMPLETAS:</strong><br>' + 
                                    errors.map(err => `• ${err}`).join('<br>');
            
            // Mostrar o toast
            if (errorToast) {
                errorToast.show();
            } else {
                // Fallback caso o objeto do toast não esteja disponível
                alert(errors.join(' e '));
            }
            
            // Se falta forma de pagamento, destacar a seção
            if (paymentMethodInput.value === '' && paymentRequired) {
                paymentRequired.style.display = 'inline';
                
                // Em mobile, garantir que a área de forma de pagamento esteja visível
                if (window.innerWidth < 992) {
                    cartContainer.classList.add('mobile-cart-visible');
                    
                    // Rolar até a área de pagamento
                    setTimeout(() => {
                        const paymentButtons = document.querySelector('.payment-buttons');
                        if (paymentButtons) {
                            paymentButtons.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }, 300);
                }
            }
            
            return false;
        }
    });
    
    // Add backdrop click handler
    const cartBackdrop = document.querySelector('.cart-backdrop');
    if (cartBackdrop) {
        cartBackdrop.addEventListener('click', function() {
            if (cartContainer.classList.contains('mobile-cart-visible')) {
                cartContainer.classList.remove('mobile-cart-visible');
            }
        });
    }
    
    // Touch swipe handling for cart
    if (window.innerWidth < 992) {
        let touchStartY = 0;
        let touchEndY = 0;
        const minSwipeDistance = 50;
        
        const cartHandle = document.querySelector('.mobile-cart-handle');
        if (cartHandle) {
            // Add touch events for cart dragging
            cartHandle.addEventListener('touchstart', function(e) {
                touchStartY = e.changedTouches[0].screenY;
            }, {passive: true});
            
            cartHandle.addEventListener('touchend', function(e) {
                touchEndY = e.changedTouches[0].screenY;
                handleSwipe();
            }, {passive: true});
        }
        
        function handleSwipe() {
            if (touchStartY - touchEndY < -minSwipeDistance) {
                // Swipe down
                cartContainer.classList.remove('mobile-cart-visible');
            }
        }
    }

    <?php
    // Verificar se o caixa está fechado
    $sql_check_caixa = "SELECT status FROM controle_caixa 
                        WHERE usuario_id = ? 
                        AND DATE(data_abertura) = CURRENT_DATE 
                        ORDER BY id DESC LIMIT 1";
    $stmt_check = mysqli_prepare($conn, $sql_check_caixa);
    mysqli_stmt_bind_param($stmt_check, "i", $_SESSION['usuario_id']);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $caixa_status = '';
    
    if ($row_check = mysqli_fetch_assoc($result_check)) {
        $caixa_status = $row_check['status'];
    } else {
        $caixa_status = 'fechado';
    }
    
    if ($caixa_status !== 'aberto'): ?>
        // Mostrar modal de caixa fechado
        const caixaFechadoModal = new bootstrap.Modal(document.getElementById('caixaFechadoModal'));
        caixaFechadoModal.show();
    <?php endif; ?>
});
</script>

<style>
/* Estilos gerais de produto */
.product-card {
    transition: all 0.3s ease;
    border-radius: 8px;
    overflow: hidden;
}

.product-card .card-body {
    padding: 0.5rem !important; /* Reduzindo o padding */
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.product-card .product-name {
    font-weight: 600;
    color: #333;
    line-height: 1.2;
    margin-bottom: 0.25rem; /* Reduzindo margem */
    font-size: 0.9rem; /* Reduzindo tamanho da fonte */
}

.product-card .price-tag {
    font-size: 1rem;
    color: #2a5298 !important;
    margin-bottom: 0.25rem; /* Reduzindo margem */
}

.product-card .stock-info {
    font-size: 0.75rem; /* Reduzindo tamanho da fonte */
}

/* Estilo do botão de adicionar */
.add-product-btn {
    width: 32px !important; /* Tamanho fixo menor */
    height: 32px !important;
    padding: 0 !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    border-radius: 8px !important; /* Cantos arredondados quadrados */
    font-size: 14px !important; /* Ícone menor */
    min-width: unset !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.add-product-btn:hover {
    transform: scale(1.05);
}

.add-product-btn:active {
    transform: scale(0.95);
}

/* Ajuste do espaçamento entre cards */
.row.g-3 {
    --bs-gutter-y: 0.75rem !important; /* Reduzindo espaço vertical entre cards */
}

.product-info {
    min-width: 0; /* Permite que o texto seja truncado corretamente */
}

/* Ajuste para telas pequenas */
@media (max-width: 576px) {
    .product-card .card-body {
        padding: 0.4rem !important;
    }
    
    .product-card .product-name {
        font-size: 0.85rem;
    }
    
    .product-card .price-tag {
        font-size: 0.9rem;
    }
    
    .add-product-btn {
        width: 28px !important;
        height: 28px !important;
        font-size: 12px !important;
    }
}

/* Estilos gerais de produto */
.product-card {
    transition: all 0.3s ease;
    border-radius: 8px;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.product-card .card-body {
    border-radius: 3px;
    background: #fff;
}

.product-card.out-of-stock {
    opacity: 0.7;
    border-style: dashed !important;
}

.product-card .product-name {
    font-weight: 600;
    color: #333;
    line-height: 1.3;
    margin-bottom: 0.5rem;
}

.product-card .price-tag {
    font-size: 1.1rem;
    color: #2a5298 !important;
}

.product-card .stock-info {
    font-size: 0.85rem;
}

.product-card .stock-badge {
    padding: 8px;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.btn-circle {
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #0d6efd;
    color: white;
    border: none;
    transition: all 0.2s;
}

.btn-circle:hover {
    background-color: #0b5ed7;
    transform: scale(1.1);
}

.stock-info {
    position: absolute;
    bottom: 4px;
    right: 8px;
    font-size: 0.75rem;
}

.stock-info i {
    font-size: 0.7rem;
}

.stock-badge {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.out-of-stock {
    opacity: 0.7;
}

.out-of-stock::before {
    content: none;
}

@media (max-width: 576px) {
    .product-name {
        font-size: 0.85rem;
    }
    
    .price-tag {
        font-size: 0.9rem;
    }
    
    .btn-circle {
        width: 28px;
        height: 28px;
    }
    
    .stock-info {
        font-size: 0.7rem;
    }
}

/* Mobile optimizations */
@media (max-width: 991px) {
    .mobile-cart-handle {
        width: 40px;
        height: 5px;
        background-color: #dee2e6;
        border-radius: 5px;
        margin: 10px auto 0;
        display: none;
        cursor: grab;
    }
    
    #cart-container {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1020;
        margin: 0;
        padding: 0;
        max-height: 70vh;
        overflow-y: auto;
        transform: translateY(100%);
        transition: transform 0.3s ease-out;
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.1);
    }
    
    #cart-container.mobile-cart-visible {
        transform: translateY(0);
    }
    
    #cart-container.mobile-cart-visible .mobile-cart-handle {
        display: block;
    }
    
    .cart-card {
        border-radius: 15px 15px 0 0;
        margin-bottom: 0 !important;
        max-height: 70vh;
    }
    
    .cart-items-container {
        max-height: 30vh;
        overflow-y: auto;
    }
    
    .product-card {
        transition: all 0.2s;
    }
    
    .product-card:active {
        transform: scale(0.95);
    }
    
    /* Aumentar o tamanho do botão de adicionar produto */
    .add-product-btn {
        width: 46px;
        height: 46px;
        font-size: 18px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
    }
    
    /* Aumentar o tamanho do indicador de estoque */
    .stock-badge {
        font-size: 14px !important;
        padding: 5px 10px;
        border-radius: 20px;
        min-width: 35px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* Ajustar espaçamento para cards de produto em telas pequenas */
    .product-card .card-body {
        padding: 0.75rem;
    }
    
    .product-card .card-title {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    
    /* Melhorar visualização do indicador de preço */
    .price-badge {
        font-size: 1.1rem !important;
        padding: 0.3rem 0.8rem;
        margin-bottom: 0.75rem;
        display: inline-block;
        width: auto;
    }
    
    /* Improve scrolling */
    .categories-filter {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
    }
    
    .categories-filter::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }
    
    .cart-counter {
        display: inline-block;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        min-width: 18px;
        height: 18px;
        font-size: 12px;
        text-align: center;
        line-height: 18px;
    }
    
    /* Hide text on payment buttons for smallest screens */
    @media (max-width: 380px) {
        .payment-buttons .btn {
            padding: 0.375rem;
        }
    }
    
    /* Fixed space at the bottom for mobile */
    .mobile-cart-spacer {
        display: none;
        height: 70px; /* Minimum height when cart is closed */
    }
    
    /* Cart backdrop for better visibility */
    .cart-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0,0,0,0.4);
        z-index: 1019;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    
    #cart-container.mobile-cart-visible + .cart-backdrop {
        opacity: 1;
        pointer-events: auto;
    }
}

/* Estilo para melhorar a visualização do estado ativo dos botões de pagamento */
.payment-method-btn.active {
    transform: scale(1.05);
    box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 1;
    font-weight: bold;
}

.payment-method-btn.active:after {
    content: '✓';
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: #28a745;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

/* Estilos específicos para cada tipo de pagamento quando ativos */
.payment-method-btn[data-method="Dinheiro"].active {
    background-color: #146c43 !important;
    color: white !important;
    border-color: #146c43 !important;
}

.payment-method-btn[data-method="Pix"].active {
    background-color: #0a58ca !important;
    color: white !important;
    border-color: #0a58ca !important;
}

.payment-method-btn[data-method="Cartão"].active {
    background-color: #cc9a06 !important;
    color: white !important;
    border-color: #cc9a06 !important;
}

/* Estilo dos toasts de erro */
#errorToast {
    min-width: 280px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    font-size: 16px;
    font-weight: bold;
    padding: 10px 5px;
    border-left: 5px solid #dc0000;
}

@media (max-width: 768px) {
    #errorToast {
        width: 90%;
        max-width: none;
        left: 5%;
        right: 5%;
        bottom: 10px;
    }
    
    .position-fixed.bottom-0.end-0.p-3 {
        left: 0;
        right: 0;
        bottom: 0;
        padding: 0 !important;
    }
    
    .toast-body {
        padding: 12px 8px;
    }
}

.payment-required {
    animation: pulse 1.5s infinite;
    font-size: 14px;
    padding: 2px 5px;
    border-radius: 3px;
    background-color: rgba(220, 53, 69, 0.1);
}

@keyframes pulse {
    0% {
        opacity: 0.7;
        transform: scale(1);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    100% {
        opacity: 0.7;
        transform: scale(1);
    }
}

/* Estilo para destacar campos com erro */
.highlight-error {
    animation: flashError 1.5s;
    border: 2px solid #dc3545 !important;
    border-radius: 5px;
    box-shadow: 0 0 8px rgba(220, 53, 69, 0.6);
}

@keyframes flashError {
    0%, 100% { 
        background-color: transparent; 
    }
    50% { 
        background-color: rgba(220, 53, 69, 0.2); 
    }
}

/* Estilo para alertas do servidor */
.server-alert {
    margin-bottom: 20px;
    padding: 15px;
    border-width: 1px;
    border-left-width: 5px;
    font-size: 16px;
}

.server-alert.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
}

.server-alert.alert-success {
    background-color: rgba(25, 135, 84, 0.1);
    border-color: #198754;
}

.server-alert .alert-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.alert-danger .alert-icon {
    color: #dc3545;
}

.alert-success .alert-icon {
    color: #198754;
}

/* Animação de entrada para os alertas */
.alert.fade.show {
    animation: alertIn 0.5s ease forwards;
}

@keyframes alertIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .server-alert {
        margin-left: 10px;
        margin-right: 10px;
    }
}

/* Estilo para a modal de venda concluída */
#vendaConcluidaModal .modal-content {
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

#vendaConcluidaModal .modal-header {
    border-bottom: none;
    padding: 15px 20px;
}

#vendaConcluidaModal .modal-body {
    padding: 20px;
}

#vendaConcluidaModal .modal-footer {
    border-top: none;
    padding: 15px 20px;
}

#vendaConcluidaModal .display-1 {
    font-size: 5rem;
    animation: successPulse 2s infinite;
}

@keyframes successPulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

#vendaConcluidaModal h4 {
    font-weight: 600;
    margin-bottom: 10px;
}

#vendaConcluidaModal .btn-primary {
    background-color: #198754;
    border-color: #198754;
    padding: 10px 20px;
    font-weight: 600;
}

#vendaConcluidaModal .btn-primary:hover {
    background-color: #146c43;
    border-color: #146c43;
}

#vendaConcluidaModal .btn-outline-secondary {
    padding: 8px 15px;
}

/* Estilo para produtos fora de estoque */
.out-of-stock {
    position: relative;
    border-color: #dc3545;
    opacity: 0.8;
    background-color: rgba(220, 53, 69, 0.05);
}

.out-of-stock::before {
    content: "FORA DE ESTOQUE";
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    text-align: center;
    font-weight: bold;
    color: #dc3545;
    font-size: 0.85rem;
    transform: translateY(-50%) rotate(-15deg);
    z-index: 1;
    opacity: 0.8;
    letter-spacing: 1px;
    pointer-events: none;
}

.out-of-stock .card-body {
    opacity: 0.85;
}

/* Estilos para o toggle do carrinho */
.toggle-cart-items {
    width: 100%;
    padding: 5px;
    margin-top: -10px;
    border: 1px dashed #dee2e6;
    background-color: #f8f9fa;
    transition: all 0.2s ease;
}

.toggle-cart-items:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.toggle-cart-items .toggle-icon {
    margin-left: 5px;
    transition: transform 0.2s ease;
}

.toggle-cart-items .toggle-text {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Animação para os itens do carrinho */
.cart-item {
    transition: all 0.3s ease;
}

@media (max-width: 991px) {
    .cart-items-container {
        max-height: none;
        overflow-y: visible;
    }
    
    #cart-toggle {
        position: sticky;
        bottom: 0;
        background-color: white;
        padding: 5px 0;
        border-top: 1px solid #dee2e6;
        z-index: 1;
    }
    
    .toggle-cart-items {
        margin-top: 0;
        border-radius: 20px;
        padding: 8px 15px;
    }
}

/* Estilos para itens do carrinho mais compactos */
.cart-item {
    padding: 4px 0;
    margin-bottom: 4px !important;
}

.cart-item .input-group-sm > .form-control,
.cart-item .input-group-sm > .btn {
    padding: 2px;
    font-size: 0.8rem;
    line-height: 1.2;
    min-height: 24px;
}

.cart-item .input-group {
    min-width: 80px;
}

.cart-item .form-control {
    height: 24px;
    padding: 0 2px !important;
}

.cart-item small {
    font-size: 0.85rem;
    line-height: 1.2;
}

.cart-item .remove-item {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.cart-item .remove-item:hover {
    opacity: 1;
}

.cart-card .bg-dark {
    background-color: #212529 !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.cart-card #cart-total {
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

@media (max-width: 991px) {
    .cart-card .bg-dark {
        border-radius: 8px;
        margin: 0 2px;
    }
}

.total-section {
    margin: 8px 0;
    position: relative;
}

.total-section .bg-dark {
    background-color: #212529 !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.total-section #cart-total {
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    font-size: 1.1rem !important;
}

@media (max-width: 991px) {
    .total-section {
        margin: 8px 2px;
    }
    
    .total-section .rounded-pill {
        border-radius: 20px !important;
    }
}

/* Atualize os estilos do carrinho */
.cart-card {
    background-color: #212529;
    color: #fff;
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.cart-card .card-header {
    background-color: #2c3136;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    color: #fff;
}

.cart-card .text-muted {
    color: #adb5bd !important;
}

/* Estilo para o carrinho vazio */
#empty-cart {
    color: #adb5bd;
}

/* Estilos para itens do carrinho */
.cart-item {
    padding: 4px 0;
    margin-bottom: 4px !important;
    border-bottom: 1px solid rgba(255,255,255,0.1) !important;
}

.cart-item small {
    color: #e9ecef;
}

.cart-item .input-group-sm > .form-control,
.cart-item .input-group-sm > .btn {
    background-color: #2c3136;
    border-color: #495057;
    color: #fff;
}

.cart-item .input-group-sm > .form-control:focus {
    background-color: #343a40;
    color: #fff;
    border-color: #6c757d;
}

.cart-item .input-group-sm > .btn:hover {
    background-color: #343a40;
    border-color: #6c757d;
    color: #fff;
}

.cart-item .remove-item {
    color: #dc3545 !important;
    opacity: 0.8;
}

.cart-item .remove-item:hover {
    opacity: 1;
}

/* Estilo para o botão de toggle do carrinho */
.toggle-cart-items {
    background-color: #2c3136;
    border-color: #495057;
    color: #adb5bd;
}

.toggle-cart-items:hover {
    background-color: #343a40;
    border-color: #6c757d;
    color: #fff;
}

/* Ajustes para o total e botões de pagamento */
.total-section .bg-dark {
    background-color: #2c3136 !important;
}

.payment-buttons .btn {
    border: 1px solid rgba(255,255,255,0.1);
}

/* Mobile adjustments */
@media (max-width: 991px) {
    #cart-container {
        background-color: #212529;
    }
    
    .cart-card {
        background-color: #212529;
    }
    
    #cart-toggle {
        background-color: #212529;
        border-top: 1px solid rgba(255,255,255,0.1);
    }
}

/* Ajuste para o backdrop do carrinho mobile */
.cart-backdrop {
    background-color: rgba(0,0,0,0.5);
}

/* Estilo para o handle do carrinho mobile */
.mobile-cart-handle {
    background-color: rgba(255,255,255,0.2);
}
</style>

<?php include 'footer.php'; ?> 