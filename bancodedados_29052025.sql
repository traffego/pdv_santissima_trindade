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

-- Copiando estrutura para tabela pdv.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.categorias: ~5 rows (aproximadamente)
INSERT INTO `categorias` (`id`, `nome`) VALUES
	(1, 'Bebidas');
INSERT INTO `categorias` (`id`, `nome`) VALUES
	(3, 'Caldos');
INSERT INTO `categorias` (`id`, `nome`) VALUES
	(5, 'Chocolate');
INSERT INTO `categorias` (`id`, `nome`) VALUES
	(2, 'Churrasco');
INSERT INTO `categorias` (`id`, `nome`) VALUES
	(4, 'Pasteis');

-- Copiando estrutura para tabela pdv.controle_caixa
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.controle_caixa: ~0 rows (aproximadamente)

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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.itens_venda: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela pdv.permissoes_categorias
CREATE TABLE IF NOT EXISTS `permissoes_categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permissao` (`usuario_id`,`categoria`),
  CONSTRAINT `permissoes_categorias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.permissoes_categorias: ~5 rows (aproximadamente)
INSERT INTO `permissoes_categorias` (`id`, `usuario_id`, `categoria`) VALUES
	(1, 4, 'Bebidas');
INSERT INTO `permissoes_categorias` (`id`, `usuario_id`, `categoria`) VALUES
	(2, 4, 'Caldos');
INSERT INTO `permissoes_categorias` (`id`, `usuario_id`, `categoria`) VALUES
	(3, 4, 'Chocolate');
INSERT INTO `permissoes_categorias` (`id`, `usuario_id`, `categoria`) VALUES
	(4, 4, 'Churrasco');
INSERT INTO `permissoes_categorias` (`id`, `usuario_id`, `categoria`) VALUES
	(5, 4, 'Fritos');

-- Copiando estrutura para tabela pdv.produtos
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT NULL,
  `quantidade_estoque` int(11) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_produto_categoria` (`categoria_id`),
  CONSTRAINT `fk_produto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.produtos: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela pdv.sangrias
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.sangrias: ~1 rows (aproximadamente)
INSERT INTO `sangrias` (`id`, `valor`, `data`, `observacao`, `usuario_id`, `controle_caixa_id`) VALUES
	(1, 171.00, '2025-05-29 02:08:37', '', 4, NULL);

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

-- Copiando dados para a tabela pdv.usuarios: ~3 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `caixa_numero`, `ativo`, `ultimo_login`, `nivel`) VALUES
	(1, 'Operador Caixa 1', 'caixa1', '$2a$10$2QWKoRfDHUoEbjCOBw4caeJ47kdhzpUKiR.SIofdMH2xsTzc22mMu', 1, 1, '2025-05-29 02:28:18', 'operador');
INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `caixa_numero`, `ativo`, `ultimo_login`, `nivel`) VALUES
	(2, 'Operador Caixa 2', 'caixa2', '$2a$10$2QWKoRfDHUoEbjCOBw4caeJ47kdhzpUKiR.SIofdMH2xsTzc22mMu', 2, 1, NULL, 'operador');
INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `caixa_numero`, `ativo`, `ultimo_login`, `nivel`) VALUES
	(3, 'Operador Caixa 3', 'caixa3', '$2a$10$2QWKoRfDHUoEbjCOBw4caeJ47kdhzpUKiR.SIofdMH2xsTzc22mMu', 3, 1, NULL, 'operador');
INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `caixa_numero`, `ativo`, `ultimo_login`, `nivel`) VALUES
	(4, 'Administrador', 'admin', '$2a$10$2QWKoRfDHUoEbjCOBw4caeJ47kdhzpUKiR.SIofdMH2xsTzc22mMu', 0, 1, '2025-05-29 10:05:22', 'administrador');

-- Copiando estrutura para tabela pdv.vendas
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela pdv.vendas: ~0 rows (aproximadamente)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
