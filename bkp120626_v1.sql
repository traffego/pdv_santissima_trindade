-- --------------------------------------------------------
-- Servidor:                     187.33.241.40
-- Versão do servidor:           10.11.11-MariaDB-cll-lve - MariaDB Server
-- OS do Servidor:               Linux
-- HeidiSQL Versão:              12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Copiando estrutura para tabela platafo5_pdv.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela platafo5_pdv.categorias: ~9 rows (aproximadamente)
REPLACE INTO `categorias` (`id`, `nome`) VALUES
	(11, 'Batata'),
	(1, 'Bebidas'),
	(13, 'Brincadeira'),
	(14, 'Cachorro Quente'),
	(12, 'Caixa Completo'),
	(3, 'Caldos'),
	(5, 'Chocolate'),
	(2, 'Churrasco'),
	(10, 'Doces'),
	(4, 'Pasteis');

-- Copiando estrutura para tabela platafo5_pdv.controle_caixa
CREATE TABLE IF NOT EXISTS `controle_caixa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `caixa_numero` int(11) NOT NULL,
  `data_abertura` datetime NOT NULL,
  `data_fechamento` datetime DEFAULT NULL,
  `valor_inicial` decimal(10,2) NOT NULL,
  `valor_final` decimal(10,2) DEFAULT NULL,
  `valor_sangrias` decimal(10,2) DEFAULT 0.00,
  `valor_vendas` decimal(10,2) DEFAULT 0.00,
  `valor_vendas_dinheiro` decimal(10,2) DEFAULT 0.00,
  `valor_vendas_pix` decimal(10,2) DEFAULT 0.00,
  `valor_vendas_cartao` decimal(10,2) DEFAULT 0.00,
  `status` enum('aberto','fechado') NOT NULL DEFAULT 'aberto',
  `observacoes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_controle_caixa_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela platafo5_pdv.controle_caixa: ~18 rows (aproximadamente)
REPLACE INTO `controle_caixa` (`id`, `usuario_id`, `caixa_numero`, `data_abertura`, `data_fechamento`, `valor_inicial`, `valor_final`, `valor_sangrias`, `valor_vendas`, `valor_vendas_dinheiro`, `valor_vendas_pix`, `valor_vendas_cartao`, `status`, `observacoes`) VALUES
	(1, 4, 999, '2025-05-29 11:46:07', NULL, 0.00, NULL, 0.00, 15.00, 0.00, 15.00, 0.00, 'aberto', ''),
	(2, 4, 20, '2025-05-31 16:55:47', '2025-06-11 11:16:33', 0.00, 113.00, 0.00, 269.00, 113.00, 156.00, 0.00, 'fechado', 'Abertura\n\nObservações do fechamento: '),
	(3, 9, 10, '2025-05-31 17:11:03', NULL, 500.00, NULL, 0.00, 15.00, 0.00, 15.00, 0.00, 'aberto', ''),
	(4, 4, 20, '2025-06-11 11:50:31', '2025-06-11 13:30:05', 1.00, 1.00, 0.00, 37.00, 0.00, 0.00, 0.00, 'fechado', '\n\nObservações do fechamento: '),
	(5, 13, 12, '2025-06-11 13:26:22', '2025-06-11 13:42:08', 1.00, 0.00, 0.00, 10.00, 0.00, 0.00, 0.00, 'fechado', '\n\nObservações do fechamento: '),
	(6, 7, 7, '2025-06-11 13:40:46', '2025-06-11 14:02:40', 1.00, 21.00, 0.00, 20.00, 0.00, 0.00, 0.00, 'fechado', 'Abertura de Caixa 11/06/2025 ás 13:39\n\nObservações do fechamento: Fechamento ás 14:01'),
	(7, 13, 12, '2025-06-11 13:44:30', '2025-06-11 15:50:36', 0.00, 10.00, 0.00, 60.00, 0.00, 10.00, 40.00, 'fechado', '\n\nObservações do fechamento: '),
	(8, 13, 12, '2025-06-11 15:55:59', NULL, 0.00, NULL, 0.00, 30.00, 0.00, 0.00, 0.00, 'aberto', ''),
	(9, 4, 20, '2025-06-11 16:08:41', NULL, 0.00, NULL, 0.00, 95.00, 0.00, 0.00, 0.00, 'aberto', ''),
	(10, 7, 7, '2025-06-11 20:02:54', NULL, 10.00, NULL, 0.00, 22.00, 0.00, 0.00, 0.00, 'aberto', 'Teste 20:02 '),
	(11, 5, 9, '2025-06-11 20:06:57', NULL, 10.00, NULL, 0.00, 10.00, 0.00, 0.00, 0.00, 'aberto', ''),
	(12, 3, 5, '2025-06-11 20:22:37', NULL, 10.50, NULL, 0.00, 60.00, 0.00, 0.00, 0.00, 'aberto', ''),
	(13, 6, 4, '2025-06-11 20:24:22', NULL, 50.00, NULL, 0.00, 301.00, 0.00, 0.00, 0.00, 'aberto', ''),
	(14, 1, 1, '2025-06-11 20:24:49', NULL, 10.00, NULL, 0.00, 110.00, 0.00, 0.00, 0.00, 'aberto', ''),
	(15, 10, 11, '2025-06-11 20:26:20', NULL, 10.00, NULL, 0.00, 5.00, 0.00, 0.00, 0.00, 'aberto', ''),
	(16, 2, 2, '2025-06-11 20:27:37', NULL, 10.00, NULL, 0.00, 32.00, 0.00, 0.00, 0.00, 'aberto', 'Thiago'),
	(17, 8, 8, '2025-06-11 20:27:42', NULL, 10.00, NULL, 0.00, 28.00, 0.00, 0.00, 0.00, 'aberto', ''),
	(18, 12, 6, '2025-06-11 20:38:34', NULL, 0.00, NULL, 0.00, 30.00, 0.00, 0.00, 0.00, 'aberto', '');

-- Copiando estrutura para tabela platafo5_pdv.itens_venda
CREATE TABLE IF NOT EXISTS `itens_venda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) DEFAULT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `preco_unitario` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `venda_id` (`venda_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `itens_venda_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`),
  CONSTRAINT `itens_venda_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela platafo5_pdv.itens_venda: ~71 rows (aproximadamente)
REPLACE INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(147, 89, 14, 1, 10.00);

-- Copiando estrutura para tabela platafo5_pdv.permissoes_categorias
CREATE TABLE IF NOT EXISTS `permissoes_categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permissao` (`usuario_id`,`categoria`),
  CONSTRAINT `permissoes_categorias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela platafo5_pdv.permissoes_categorias: ~63 rows (aproximadamente)
REPLACE INTO `permissoes_categorias` (`id`, `usuario_id`, `categoria`) VALUES
	(21, 1, 'Batata'),
	(22, 1, 'Bebidas'),
	(23, 1, 'Brincadeira'),
	(24, 1, 'Cachorro Quente'),
	(25, 1, 'Caixa Completo'),
	(26, 1, 'Caldos'),
	(27, 1, 'Chocolate'),
	(28, 1, 'Churrasco'),
	(29, 1, 'Doces'),
	(30, 1, 'Pasteis'),
	(31, 2, 'Batata'),
	(32, 2, 'Bebidas'),
	(33, 2, 'Brincadeira'),
	(34, 2, 'Cachorro Quente'),
	(35, 2, 'Caixa Completo'),
	(36, 2, 'Caldos'),
	(37, 2, 'Chocolate'),
	(38, 2, 'Churrasco'),
	(39, 2, 'Doces'),
	(40, 2, 'Pasteis'),
	(19, 3, 'Churrasco'),
	(1, 4, 'Bebidas'),
	(2, 4, 'Caldos'),
	(3, 4, 'Chocolate'),
	(4, 4, 'Churrasco'),
	(5, 4, 'Fritos'),
	(10, 5, 'Caldos'),
	(11, 6, 'Batata'),
	(52, 7, 'Bebidas'),
	(53, 8, 'Cachorro Quente'),
	(54, 10, 'Brincadeira'),
	(41, 11, 'Batata'),
	(42, 11, 'Bebidas'),
	(43, 11, 'Brincadeira'),
	(44, 11, 'Cachorro Quente'),
	(45, 11, 'Caixa Completo'),
	(46, 11, 'Caldos'),
	(47, 11, 'Chocolate'),
	(48, 11, 'Churrasco'),
	(49, 11, 'Doces'),
	(50, 11, 'Pasteis'),
	(51, 12, 'Doces'),
	(55, 13, 'Pasteis'),
	(56, 14, 'Batata'),
	(57, 14, 'Bebidas'),
	(58, 14, 'Brincadeira'),
	(59, 14, 'Cachorro Quente'),
	(60, 14, 'Caixa Completo'),
	(61, 14, 'Caldos'),
	(62, 14, 'Chocolate'),
	(63, 14, 'Churrasco'),
	(64, 14, 'Doces'),
	(65, 14, 'Pasteis'),
	(66, 15, 'Batata'),
	(67, 15, 'Bebidas'),
	(68, 15, 'Brincadeira'),
	(69, 15, 'Cachorro Quente'),
	(70, 15, 'Caixa Completo'),
	(71, 15, 'Caldos'),
	(72, 15, 'Chocolate'),
	(73, 15, 'Churrasco'),
	(74, 15, 'Doces'),
	(75, 15, 'Pasteis');

-- Copiando estrutura para tabela platafo5_pdv.produtos
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT NULL,
  `quantidade_estoque` int(11) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `cor` varchar(7) DEFAULT '#eeeeee',
  PRIMARY KEY (`id`),
  KEY `fk_produto_categoria` (`categoria_id`),
  CONSTRAINT `fk_produto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela platafo5_pdv.produtos: ~23 rows (aproximadamente)
REPLACE INTO `produtos` (`id`, `nome`, `preco`, `quantidade_estoque`, `categoria_id`, `cor`) VALUES
	(12, 'Coca Cola', 6.00, 495, 1, '#2be212'),
	(14, 'Pastel', 10.00, 19, 4, '#750b0b'),
	(15, 'Batata', 7.00, 444, 11, '#0a0a0a'),
	(16, 'Doce', 6.00, 92, 10, '#2be212'),
	(17, 'Água S/Gás', 2.00, 492, 1, '#fff705'),
	(18, 'Água C/Gás', 3.00, 180, 1, '#f7f7f7'),
	(19, 'Guaraná Antarctica', 6.00, 500, 1, '#2be212'),
	(20, 'Guaramor', 2.00, 299, 1, '#f8f00d'),
	(21, 'Cachorro Quente', 7.00, 195, 14, '#050505'),
	(22, 'Caldo Verde', 10.00, 67, 3, '#750b0b'),
	(23, 'Brincadeira', 5.00, 995, 13, '#3329bc'),
	(24, 'Churrasquinho', 10.00, 140, 2, '#750b0b'),
	(25, 'Pula - Pula', 5.00, 1000, 13, '#203fd9'),
	(26, 'Tobogã', 5.00, 1000, 13, '#2719e6'),
	(27, 'Suco Lata', 7.00, 299, 1, '#000000'),
	(28, 'Vinho Copo de 300ml', 7.00, 200, 1, '#050505'),
	(29, 'Cerveja Brama', 8.00, 495, 1, '#e6a20f'),
	(30, 'Cerveja Antártica', 8.00, 492, 1, '#eea811'),
	(31, 'Cerveja Heneken', 10.00, 295, 1, '#750b0b'),
	(32, 'Mocotó', 12.00, 199, 3, '#f00fc7'),
	(33, 'Coca Cola Zero', 6.00, 98, 1, '#2be212'),
	(34, 'Fanta Uva', 6.00, 100, 1, '#2be212'),
	(35, 'Fanta Laranja', 6.00, 98, 1, '#2be212'),
	(36, 'Sprite', 6.00, 100, 1, '#2be212');

-- Copiando estrutura para tabela platafo5_pdv.sangrias
CREATE TABLE IF NOT EXISTS `sangrias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valor` decimal(10,2) DEFAULT NULL,
  `data` datetime DEFAULT current_timestamp(),
  `observacao` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `controle_caixa_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sangrias_usuarios` (`usuario_id`),
  KEY `fk_sangrias_controle_caixa` (`controle_caixa_id`),
  CONSTRAINT `fk_sangrias_controle_caixa` FOREIGN KEY (`controle_caixa_id`) REFERENCES `controle_caixa` (`id`),
  CONSTRAINT `fk_sangrias_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela platafo5_pdv.sangrias: ~9 rows (aproximadamente)
REPLACE INTO `sangrias` (`id`, `valor`, `data`, `observacao`, `usuario_id`, `controle_caixa_id`) VALUES
	(1, 171.00, '2025-05-29 02:08:37', '', 4, NULL),
	(2, 10.00, '2025-06-11 13:51:44', 'Thiago ás 13:51', 7, NULL),
	(3, 10.00, '2025-06-11 20:29:24', '', 6, NULL),
	(4, 20.00, '2025-06-11 20:45:43', '20h45', 3, NULL),
	(5, 500.00, '2025-06-11 20:46:17', 'Roberto levou 500', 1, NULL),
	(6, 100.00, '2025-06-11 20:46:36', '', 10, NULL),
	(7, 7.00, '2025-06-11 20:47:33', '', 6, NULL),
	(8, 300.00, '2025-06-11 22:30:19', '', 12, NULL),
	(9, 200.00, '2025-06-11 22:31:31', 'André retirou o valor as 21h', 12, NULL),
	(10, 100.00, '2025-06-12 09:12:47', '', 10, NULL);

-- Copiando estrutura para tabela platafo5_pdv.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `caixa_numero` int(11) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `ultimo_login` datetime DEFAULT NULL,
  `nivel` varchar(20) NOT NULL DEFAULT 'operador' COMMENT 'administrador ou operador',
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela platafo5_pdv.usuarios: ~16 rows (aproximadamente)
REPLACE INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `caixa_numero`, `ativo`, `ultimo_login`, `nivel`) VALUES
	(1, 'Operador Caixa 1', 'caixa1', '$2a$10$2QWKoRfDHUoEbjCOBw4caeJ47kdhzpUKiR.SIofdMH2xsTzc22mMu', 1, 1, '2025-06-12 08:37:22', 'operador'),
	(2, 'Operador Caixa 2', 'caixa2', '$2y$10$J87fyu38fQViaVAIi0jZyeAozYK8GiD5ke8eRvKorqFEoQHROPVAa', 2, 1, '2025-06-12 10:03:18', 'operador'),
	(3, 'Caixa Churrasquinho', 'Churrasquinho', '$2y$10$YjbSXQfcUydrHsT9OleL2.J2WxsjhhVEmbsiIYxQpUEiCefVP3.Y2', 5, 1, '2025-06-12 08:23:04', 'operador'),
	(4, 'Thiago', 'admin', '$2y$10$C1RKT466Oeo3OieA7LvAIeQpZ/9xBQuLKU/.uLQZADSUKJNLEhbOW', 20, 1, '2025-06-12 14:12:10', 'administrador'),
	(5, 'Caixa Caldos', 'Caldos', '$2y$10$p/cEcDsICoCXCzCGYEYhv.r5TBlX9cwMZSo9Eoj7nvLOmhq50yHCi', 9, 1, '2025-06-11 20:04:08', 'operador'),
	(6, 'Caixa Batata', 'Batata', '$2y$10$xv7ZYg0LJHOAhe4dmRuXKu8RdAH5Hl3Ax5EPHN/EhzThVI6yFGoYm', 4, 1, '2025-06-11 22:16:29', 'operador'),
	(7, 'Caixa Bebidas', 'Bebidas', '$2y$10$1NKUXJ0.ZvQF0.9uT3ZAj.ALlQeKx44aGQ.JweKTYdBV2jxAOvWse', 7, 1, '2025-06-11 20:24:41', 'operador'),
	(8, 'Caixa Cachorro Quente', 'Cachorro Quente', '$2y$10$KMj5ttscBMmaP/bIVRX9luWX31eiwP.XnlIBsL8orh2.4qnbhsMva', 8, 1, '2025-06-11 21:00:29', 'operador'),
	(9, 'Padre Adriano', 'Padre Adriano', '$2y$10$hNNj/E0BUKe1u3l9RbjDJuWF7gsJ1RWvJhG/yGBiEyprbiC0qa8Ii', 10, 1, '2025-05-31 17:09:48', 'administrador'),
	(10, 'Caixa Brincadeira', 'Brincadeira', '$2y$10$et3H9D9uYlnbtQ1NY5LYIu4RZoVWcixW3JKteMvnH5dGhEQEhcfBW', 11, 1, '2025-06-12 11:27:41', 'operador'),
	(11, 'Operador de Caixa 3', 'caixa3', '$2y$10$AscxL/eF5DzyC8bIQBGyXey/ufU0zr0WIi0AZYbtgAGP6nCA/Yoai', 3, 1, NULL, 'operador'),
	(12, 'Caixa Doces', 'Doces', '$2y$10$ucqkA.j19XK0Vu60y7iPQ.VfbEiFsq2LqIeSOgWmVxtNnm80zPWpO', 6, 1, '2025-06-11 21:54:38', 'operador'),
	(13, 'Caixa Pastel', 'Pastel', '$2y$10$ikqylHYRqFgYYvDdsowSSeoIx5Dav9CQy1MXQg2wYFqbpf/.imE1y', 12, 1, '2025-06-12 14:10:03', 'operador'),
	(14, 'Operador de caixa 4', 'caixa4', '$2y$10$3rnGUHjGRoGR7abdJNk1DeSZyfCZ0ZrD7c6/bCz6zViZuVjXwakDG', 13, 1, NULL, 'operador'),
	(15, 'Operador de caixa 5', 'caixa5', '$2y$10$40/rkudD4L2FwMyOouMZH.PJpifmGrwQDv6k9aMVyughUc.pMagH.', 13, 1, NULL, 'operador'),
	(16, 'Otávio', 'Otavio', '$2y$10$x2h0c6pJCheiHvIiY47avuCCqd.hAJiIf3Pk59GCb1XHPJbINKuzC', 14, 1, '2025-05-31 22:23:48', 'administrador');

-- Copiando estrutura para tabela platafo5_pdv.vendas
CREATE TABLE IF NOT EXISTS `vendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` datetime DEFAULT current_timestamp(),
  `valor_total` decimal(10,2) DEFAULT NULL,
  `forma_pagamento` enum('Dinheiro','Pix','Cartão') DEFAULT NULL,
  `caixa` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `controle_caixa_id` int(11) DEFAULT NULL,
  `data_venda` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_vendas_usuarios` (`usuario_id`),
  KEY `idx_data_hora` (`data_hora`),
  KEY `fk_vendas_controle_caixa` (`controle_caixa_id`),
  CONSTRAINT `fk_vendas_controle_caixa` FOREIGN KEY (`controle_caixa_id`) REFERENCES `controle_caixa` (`id`),
  CONSTRAINT `fk_vendas_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela platafo5_pdv.vendas: ~52 rows (aproximadamente)
REPLACE INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`, `controle_caixa_id`, `data_venda`) VALUES
	(89, '2025-06-12 14:21:20', 10.00, 'Dinheiro', 12, 13, '2025-06-12 14:21:20', 8, '2025-06-12 14:21:20');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
