<?php
session_start();
require_once __DIR__ . '/../config.php';

// Verifica se os campos foram preenchidos
if (!isset($_POST['usuario'], $_POST['senha'])) {
    $_SESSION['register_error'] = "Preencha todos os campos.";
    header("Location: /register.php");
    exit;
}

$usuario = trim($_POST['usuario']);
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha
$nivel_permissao = trim($_POST['nivel_permissao']);

try {
    // Verifica se já existe um usuário com o mesmo nome
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['register_error'] = "Usuário já existe.";
        header("Location: /register.php");
        exit;
    }

    // Insere novo usuário
    $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha, nivel_permissao) VALUES (?, ?, ?)");
    $stmt->execute([$usuario, $senha, $nivel_permissao]);

    $_SESSION['register_success'] = "Usuário cadastrado com sucesso!";
    header("Location: /register.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['register_error'] = "Erro no banco de dados: " . $e->getMessage();
    header("Location: /register.php");
    exit;
}