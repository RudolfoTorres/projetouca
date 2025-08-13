<?php

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/projetoUCA.db');
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "❌ Erro na conexão: " . $e->getMessage();
}

?>