<?php require_once 'check_login.php'; 
$base_path = '';
if (strpos($_SERVER['SCRIPT_NAME'], '/produtos/') !== false) {
    $base_path = '../';
}
?>

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
    <link rel="icon" href="<?php echo $base_path; ?>logo.jpeg" type="image/jpeg">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #3b82f6; /* Modern Indigo/Blue */
            --primary-hover: #2563eb;
            --secondary-color: #0f172a; /* Sleek Slate Dark */
            --accent-color: #10b981; /* Emerald */
            --bg-sidebar: #0f172a;
            --bg-sidebar-hover: #1e293b;
            --text-sidebar: #94a3b8;
            --text-sidebar-active: #ffffff;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            touch-action: manipulation;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Sidebar styles */
        .sidebar {
            min-height: 100vh;
            background-color: var(--bg-sidebar);
            padding-top: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            width: var(--sidebar-width);
            z-index: 1030;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .sidebar a {
            color: var(--text-sidebar);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-radius: 8px;
            margin: 4px 12px;
            font-weight: 500;
            font-size: 0.92rem;
            transition: all 0.2s ease;
        }
        
        /* Submenu styles */
        .submenu {
            margin-left: 20px;
            border-left: 1px solid rgba(255,255,255,0.05);
            padding-left: 5px;
        }
        
        .submenu a {
            font-size: 0.85rem;
            padding: 8px 16px;
            margin: 2px 12px;
        }
        
        .submenu a:hover {
            color: var(--text-sidebar-active);
        }
        
        .menu-group {
            margin-bottom: 5px;
        }
        
        .menu-group > a.active + .submenu {
            border-left-color: var(--primary-color);
        }
        
        .sidebar a:hover {
            background-color: var(--bg-sidebar-hover);
            color: var(--text-sidebar-active);
            transform: translateX(4px);
        }
        
        .sidebar .active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            color: var(--text-sidebar-active);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
            position: relative;
        }
        
        .sidebar i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .sidebar a:hover i {
            transform: scale(1.1);
        }
        
        .content {
            padding: 30px;
            margin-left: var(--sidebar-width);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card {
            margin-bottom: 24px;
            border-radius: 12px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.03);
            overflow: hidden;
            background: #ffffff;
        }
        
        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 16px 20px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .gauge-container {
            width: 200px;
            height: 200px;
            margin: 0 auto;
            position: relative;
        }
        
        .user-info {
            color: #ffffff;
            padding: 20px 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            text-align: center;
        }
        
        .user-info img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin-bottom: 12px;
            border: 2px solid rgba(255,255,255,0.1);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .user-info h5 {
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 1.05rem;
            color: #ffffff;
            letter-spacing: 0.3px;
        }
        
        .user-info .badge {
            font-size: 0.78rem;
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .logout-btn {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        
        /* Mobile styles */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background-color: var(--bg-sidebar);
            color: white;
            z-index: 1020;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        #mobile-menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s;
        }

        #mobile-menu-toggle:active {
            opacity: 0.7;
        }

        /* Overlay for mobile menu */
        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1025;
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .sidebar {
                width: 280px;
                left: -280px;
                box-shadow: none;
            }
            
            .sidebar.active {
                left: 0;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.25);
            }
            
            .content {
                margin-left: 0;
                padding: 20px;
                padding-top: 80px;
            }
            
            .mobile-header {
                display: flex;
            }
            
            .page-title-mobile {
                text-align: center;
                margin-bottom: 20px;
            }
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

        /* Logout button styles */
        .sidebar .btn-danger {
            margin: 10px;
            border-radius: 8px;
            font-weight: 600;
            padding: 12px 15px;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
            transition: all 0.3s ease;
        }
        
        .sidebar .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }
        
        .sidebar .btn-danger:active {
            transform: translateY(0);
        }
        
        @media (max-width: 991px) {
            .sidebar .btn-danger {
                margin: 15px 10px;
            }
        }
    </style>
</head>
<body class="<?php echo isset($_SESSION['custom_body_class']) ? $_SESSION['custom_body_class'] : ''; ?>">
    <!-- Mobile top bar -->
    <div class="mobile-header">
        <button id="mobile-menu-toggle" type="button" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        <span class="fw-bold" style="font-size: 1.15rem; letter-spacing: 0.5px;">Sistema PDV</span>
        <div style="width: 34px;"></div>
    </div>
    
    <!-- Overlay for mobile menu -->
    <div class="menu-overlay"></div>
    
    <div class="container-fluid p-0">
        <div class="d-flex">
            <div class="sidebar d-flex flex-column">
                <div class="user-info">
                    <img src="<?php echo $base_path; ?>logo.jpeg" alt="Logo" class="img-fluid" style="width: 64px; height: 64px; object-fit: cover;">
                    <h5><?php echo $_SESSION['usuario_nome']; ?></h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                        <?php if ($_SESSION['nivel'] === 'administrador'): ?>
                            <span class="badge bg-danger">Administrador</span>
                        <?php else: ?>
                            <span class="badge bg-success">Caixa <?php echo $_SESSION['caixa_numero']; ?></span>
                        <?php endif; ?>
                        <a href="<?php echo $base_path; ?>logout.php" class="btn btn-sm btn-outline-danger border-0 p-1" title="Sair do Sistema" style="line-height: 1; display: inline-flex; align-items: center; justify-content: center; min-height: auto; width: auto; margin: 0; background: none;">
                            <i class="fas fa-sign-out-alt" style="margin: 0 !important; font-size: 1.05rem; color: #ef4444;"></i>
                        </a>
                    </div>
                </div>
                
                <?php if ($_SESSION['nivel'] === 'administrador'): ?>
                    <!-- Menu de Administrador -->
                    <a href="<?php echo $base_path; ?>index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    
                    <!-- Menu Produtos com Submenu -->
                    <div class="menu-group">
                        <a href="<?php echo $base_path; ?>produtos.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['produtos.php', 'adicionar_produto.php', 'editar_produto.php', 'categorias.php', 'editar_categoria.php']) ? 'active' : ''; ?> menu-item">
                            <i class="fas fa-box"></i> Produtos
                        </a>
                        <div class="submenu" style="padding-left: 20px;">
                            <a href="<?php echo $base_path; ?>produtos/categorias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categorias.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-tags"></i> Categorias
                            </a>
                            <a href="<?php echo $base_path; ?>produtos/gerenciar_permissoes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gerenciar_permissoes.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-key"></i> Permissões
                            </a>
                        </div>
                    </div>

                    <a href="<?php echo $base_path; ?>vender.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'vender.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-shopping-cart"></i> Vender
                    </a>
                    <a href="<?php echo $base_path; ?>lista_vendas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'lista_vendas.php' || basename($_SERVER['PHP_SELF']) == 'detalhes_venda.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-receipt"></i> Vendas
                    </a>
                    
                    <!-- Menu de Caixa com Submenu -->
                    <div class="menu-group">
                        <a href="<?php echo $base_path; ?>controle_caixa.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['controle_caixa.php', 'gestao_caixas.php']) ? 'active' : ''; ?> menu-item">
                            <i class="fas fa-cash-register"></i> Caixa
                        </a>
                        <div class="submenu" style="padding-left: 20px;">
                            <a href="<?php echo $base_path; ?>gestao_caixas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gestao_caixas.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-history"></i> Histórico
                            </a>
                            <a href="<?php echo $base_path; ?>sangrias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sangrias.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-money-bill-wave"></i> Sangrias
                            </a>
                        </div>
                    </div>

                    <a href="<?php echo $base_path; ?>usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' || basename($_SERVER['PHP_SELF']) == 'adicionar_usuario.php' || basename($_SERVER['PHP_SELF']) == 'editar_usuario.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-users"></i> Usuários
                    </a>
                <?php else: ?>
                    <!-- Menu de Operador de Caixa -->
                    <a href="<?php echo $base_path; ?>vender.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'vender.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-shopping-cart"></i> Vender
                    </a>
                    <a href="<?php echo $base_path; ?>lista_vendas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'lista_vendas.php' || basename($_SERVER['PHP_SELF']) == 'detalhes_venda.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-receipt"></i> Minhas Vendas
                    </a>
                    
                    <!-- Menu de Caixa com Submenu -->
                    <div class="menu-group">
                        <a href="<?php echo $base_path; ?>controle_caixa.php" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['controle_caixa.php', 'gestao_caixas.php']) ? 'active' : ''; ?> menu-item">
                            <i class="fas fa-cash-register"></i> Meu Caixa
                        </a>
                        <div class="submenu" style="padding-left: 20px;">
                            <a href="<?php echo $base_path; ?>gestao_caixas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gestao_caixas.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-history"></i> Histórico
                            </a>
                            <a href="<?php echo $base_path; ?>sangrias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sangrias.php' ? 'active' : ''; ?> menu-item">
                                <i class="fas fa-money-bill-wave"></i> Sangrias
                            </a>
                        </div>
                    </div>

                    <a href="<?php echo $base_path; ?>usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' || basename($_SERVER['PHP_SELF']) == 'adicionar_usuario.php' || basename($_SERVER['PHP_SELF']) == 'editar_usuario.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-users"></i> Usuários
                    </a>
                <?php endif; ?>
            </div>
            <div class="content flex-grow-1">
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