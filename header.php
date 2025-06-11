<?php require_once 'check_login.php'; ?>

<?php 
// Ativar exibição de erros PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Sao_Paulo'); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema PDV</title>
    <!-- Favicon -->
    <link rel="icon" href="logo.jpeg" type="image/jpeg">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #343a40;
            --accent-color: #198754;
        }

        body {
            touch-action: manipulation;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Sidebar styles */
        .sidebar {
            min-height: 100vh;
            background-color: var(--secondary-color);
            padding-top: 20px;
            transition: all 0.3s ease;
            position: fixed;
            width: 16.66%;
            z-index: 1030;
            overflow-y: auto;
        }
        
        .sidebar a {
            color: #f8f9fa;
            padding: 10px 15px;
            display: block;
            text-decoration: none;
            border-radius: 5px;
            margin: 2px 5px;
            transition: all 0.2s;
        }
        
        /* Submenu styles */
        .submenu {
            margin-left: 10px;
            border-left: 1px solid rgba(255,255,255,0.1);
        }
        
        .submenu a {
            font-size: 0.9em;
            padding: 8px 15px;
            opacity: 0.9;
        }
        
        .submenu a:hover {
            opacity: 1;
        }
        
        .menu-group {
            margin-bottom: 10px;
        }
        
        .menu-group > a.active + .submenu {
            border-left-color: var(--primary-color);
        }
        
        .sidebar a:hover {
            background-color: #495057;
            transform: translateX(3px);
        }
        
        .sidebar .active {
            background-color: var(--primary-color);
            position: relative;
        }
        
        .sidebar .active::before {
            content: '';
            position: absolute;
            left: -5px;
            top: 0;
            height: 100%;
            width: 5px;
            background-color: #ffffff;
            border-radius: 0 3px 3px 0;
        }
        
        .sidebar i {
            margin-right: 10px;
        }
        
        .content {
            padding: 20px;
            margin-left: 16.66%;
            transition: all 0.3s ease;
        }
        
        .card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            background-color: rgba(0,0,0,0.03);
            font-weight: 500;
        }
        
        .gauge-container {
            width: 200px;
            height: 200px;
            margin: 0 auto;
            position: relative;
        }
        
        .user-info {
            color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .user-info img {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            margin-bottom: 10px;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .user-info h5 {
            margin-bottom: 5px;
            font-size: 1rem;
        }
        
        .user-info .badge {
            font-size: 0.8rem;
            padding: 5px 10px;
        }
        
        .logout-btn {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        /* Mobile styles */
        #mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1040;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .sidebar {
                width: 250px;
                left: -250px;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .content {
                margin-left: 0;
                padding-top: 60px;
            }
            
            #mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .page-title-mobile {
                text-align: center;
                margin-bottom: 15px;
            }
        }
        
        /* Overlay for mobile menu */
        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1025;
        }

        /* Venda page styles */
        .header-section {
            margin: -1.5rem -1.5rem 2rem -1.5rem;
        }

        .main-header {
            position: relative;
            overflow: hidden;
        }

        .main-header::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M0 0h100v100H0z" fill="none"/><path d="M100 0H0v100h100V0zM9.941 50c0 22.124 17.935 40.059 40.059 40.059 22.124 0 40.059-17.935 40.059-40.059 0-22.124-17.935-40.059-40.059-40.059C27.876 9.941 9.941 27.876 9.941 50z" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.1;
        }

        .header-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .header-icon-sm {
            width: 35px;
            height: 35px;
            background: rgba(13, 110, 253, 0.1);
            border-radius: 10px;
        }

        .stats-header {
            background-size: cover;
            position: relative;
        }

        .stats-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(45deg, rgba(0,0,0,0.1) 0%, rgba(255,255,255,0.1) 100%);
        }

        .stat-item {
            position: relative;
            z-index: 1;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        /* Produtos section styles */
        .products-container {
            border: none;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.05) !important;
        }

        .card-header {
            border: none;
            background: #fff;
        }

        .search-container .input-group {
            border: 2px solid #e9ecef;
            padding: 3px;
            transition: all 0.3s ease;
        }

        .search-container .input-group:focus-within {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .category-btn {
            font-weight: 500;
            letter-spacing: 0.5px;
            padding: 8px 16px;
        }

        .category-btn.active {
            transform: scale(1.05);
        }

        .product-card {
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .cart-counter {
            position: relative;
            top: -8px;
            right: -8px;
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 10px;
            background: #dc3545;
            color: white;
            border: 2px solid #fff;
        }

        @media (max-width: 768px) {
            .header-section {
                margin: -1rem -1rem 1rem -1rem;
            }

            .main-header, .stats-header {
                border-radius: 0 !important;
            }

            .header-icon {
                width: 50px;
                height: 50px;
            }

            .stat-icon {
                width: 35px;
                height: 35px;
            }

            /* Mobile product styles */
            .product-card {
                border-radius: 12px;
                margin-bottom: 0;
            }

            .product-card .card-body {
                padding: 12px 15px;
            }

            .product-info {
                min-width: 0; /* Para garantir que o texto quebre corretamente */
            }

            .product-name {
                font-size: 1rem;
                margin-bottom: 4px !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .price-tag {
                font-size: 1.1rem;
                color: var(--primary-color) !important;
            }

            .stock-info {
                font-size: 0.8rem;
                opacity: 0.8;
            }

            .mobile-square-btn {
                width: 42px;
                height: 42px;
                border-radius: 10px;
                background-color: var(--primary-color);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0;
                font-size: 1.2rem;
                transition: all 0.2s ease;
                border: none;
                box-shadow: 0 2px 5px rgba(13, 110, 253, 0.2);
            }

            .mobile-square-btn:active {
                transform: scale(0.95);
                background-color: #0b5ed7;
            }

            .mobile-square-btn:hover {
                background-color: #0b5ed7;
                color: white;
            }

            .mobile-square-btn:focus {
                box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
            }

            /* Ajuste do espaçamento entre produtos */
            .product-item {
                margin-bottom: 10px;
            }

            /* Melhorar visualização do estoque */
            .stock-info {
                margin-top: 4px !important;
            }

            .stock-info small {
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .stock-info i {
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body class="<?php echo isset($_SESSION['custom_body_class']) ? $_SESSION['custom_body_class'] : ''; ?>">
    <!-- Mobile menu toggle button -->
    <button id="mobile-menu-toggle" type="button" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Overlay for mobile menu -->
    <div class="menu-overlay"></div>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar d-flex flex-column">
                <div class="user-info">
                    <img src="logo.jpeg" alt="Logo" class="img-fluid" style="width: 64px; height: 64px; object-fit: cover;">
                    <h5><?php echo $_SESSION['usuario_nome']; ?></h5>
                    <?php if ($_SESSION['nivel'] === 'administrador'): ?>
                        <span class="badge bg-danger">Administrador</span>
                    <?php else: ?>
                        <span class="badge bg-success">Caixa <?php echo $_SESSION['caixa_numero']; ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if ($_SESSION['nivel'] === 'administrador'): ?>
                    <!-- Menu de Administrador -->
                    <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    
                    <!-- Menu Produtos com Submenu -->
                    <div class="menu-group">
                        <a href="produtos.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['produtos.php', 'adicionar_produto.php', 'editar_produto.php', 'categorias.php', 'editar_categoria.php']) ? 'active' : ''; ?> menu-item">
                            <i class="fas fa-box"></i> Produtos
                        </a>
                        <div class="submenu" style="padding-left: 20px;">
                            <a href="produtos/categorias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categorias.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-tags"></i> Categorias
                            </a>
                            <a href="produtos/gerenciar_permissoes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gerenciar_permissoes.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-key"></i> Permissões
                            </a>
                        </div>
                    </div>

                    <a href="vender.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'vender.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-shopping-cart"></i> Vender
                    </a>
                    <a href="lista_vendas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'lista_vendas.php' || basename($_SERVER['PHP_SELF']) == 'detalhes_venda.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-receipt"></i> Vendas
                    </a>
                    
                    <!-- Menu de Caixa com Submenu -->
                    <div class="menu-group">
                        <a href="controle_caixa.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['controle_caixa.php', 'gestao_caixas.php']) ? 'active' : ''; ?> menu-item">
                            <i class="fas fa-cash-register"></i> Caixa
                        </a>
                        <div class="submenu" style="padding-left: 20px;">
                            <a href="gestao_caixas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gestao_caixas.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-history"></i> Histórico
                            </a>
                            <a href="sangrias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sangrias.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-money-bill-wave"></i> Sangrias
                            </a>
                        </div>
                    </div>

                    <a href="usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' || basename($_SERVER['PHP_SELF']) == 'adicionar_usuario.php' || basename($_SERVER['PHP_SELF']) == 'editar_usuario.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-users"></i> Usuários
                    </a>
                    <a href="limpar_sistema.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'limpar_sistema.php' ? 'active' : ''; ?> menu-item text-danger">
                        <i class="fas fa-trash-alt"></i> Limpar Sistema
                    </a>
                <?php else: ?>
                    <!-- Menu de Operador de Caixa -->
                    <a href="vender.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'vender.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-shopping-cart"></i> Vender
                    </a>
                    <a href="lista_vendas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'lista_vendas.php' || basename($_SERVER['PHP_SELF']) == 'detalhes_venda.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-receipt"></i> Minhas Vendas
                    </a>
                    
                    <!-- Menu de Caixa com Submenu -->
                    <div class="menu-group">
                        <a href="controle_caixa.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['controle_caixa.php', 'gestao_caixas.php']) ? 'active' : ''; ?> menu-item">
                            <i class="fas fa-cash-register"></i> Meu Caixa
                        </a>
                        <div class="submenu" style="padding-left: 20px;">
                            <a href="gestao_caixas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gestao_caixas.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-history"></i> Histórico
                            </a>
                            <a href="sangrias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sangrias.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-money-bill-wave"></i> Sangrias
                            </a>
                        </div>
                    </div>

                    <a href="usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' || basename($_SERVER['PHP_SELF']) == 'adicionar_usuario.php' || basename($_SERVER['PHP_SELF']) == 'editar_usuario.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-users"></i> Usuários
                    </a>
                <?php endif; ?>
                
                <a href="logout.php" class="mt-auto logout-btn menu-item">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
            <div class="col-md-10 content">
                <div class="d-none d-md-flex justify-content-between align-items-center mb-4">
                    <h2><?php echo ucfirst(str_replace(['.php', '_'], ['', ' '], basename($_SERVER['PHP_SELF']))); ?></h2>
                    <div>
                        <span class="badge bg-dark">
                            <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d/m/Y'); ?>
                        </span>
                        <span class="badge bg-primary ms-2">
                            <i class="fas fa-user me-1"></i> <?php echo $_SESSION['usuario']; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Mobile header -->
                <div class="d-block d-md-none page-title-mobile">
                    <h4><?php echo ucfirst(str_replace(['.php', '_'], ['', ' '], basename($_SERVER['PHP_SELF']))); ?></h4>
                    <div class="d-flex justify-content-center gap-2 mb-2">
                        <span class="badge bg-dark">
                            <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d/m/Y'); ?>
                        </span>
                        <span class="badge bg-primary">
                            <i class="fas fa-user me-1"></i> <?php echo $_SESSION['usuario']; ?>
                        </span>
                    </div>
                </div>
                
                <hr>
                
                <!-- Mobile menu script -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
                    const sidebar = document.querySelector('.sidebar');
                    const overlay = document.querySelector('.menu-overlay');
                    const menuItems = document.querySelectorAll('.menu-item');
                    
                    function toggleMenu() {
                        sidebar.classList.toggle('active');
                        
                        if (sidebar.classList.contains('active')) {
                            overlay.style.display = 'block';
                            document.body.style.overflow = 'hidden';
                        } else {
                            overlay.style.display = 'none';
                            document.body.style.overflow = 'auto';
                        }
                    }
                    
                    mobileMenuToggle.addEventListener('click', toggleMenu);
                    overlay.addEventListener('click', toggleMenu);
                    
                    menuItems.forEach(item => {
                        item.addEventListener('click', function() {
                            if (window.innerWidth <= 991) {
                                toggleMenu();
                            }
                        });
                    });
                });
                </script> 