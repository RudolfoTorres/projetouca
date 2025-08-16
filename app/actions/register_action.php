<?php
session_start();
require_once __DIR__ . '/../config.php';

// Verifica se os campos foram preenchidos
if (!isset($_POST['usuario'], $_POST['senha'], $_POST['nivel_permissao'], $_POST['linhas'])) {
    $_SESSION['register_error'] = "Todos os campos, incluindo a seleção de linhas, são obrigatórios.";
    header("Location: /register.php");
    exit;
}

$usuario = trim($_POST['usuario']);
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha
$nivel_permissao = trim($_POST['nivel_permissao']);
$linhas = $_POST['linhas']; // Isso será um array de IDs

try {
    // Iniciar a transação antes de qualquer operação de banco de dados
    $pdo->beginTransaction();

    // Verifica se já existe um usuário com o mesmo nome
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['register_error'] = "Usuário já existe.";
        // Não é necessário rollBack aqui, pois não houve alteração no BD.
        header("Location: /register.php");
        exit;
    }

    // Insere novo usuário na tabela `usuarios`
    $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha, nivel_permissao) VALUES (?, ?, ?)");
    $stmt->execute([$usuario, $senha, $nivel_permissao]);

    // Obtém o ID do usuário recém-inserido
    $user_id = $pdo->lastInsertId();

    // Prepara a consulta para a tabela `user_linhas`
    $stmt_linhas = $pdo->prepare("INSERT INTO user_linhas (user_id, linha_id) VALUES (?, ?)");

    // Insere as linhas de acesso na tabela de junção
    foreach ($linhas as $linha_id) {
        $stmt_linhas->execute([$user_id, $linha_id]);
    }

    // Se tudo deu certo, confirma a transação
    $pdo->commit();

    $_SESSION['register_success'] = "Usuário e linhas de acesso cadastrados com sucesso!";
    header("Location: /register.php");
    exit;
} catch (PDOException $e) {
    // Em caso de erro, desfaz todas as alterações
    $pdo->rollBack();
    $_SESSION['register_error'] = "Erro no banco de dados: " . $e->getMessage();
    header("Location: /register.php");
    exit;
}
?>