-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.4.32-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
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

-- Copiando estrutura para tabela pdv.itens_venda
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.itens_venda: ~18 rows (aproximadamente)
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(1, 1, 1, 1, 7.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(2, 2, 2, 1, 5.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(3, 3, 2, 2, 5.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(4, 3, 1, 1, 7.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(5, 4, 5, 1, 10.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(6, 5, 2, 1, 3.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(7, 5, 6, 1, 7.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(8, 6, 2, 1, 3.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(9, 7, 2, 1, 3.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(10, 7, 10, 1, 7.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(11, 8, 2, 1, 3.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(12, 9, 10, 1, 7.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(13, 10, 2, 1, 3.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(14, 10, 9, 1, 5.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(15, 11, 2, 1, 3.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(16, 12, 2, 1, 3.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(17, 13, 1, 1, 5.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(18, 14, 1, 1, 5.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(19, 15, 2, 1, 3.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(20, 16, 5, 1, 10.00);
INSERT INTO `itens_venda` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(21, 16, 2, 1, 3.00);

-- Copiando estrutura para tabela pdv.produtos
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `quantidade_estoque` int(11) DEFAULT NULL,
  `codigo_barras` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_codigo_barras` (`codigo_barras`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.produtos: ~6 rows (aproximadamente)
INSERT INTO `produtos` (`id`, `nome`, `preco`, `categoria`, `quantidade_estoque`, `codigo_barras`) VALUES
	(1, 'Coca Cola', 5.00, 'Bebidas', 598, NULL);
INSERT INTO `produtos` (`id`, `nome`, `preco`, `categoria`, `quantidade_estoque`, `codigo_barras`) VALUES
	(2, 'Água', 3.00, 'Bebidas', 291, NULL);
INSERT INTO `produtos` (`id`, `nome`, `preco`, `categoria`, `quantidade_estoque`, `codigo_barras`) VALUES
	(5, 'Churrasco', 10.00, 'Churrasco', 118, NULL);
INSERT INTO `produtos` (`id`, `nome`, `preco`, `categoria`, `quantidade_estoque`, `codigo_barras`) VALUES
	(6, 'Caldo', 7.00, 'Caldos', 149, NULL);
INSERT INTO `produtos` (`id`, `nome`, `preco`, `categoria`, `quantidade_estoque`, `codigo_barras`) VALUES
	(9, 'Pastel', 5.00, 'Fritos', 199, NULL);
INSERT INTO `produtos` (`id`, `nome`, `preco`, `categoria`, `quantidade_estoque`, `codigo_barras`) VALUES
	(10, 'Chocolate', 7.00, 'Chocolate', 0, NULL);

-- Copiando estrutura para tabela pdv.sangrias
CREATE TABLE IF NOT EXISTS `sangrias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valor` decimal(10,2) DEFAULT NULL,
  `data` datetime DEFAULT current_timestamp(),
  `observacao` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sangrias_usuarios` (`usuario_id`),
  CONSTRAINT `fk_sangrias_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.sangrias: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela pdv.usuarios
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.usuarios: ~4 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `caixa_numero`, `ativo`, `ultimo_login`, `nivel`) VALUES
	(1, 'Operador Caixa 1', 'caixa1', '$2a$10$2QWKoRfDHUoEbjCOBw4caeJ47kdhzpUKiR.SIofdMH2xsTzc22mMu', 1, 1, '2025-05-23 23:37:19', 'operador');
INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `caixa_numero`, `ativo`, `ultimo_login`, `nivel`) VALUES
	(2, 'Operador Caixa 2', 'caixa2', '$2a$10$2QWKoRfDHUoEbjCOBw4caeJ47kdhzpUKiR.SIofdMH2xsTzc22mMu', 2, 1, NULL, 'operador');
INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `caixa_numero`, `ativo`, `ultimo_login`, `nivel`) VALUES
	(3, 'Operador Caixa 3', 'caixa3', '$2a$10$2QWKoRfDHUoEbjCOBw4caeJ47kdhzpUKiR.SIofdMH2xsTzc22mMu', 3, 1, NULL, 'operador');
INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `caixa_numero`, `ativo`, `ultimo_login`, `nivel`) VALUES
	(4, 'Administrador', 'admin', '$2a$10$2QWKoRfDHUoEbjCOBw4caeJ47kdhzpUKiR.SIofdMH2xsTzc22mMu', 0, 1, '2025-05-23 20:03:23', 'administrador');

-- Copiando estrutura para tabela pdv.vendas
CREATE TABLE IF NOT EXISTS `vendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` datetime DEFAULT current_timestamp(),
  `valor_total` decimal(10,2) DEFAULT NULL,
  `forma_pagamento` enum('Dinheiro','Pix','Cartão') DEFAULT NULL,
  `caixa` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_hora` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_vendas_usuarios` (`usuario_id`),
  KEY `idx_data_hora` (`data_hora`),
  CONSTRAINT `fk_vendas_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.vendas: ~14 rows (aproximadamente)
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(1, '2025-05-23 16:50:04', 7.00, 'Pix', 1, 4, '2025-05-23 19:52:05');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(2, '2025-05-23 16:53:29', 5.00, 'Dinheiro', 1, 4, '2025-05-23 19:52:05');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(3, '2025-05-23 17:01:55', 17.00, 'Pix', 1, 4, '2025-05-23 19:52:05');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(4, '2025-05-23 18:14:51', 10.00, 'Pix', 1, 1, '2025-05-23 19:52:05');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(5, '2025-05-23 18:46:47', 10.00, 'Pix', 1, 1, '2025-05-23 19:52:05');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(6, '2025-05-23 18:52:32', 3.00, 'Pix', 1, 1, '2025-05-23 19:52:05');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(7, '2025-05-23 18:53:41', 10.00, 'Pix', 1, 1, '2025-05-23 19:52:05');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(8, '2025-05-23 19:10:24', 3.00, 'Pix', 1, 1, '2025-05-23 19:52:05');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(9, '2025-05-23 19:10:36', 7.00, 'Dinheiro', 1, 1, '2025-05-23 19:52:05');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(10, '2025-05-23 19:14:10', 8.00, 'Cartão', 1, 1, '2025-05-23 20:00:51');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(11, '2025-05-23 19:17:24', 3.00, 'Pix', 1, 1, '2025-05-23 20:00:51');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(12, '2025-05-23 19:18:20', 3.00, 'Pix', 1, 1, '2025-05-23 20:00:51');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(13, '2025-05-23 19:21:56', 5.00, 'Pix', 999, 4, '2025-05-23 20:00:51');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(14, '2025-05-23 19:59:00', 5.00, 'Pix', 999, 4, '2025-05-23 20:00:51');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(15, '2025-05-23 20:03:45', 3.00, 'Pix', 999, 4, '2025-05-23 20:03:45');
INSERT INTO `vendas` (`id`, `data`, `valor_total`, `forma_pagamento`, `caixa`, `usuario_id`, `data_hora`) VALUES
	(16, '2025-05-23 23:10:08', 13.00, 'Pix', 1, 1, '2025-05-23 23:10:08');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
