-- Criar tabela de permissões de categorias por caixa
CREATE TABLE IF NOT EXISTS permissoes_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_permissao (usuario_id, categoria)
);

-- Criar tabela de categorias (para manter consistência)
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE
);

-- Inserir categorias existentes dos produtos
INSERT IGNORE INTO categorias (nome)
SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL AND categoria != '';

-- Dar todas as permissões para usuários administradores por padrão
INSERT INTO permissoes_categorias (usuario_id, categoria)
SELECT u.id, c.nome
FROM usuarios u
CROSS JOIN categorias c
WHERE u.nivel = 'administrador'
ON DUPLICATE KEY UPDATE categoria = c.nome; 