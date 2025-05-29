-- Adicionar coluna nivel à tabela usuarios
ALTER TABLE usuarios
ADD COLUMN nivel VARCHAR(20) NOT NULL DEFAULT 'operador' COMMENT 'administrador ou operador';

-- Atualizar o usuário admin como administrador (supondo que já exista um admin com ID 1)
UPDATE usuarios SET nivel = 'administrador', caixa_numero = 0 WHERE usuario = 'admin';

-- Opcional: Criar um administrador se não existir
INSERT INTO usuarios (nome, usuario, senha, caixa_numero, nivel, ativo)
SELECT 'Administrador', 'admin', '$2y$10$USkN1e57.NsuXoM7SQbk3O49JWNafGc2bV7vkW9QrYkDOS/aMy2oy', 0, 'administrador', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE usuario = 'admin'); 