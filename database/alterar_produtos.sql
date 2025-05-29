-- Adicionar coluna categoria_id
ALTER TABLE produtos ADD COLUMN categoria_id INT;

-- Adicionar chave estrangeira
ALTER TABLE produtos
ADD CONSTRAINT fk_produto_categoria
FOREIGN KEY (categoria_id) REFERENCES categorias(id)
ON DELETE SET NULL;

-- Atualizar produtos existentes para manter a categoria como texto temporariamente
ALTER TABLE produtos ADD COLUMN categoria_temp VARCHAR(100);
UPDATE produtos SET categoria_temp = categoria;

-- Remover a coluna antiga de categoria
ALTER TABLE produtos DROP COLUMN categoria; 