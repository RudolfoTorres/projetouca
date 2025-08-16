<?php
session_start();
require_once __DIR__ . '/../config.php';

// Redireciona para o login se o usuário não estiver autenticado
if (!isset($_SESSION['user_usuario'])) {
    header("Location: /login.php");
    exit();
}

// 1. REGRA: Apenas gerentes e supervisores podem acessar esta página
$user_nivel_permissao = $_SESSION['user_nivel_permissao'];
if ($user_nivel_permissao === 'INSTRUTOR') {
    $_SESSION['register_error'] = "Você não tem permissão para cadastrar usuários.";
    header("Location: /dashboard.php?page=register");
    exit();
}

// Verifica se os campos foram preenchidos
if (!isset($_POST['usuario'], $_POST['senha'], $_POST['nivel_permissao'], $_POST['linhas'])) {
    $_SESSION['register_error'] = "Preencha todos os campos.";
    header("Location: /dashboard.php?page=register");
    exit();
}

$usuario = trim($_POST['usuario']);
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
$nivel_permissao_novo = trim($_POST['nivel_permissao']);
$linhas_selecionadas = $_POST['linhas'];

// Validação dos dados
if (empty($usuario) || empty($senha) || empty($nivel_permissao_novo) || empty($linhas_selecionadas)) {
    $_SESSION['register_error'] = "Preencha todos os campos.";
    header("Location: /dashboard.php?page=register");
    exit();
}

// 2. REGRA: Supervisor só pode cadastrar instrutores
if ($user_nivel_permissao === 'SUPERVISOR' && $nivel_permissao_novo !== 'INSTRUTOR') {
    $_SESSION['register_error'] = "Um supervisor só pode cadastrar usuários com o nível de permissão 'INSTRUTOR'.";
    header("Location: /dashboard.php?page=register");
    exit();
}

try {
    $pdo->beginTransaction();

    // Verifica se o usuário já existe no banco de dados
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['register_error'] = "Usuário já existe.";
        header("Location: /dashboard.php?page=register");
        exit();
    }

    // 3. REGRA: Supervisor só pode vincular o novo usuário às suas próprias linhas de acesso
    if ($user_nivel_permissao === 'SUPERVISOR') {
        $user_id_logado = $_SESSION['user_id'];
        $linhas_do_supervisor_query = $pdo->prepare("SELECT linha_id FROM user_linhas WHERE user_id = ?");
        $linhas_do_supervisor_query->execute([$user_id_logado]);
        $linhas_do_supervisor = $linhas_do_supervisor_query->fetchAll(PDO::FETCH_COLUMN);

        foreach ($linhas_selecionadas as $linha_id) {
            if (!in_array($linha_id, $linhas_do_supervisor)) {
                $pdo->rollBack();
                $_SESSION['register_error'] = "Você não tem permissão para vincular o usuário à linha selecionada.";
                header("Location: /dashboard.php?page=register");
                exit();
            }
        }
    }

    // Insere novo usuário na tabela 'usuarios'
    $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha, nivel_permissao) VALUES (?, ?, ?)");
    $stmt->execute([$usuario, $senha, $nivel_permissao_novo]);
    $new_user_id = $pdo->lastInsertId();

    // Insere as linhas de acesso na tabela 'user_linhas'
    $stmt = $pdo->prepare("INSERT INTO user_linhas (user_id, linha_id) VALUES (?, ?)");
    foreach ($linhas_selecionadas as $linha_id) {
        $stmt->execute([$new_user_id, $linha_id]);
    }

    $pdo->commit();
    $_SESSION['register_success'] = "Usuário e linhas de acesso cadastrados com sucesso!";
    header("Location: /dashboard.php?page=register");
    exit();
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['register_error'] = "Erro no banco de dados: " . $e->getMessage();
    header("Location: /dashboard.php?page=register");
    exit();
}
?>