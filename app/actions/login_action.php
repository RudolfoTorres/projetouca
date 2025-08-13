<?php
session_start();

require_once '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (!$usuario || !$senha) {
        $_SESSION['error'] = 'Por favor, preencha usuário e senha.';
        header('Location: ../../public/login.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, usuario, senha, nivel_permissao FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_usuario'] = $user['usuario'];
            $_SESSION['user_nivel_permissao'] = $user['nivel_permissao'];
            header('Location: /dashboard.php');
            exit;
        } else {
            $_SESSION['error'] = 'Usuário ou senha inválidos.';
            header('Location: /login.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erro no banco: ' . $e->getMessage();
        header('Location: /login.php');
        exit;
    }
} else {
    header('Location: /login.php');
    exit;
}
