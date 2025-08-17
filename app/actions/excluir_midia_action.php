<?php
session_start();
require_once __DIR__ . '/../config.php';

// Define um handler de exceção para retornar um JSON de erro em caso de falha fatal
set_exception_handler(function($e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => "Erro fatal no servidor: " . $e->getMessage()]);
    exit();
});

// Acesso não autorizado se não for uma requisição AJAX POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
    exit();
}

// Verifica a autenticação da sessão
if (!isset($_SESSION['user_usuario'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Por favor, faça login novamente.']);
    exit();
}

$user_nivel_permissao = $_SESSION['user_nivel_permissao'];
$user_id_logado = $_SESSION['user_id'];
$midia_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($midia_id === 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID da mídia não fornecido.']);
    exit();
}

try {
    // 1. Verifique se a mídia existe e pegue a linha e o responsável
    $sql_check = "SELECT linha_id, responsavel_id FROM midias WHERE id = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$midia_id]);
    $midia = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$midia) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Mídia não encontrada.']);
        exit();
    }

    // 2. Verifique as permissões de exclusão
    $pode_excluir = false;
    
    // Gerentes e Supervisores podem excluir qualquer mídia
    if ($user_nivel_permissao === 'GERENTE' || $user_nivel_permissao === 'SUPERVISOR') {
        $pode_excluir = true;
    } else {
        // Instrutores só podem excluir mídias de sua autoria
        if ($user_nivel_permissao === 'INSTRUTOR' && $midia['responsavel_id'] == $user_id_logado) {
            $pode_excluir = true;
        } else {
            // Outros usuários só podem excluir mídias de suas linhas de atuação
            $sql_perm = "SELECT COUNT(*) FROM user_linhas WHERE user_id = ? AND linha_id = ?";
            $stmt_perm = $pdo->prepare($sql_perm);
            $stmt_perm->execute([$user_id_logado, $midia['linha_id']]);
            if ($stmt_perm->fetchColumn() > 0) {
                $pode_excluir = true;
            }
        }
    }
    
    if (!$pode_excluir) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Você não tem permissão para excluir esta mídia.']);
        exit();
    }

    // 3. Exclua a mídia
    $sql_delete = "DELETE FROM midias WHERE id = ?";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->execute([$midia_id]);

    if ($stmt_delete->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Mídia excluída com sucesso!']);
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Mídia não encontrada ou já excluída.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => "Erro no banco de dados: " . $e->getMessage()]);
}
?>