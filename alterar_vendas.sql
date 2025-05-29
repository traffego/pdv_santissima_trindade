-- Adicionar coluna usuario_id à tabela vendas
ALTER TABLE vendas
ADD COLUMN usuario_id INT NULL,
ADD CONSTRAINT fk_vendas_usuarios
FOREIGN KEY (usuario_id) REFERENCES usuarios(id);

-- Adicionar coluna usuario_id à tabela sangrias
ALTER TABLE sangrias
ADD COLUMN usuario_id INT NULL,
ADD CONSTRAINT fk_sangrias_usuarios
FOREIGN KEY (usuario_id) REFERENCES usuarios(id);

-- Fazer update inicial (opcional) associando as vendas e sangrias existentes ao usuário admin
UPDATE vendas SET usuario_id = (SELECT id FROM usuarios WHERE usuario = 'admin' LIMIT 1)
WHERE usuario_id IS NULL;

UPDATE sangrias SET usuario_id = (SELECT id FROM usuarios WHERE usuario = 'admin' LIMIT 1)
WHERE usuario_id IS NULL; 