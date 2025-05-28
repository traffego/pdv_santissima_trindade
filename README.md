# Sistema PDV (Ponto de Venda)

Um sistema de Ponto de Venda desenvolvido com PHP procedural e MySQL.

## Funcionalidades

- CRUD completo de produtos
- Sistema de vendas com seleção de produtos e métodos de pagamento
- Controle de sangrias (retiradas de dinheiro)
- Dashboard com gráficos e estatísticas
- Controle de estoque automático

## Requisitos

- PHP 7.0 ou superior
- MySQL 5.6 ou superior
- Servidor web (Apache, Nginx, etc.)

## Instalação

1. Clone ou faça download deste repositório para o diretório do seu servidor web.

2. Crie um banco de dados MySQL chamado `pdv`.

3. Importe o arquivo `setup_database.sql` para criar as tabelas e inserir dados iniciais:

   ```
   mysql -u seu_usuario -p pdv < setup_database.sql
   ```

   Ou utilize uma ferramenta como phpMyAdmin para importar o arquivo SQL.

4. Configure o arquivo `db.php` com suas credenciais de banco de dados:

   ```php
   $host = "localhost"; // Seu host MySQL
   $username = "root";  // Seu usuário MySQL
   $password = "";      // Sua senha MySQL
   $database = "pdv";   // Nome do banco de dados
   ```

5. Acesse o sistema pelo navegador (ex: http://localhost/pdv).

## Estrutura do Sistema

- **Produtos:**
  - produtos.php - Lista todos os produtos
  - adicionar_produto.php - Adiciona um novo produto
  - editar_produto.php - Edita um produto existente
  - excluir_produto.php - Remove um produto

- **Vendas:**
  - vender.php - Interface para realizar vendas
  - processar_venda.php - Processa e registra a venda

- **Sangrias:**
  - sangrias.php - Lista todas as sangrias
  - registrar_sangria.php - Registra uma nova sangria

- **Dashboard:**
  - index.php - Dashboard principal com gráficos e estatísticas

## Uso

1. **Dashboard:** A página inicial mostra estatísticas de vendas, estoque e faturamento.

2. **Produtos:** Gerencie seu catálogo de produtos (adicionar, editar, excluir).

3. **Vendas:** Selecione produtos, defina quantidades e método de pagamento para realizar uma venda.

4. **Sangrias:** Registre retiradas de dinheiro do caixa com observações.

## Tecnologias Utilizadas

- PHP (Procedural)
- MySQL
- Bootstrap 5
- Chart.js
- Font Awesome
- jQuery 