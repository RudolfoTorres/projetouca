<?php
session_start();
require_once __DIR__ . '/../config.php';

// Verifica se a requisição é do tipo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitiza e obtém os dados do formulário
    $titulo = trim($_POST['titulo']);
    $link = trim($_POST['link']);
    $linha_id = intval($_POST['linha']);
    $sistema_id = intval($_POST['sistema']);
    $plataforma_id = intval($_POST['plataforma']);
    $status_id = intval($_POST['status']);
    $responsavel_id = intval($_POST['responsavel']);
    
    // Validação básica para todos os campos obrigatórios
    if (empty($titulo) || empty($link) || empty($linha_id) || empty($sistema_id) || empty($plataforma_id) || empty($status_id) || empty($responsavel_id)) {
        $_SESSION['media_error'] = "Por favor, preencha todos os campos obrigatórios.";
        header("Location: /dashboard.php?page=cadastrar_midias");
        exit();
    }
    
    try {
        // Prepara a consulta SQL para inserir a nova mídia
        $sql = "INSERT INTO midias (titulo, link, linha_id, sistema_id, plataforma_id, status_id, responsavel_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        // Executa a consulta com os dados do formulário
        $stmt->execute([
            $titulo,
            $link,
            $linha_id,
            $sistema_id,
            $plataforma_id,
            $status_id,
            $responsavel_id
        ]);

        // Define uma mensagem de sucesso na sessão
        $_SESSION['media_success'] = "Mídia cadastrada com sucesso!";
        header("Location: /dashboard.php?page=cadastrar_midias");
        exit();

    } catch (PDOException $e) {
        // Em caso de erro, define uma mensagem de erro na sessão
        $_SESSION['media_error'] = "Erro ao cadastrar a mídia: " . $e->getMessage();
        header("Location: /dashboard.php?page=cadastrar_midias");
        exit();
    }
} else {
    // Se a requisição não for POST, redireciona de volta para o formulário
    header("Location: /dashboard.php?page=cadastrar_midias");
    exit();
}
?>