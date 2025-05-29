-- Usar o banco de dados
USE pdv;

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
ALTER TABLE `vendas` 
ADD COLUMN `controle_caixa_id` int(11) DEFAULT NULL;

-- Adicionar a chave estrangeira na tabela de vendas
ALTER TABLE `vendas` 
ADD CONSTRAINT `fk_vendas_controle_caixa` 
FOREIGN KEY (`controle_caixa_id`) REFERENCES `controle_caixa` (`id`);

-- Adicionar campo de controle_caixa_id na tabela de sangrias
ALTER TABLE `sangrias` 
ADD COLUMN `controle_caixa_id` int(11) DEFAULT NULL;

-- Adicionar a chave estrangeira na tabela de sangrias
ALTER TABLE `sangrias` 
ADD CONSTRAINT `fk_sangrias_controle_caixa` 
FOREIGN KEY (`controle_caixa_id`) REFERENCES `controle_caixa` (`id`);

-- Adicionar campo data_venda na tabela de vendas
ALTER TABLE `vendas` 
ADD COLUMN `data_venda` datetime DEFAULT CURRENT_TIMESTAMP; 