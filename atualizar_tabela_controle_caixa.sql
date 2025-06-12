-- Adicionar campos faltantes na tabela controle_caixa
ALTER TABLE `controle_caixa`
ADD COLUMN `valor_dinheiro` decimal(10,2) DEFAULT NULL,
ADD COLUMN `valor_pix` decimal(10,2) DEFAULT NULL,
ADD COLUMN `valor_cartao` decimal(10,2) DEFAULT NULL,
ADD COLUMN `diferenca` decimal(10,2) DEFAULT NULL,
ADD COLUMN `observacoes_fechamento` text DEFAULT NULL; 