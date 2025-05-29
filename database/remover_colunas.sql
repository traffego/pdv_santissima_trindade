-- Remover coluna codigo_barras
ALTER TABLE produtos DROP COLUMN IF EXISTS codigo_barras;

-- Remover coluna categoria_temp
ALTER TABLE produtos DROP COLUMN IF EXISTS categoria_temp; 