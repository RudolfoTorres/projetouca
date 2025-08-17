<?php
// O session_start() agora está no handle_editar_midia.php
require_once __DIR__ . '/../config.php';

// Redireciona para o login se o usuário não estiver autenticado
if (!isset($_SESSION['user_usuario'])) {
    header("Location: /login.php");
    exit();
}

// 1. Coleta e validação dos dados do formulário
$user_nivel_permissao = $_SESSION['user_nivel_permissao'];
$user_id_logado = $_SESSION['user_id'];
$midia_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$titulo = trim($_POST['titulo'] ?? '');
$link = trim($_POST['link'] ?? '');
$linha_id = isset($_POST['linha_id']) ? intval($_POST['linha_id']) : 0;
$sistema_id = isset($_POST['sistema_id']) ? intval($_POST['sistema_id']) : 0;
$plataforma_id = isset($_POST['plataforma_id']) ? intval($_POST['plataforma_id']) : 0;
$responsavel_id = isset($_POST['responsavel']) ? intval($_POST['responsavel']) : 0;
$status_id = isset($_POST['status_id']) ? intval($_POST['status_id']) : 0;

// Validação dos campos obrigatórios
if (empty($titulo) || empty($link) || $linha_id === 0 || $sistema_id === 0 || $plataforma_id === 0 || $responsavel_id === 0 || $status_id === 0) {
    $_SESSION['error_message'] = 'Por favor, preencha todos os campos obrigatórios.';
    header("Location: /dashboard.php?page=buscar_midias");
    exit();
}

// 2. Realiza a atualização no banco de dados
try {
    // Define a variável $sql com a consulta de atualização
    $sql = "UPDATE midias SET
                titulo = ?,
                link = ?,
                linha_id = ?,
                sistema_id = ?,
                plataforma_id = ?,
                responsavel_id = ?,
                status_id = ?
            WHERE id = ?";
             
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $titulo,
        $link,
        $linha_id,
        $sistema_id,
        $plataforma_id,
        $responsavel_id,
        $status_id,
        $midia_id
    ]);

    // 3. Verifique se a atualização foi bem-sucedida
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = 'Mídia atualizada com sucesso!';
    } else {
        $_SESSION['error_message'] = 'Nenhuma alteração foi feita ou a mídia não foi encontrada.';
    }

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro no banco de dados: ' . $e->getMessage();
}

// Redireciona para a página de busca, que irá exibir a mensagem da sessão
header("Location: /dashboard.php?page=buscar_midias");
exit();