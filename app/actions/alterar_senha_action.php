<?php
session_start();
require_once __DIR__ . '/../config.php';

// Redireciona para o login se o usuário não estiver autenticado
if (!isset($_SESSION['user_usuario'])) {
    header("Location: /login.php");
    exit();
}

// 1. Coleta e validação dos dados do formulário
$user_id_logado = $_SESSION['user_id'];
$senha_antiga = $_POST['senha_antiga'] ?? '';
$nova_senha = $_POST['nova_senha'] ?? '';
$confirma_senha = $_POST['confirma_senha'] ?? '';

// Validação dos campos
if (empty($senha_antiga) || empty($nova_senha) || empty($confirma_senha)) {
    $_SESSION['error_message'] = 'Por favor, preencha todos os campos.';
    header("Location: /dashboard.php?page=alterar_senha");
    exit();
}

if ($nova_senha !== $confirma_senha) {
    $_SESSION['error_message'] = 'A nova senha e a confirmação não coincidem.';
    header("Location: /dashboard.php?page=alterar_senha");
    exit();
}

// 2. Verifique a senha antiga no banco de dados
try {
    $sql_check = "SELECT senha FROM usuarios WHERE id = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$user_id_logado]);
    $user = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($senha_antiga, $user['senha'])) {
        $_SESSION['error_message'] = 'A senha antiga está incorreta.';
        header("Location: /dashboard.php?page=alterar_senha");
        exit();
    }

    // 3. Atualize a senha no banco de dados
    $hashed_password = password_hash($nova_senha, PASSWORD_DEFAULT);
    $sql_update = "UPDATE usuarios SET senha = ? WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$hashed_password, $user_id_logado]);

    $_SESSION['success_message'] = 'Senha alterada com sucesso!';

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro no banco de dados: ' . $e->getMessage();
}

// 4. Redirecione de volta para a página de alteração de senha
header("Location: /dashboard.php?page=alterar_senha");
exit();
?>