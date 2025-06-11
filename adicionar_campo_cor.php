<?php
require_once 'db.php';

try {
    $sql = "ALTER TABLE produtos ADD COLUMN cor VARCHAR(7) DEFAULT '#eeeeee'";
    
    if (mysqli_query($conn, $sql)) {
        echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
        echo "<h3 style='color: #28a745; margin-bottom: 20px;'>✓ Campo adicionado com sucesso!</h3>";
        echo "<p style='color: #666;'>O campo 'cor' foi adicionado à tabela produtos com o valor padrão #eeeeee.</p>";
        echo "<p style='margin-top: 20px;'><a href='produtos.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Voltar para Produtos</a></p>";
        echo "</div>";
    } else {
        throw new Exception("Erro ao adicionar campo: " . mysqli_error($conn));
    }
} catch (Exception $e) {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); background-color: #fff3f3;'>";
    echo "<h3 style='color: #dc3545; margin-bottom: 20px;'>✗ Erro ao adicionar campo</h3>";
    echo "<p style='color: #666;'>" . $e->getMessage() . "</p>";
    echo "<p style='margin-top: 20px;'><a href='produtos.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Voltar para Produtos</a></p>";
    echo "</div>";
}
?> 