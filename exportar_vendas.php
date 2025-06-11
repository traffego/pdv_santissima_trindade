<?php
require_once 'db.php';
require_once 'check_admin.php';

// Definir nome do arquivo com base nos filtros
$nome_arquivo = 'vendas';

// Adicionar período ao nome
if (isset($_GET['data_inicio']) && isset($_GET['data_fim'])) {
    $nome_arquivo .= '_' . date('d-m-Y', strtotime($_GET['data_inicio'])) . 
                    '_a_' . date('d-m-Y', strtotime($_GET['data_fim']));
}

// Adicionar caixa ao nome se filtrado
if (!empty($_GET['caixa'])) {
    $nome_arquivo .= '_caixa' . $_GET['caixa'];
}

// Adicionar forma de pagamento ao nome se filtrado
if (!empty($_GET['forma_pagamento'])) {
    $nome_arquivo .= '_' . strtolower(str_replace('ã', 'a', $_GET['forma_pagamento']));
}

// Adicionar usuário ao nome se filtrado
if (!empty($_GET['usuario'])) {
    $sql_usuario = "SELECT nome FROM usuarios WHERE id = ?";
    $stmt_usuario = mysqli_prepare($conn, $sql_usuario);
    mysqli_stmt_bind_param($stmt_usuario, "i", $_GET['usuario']);
    mysqli_stmt_execute($stmt_usuario);
    $result_usuario = mysqli_stmt_get_result($stmt_usuario);
    if ($row_usuario = mysqli_fetch_assoc($result_usuario)) {
        $nome_arquivo .= '_' . strtolower(str_replace(' ', '-', $row_usuario['nome']));
    }
}

// Adicionar data e hora da exportação
$nome_arquivo .= '_exportado_' . date('d-m-Y_H-i');

// Limpar caracteres especiais e espaços do nome do arquivo
$nome_arquivo = preg_replace('/[^a-zA-Z0-9_-]/', '', $nome_arquivo);

// Definir headers para download do Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $nome_arquivo . '.xls"');
header('Cache-Control: max-age=0');

// Pegar parâmetros do filtro
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$caixa_filtro = isset($_GET['caixa']) ? $_GET['caixa'] : '';
$forma_pagamento = isset($_GET['forma_pagamento']) ? $_GET['forma_pagamento'] : '';
$usuario_filtro = isset($_GET['usuario']) ? $_GET['usuario'] : '';

// Construir a consulta SQL
$sql = "SELECT 
    v.id as 'ID Venda',
    DATE_FORMAT(v.data_hora, '%d/%m/%Y %H:%i') as 'Data/Hora',
    v.valor_total as 'Valor Total',
    v.forma_pagamento as 'Forma Pagamento',
    v.caixa as 'Caixa',
    u.nome as 'Vendedor',
    GROUP_CONCAT(CONCAT(p.nome, ' (', iv.quantidade, ')') SEPARATOR ', ') as 'Produtos'
FROM vendas v 
LEFT JOIN usuarios u ON v.usuario_id = u.id
LEFT JOIN itens_venda iv ON v.id = iv.venda_id
LEFT JOIN produtos p ON iv.produto_id = p.id
WHERE DATE(v.data_hora) BETWEEN ? AND ?";

$params = array($data_inicio, $data_fim);
$types = "ss";

if (!empty($caixa_filtro)) {
    $sql .= " AND v.caixa = ?";
    $params[] = $caixa_filtro;
    $types .= "i";
}

if (!empty($forma_pagamento)) {
    $sql .= " AND v.forma_pagamento = ?";
    $params[] = $forma_pagamento;
    $types .= "s";
}

if (!empty($usuario_filtro)) {
    $sql .= " AND v.usuario_id = ?";
    $params[] = $usuario_filtro;
    $types .= "i";
}

$sql .= " GROUP BY v.id ORDER BY v.data_hora DESC";

// Preparar e executar a consulta
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Início da tabela Excel
echo '<table border="1">';

// Cabeçalho
echo '<tr style="background-color: #f0f0f0; font-weight: bold;">';
$first_row = true;
while ($row = mysqli_fetch_assoc($result)) {
    if ($first_row) {
        foreach (array_keys($row) as $header) {
            echo '<th>' . $header . '</th>';
        }
        echo '</tr>';
        $first_row = false;
    }
    
    // Dados
    echo '<tr>';
    foreach ($row as $value) {
        // Formatar valor total como moeda
        if ($value === $row['Valor Total']) {
            $value = 'R$ ' . number_format($value, 2, ',', '.');
        }
        echo '<td>' . str_replace('"', '""', $value) . '</td>';
    }
    echo '</tr>';
}

echo '</table>';
?> 