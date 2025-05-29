-- Create database if not exists
CREATE DATABASE IF NOT EXISTS pdv;

-- Use the database
USE pdv;

-- Users table for cashiers
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  caixa_numero INT NOT NULL,
  nivel VARCHAR(20) NOT NULL DEFAULT 'operador', -- 'administrador' ou 'operador'
  ativo BOOLEAN DEFAULT TRUE,
  ultimo_login DATETIME
);

-- Insert default users
INSERT INTO usuarios (nome, usuario, senha, caixa_numero, nivel) VALUES 
('Administrador', 'admin', '$2y$10$USkN1e57.NsuXoM7SQbk3O49JWNafGc2bV7vkW9QrYkDOS/aMy2oy', 0, 'administrador'), -- senha: admin
('Operador Caixa 1', 'caixa1', '$2y$10$USkN1e57.NsuXoM7SQbk3O49JWNafGc2bV7vkW9QrYkDOS/aMy2oy', 1, 'operador'), -- senha: caixa1
('Operador Caixa 2', 'caixa2', '$2y$10$xDhUznV.kXC8DdnUsinMjel13ztwkTKzQWVdWZD7dVnbJI159VT0e', 2, 'operador'), -- senha: caixa2
('Operador Caixa 3', 'caixa3', '$2y$10$iKrCnalQl3V13oKRzULnuOXOrmnhUVYHuHYX7geQgXT58Wi0WZzSK', 3, 'operador'); -- senha: caixa3

-- Products table
CREATE TABLE IF NOT EXISTS produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100),
  preco DECIMAL(10,2),
  categoria VARCHAR(50),
  quantidade_estoque INT
);

-- Sales table
CREATE TABLE IF NOT EXISTS vendas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  data DATETIME DEFAULT CURRENT_TIMESTAMP,
  valor_total DECIMAL(10,2),
  forma_pagamento ENUM('Dinheiro', 'Pix', 'Cartão'),
  caixa INT,
  usuario_id INT,
  controle_caixa_id INT DEFAULT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (controle_caixa_id) REFERENCES `controle_caixa`(id)
);

-- Sale items table
CREATE TABLE IF NOT EXISTS itens_venda (
  id INT AUTO_INCREMENT PRIMARY KEY,
  venda_id INT,
  produto_id INT,
  quantidade INT,
  preco_unitario DECIMAL(10,2),
  FOREIGN KEY (venda_id) REFERENCES vendas(id),
  FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Withdrawals table
CREATE TABLE IF NOT EXISTS sangrias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  valor DECIMAL(10,2),
  data DATETIME DEFAULT CURRENT_TIMESTAMP,
  observacao TEXT,
  usuario_id INT,
  controle_caixa_id INT DEFAULT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (controle_caixa_id) REFERENCES `controle_caixa`(id)
);

-- Criar tabela de controle de caixa
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

-- Adicionar campo de controle_caixa_id na tabela de vendas
ALTER TABLE `vendas` ADD CONSTRAINT `fk_vendas_controle_caixa` FOREIGN KEY (`controle_caixa_id`) REFERENCES `controle_caixa` (`id`);

-- Adicionar campo de controle_caixa_id na tabela de sangrias
ALTER TABLE `sangrias` ADD CONSTRAINT `fk_sangrias_controle_caixa` FOREIGN KEY (`controle_caixa_id`) REFERENCES `controle_caixa` (`id`);

-- Insert some sample data into products
INSERT INTO produtos (nome, preco, categoria, quantidade_estoque) VALUES 
('Refrigerante Cola 2L', 8.50, 'Bebidas', 50),
('Arroz 5kg', 22.90, 'Alimentos', 30),
('Feijão 1kg', 7.50, 'Alimentos', 40),
('Detergente 500ml', 3.25, 'Limpeza', 60),
('Sabonete', 2.50, 'Higiene', 100),
('Chocolate ao Leite', 5.75, 'Doces', 45),
('Leite Integral 1L', 4.99, 'Laticínios', 70); 