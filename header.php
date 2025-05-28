<?php require_once 'check_login.php'; ?>

<?php date_default_timezone_set('America/Sao_Paulo'); ?>
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
    </style>
</head>
<body>
    <!-- Mobile menu toggle button -->
    <button id="mobile-menu-toggle" type="button" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Overlay for mobile menu -->
    <div class="menu-overlay"></div>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar d-flex flex-column">
                <div class="text-center mb-3 mt-2">
                    <img src="logo.jpeg" alt="Logo" class="img-fluid" style="max-width: 80%;">
                </div>
                <div class="user-info">
                    <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['usuario_nome']; ?>&background=random" alt="User Avatar">
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
                    <a href="produtos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'produtos.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-box"></i> Produtos
                    </a>
                    <a href="vender.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'vender.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-shopping-cart"></i> Vender
                    </a>
                    <a href="lista_vendas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'lista_vendas.php' || basename($_SERVER['PHP_SELF']) == 'detalhes_venda.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-receipt"></i> Vendas
                    </a>
                    <a href="sangrias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sangrias.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-money-bill-wave"></i> Sangrias
                    </a>
                    <a href="usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' || basename($_SERVER['PHP_SELF']) == 'adicionar_usuario.php' || basename($_SERVER['PHP_SELF']) == 'editar_usuario.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-users"></i> Usuários
                    </a>
                <?php else: ?>
                    <!-- Menu de Operador de Caixa -->
                    <a href="vender.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'vender.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-shopping-cart"></i> Vender
                    </a>
                    <a href="lista_vendas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'lista_vendas.php' || basename($_SERVER['PHP_SELF']) == 'detalhes_venda.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-receipt"></i> Minhas Vendas
                    </a>
                    <a href="sangrias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sangrias.php' ? 'active' : ''; ?> menu-item">
                        <i class="fas fa-money-bill-wave"></i> Sangrias
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