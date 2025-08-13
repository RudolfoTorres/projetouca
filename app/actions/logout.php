<?php
session_start();

// Remove todas as variáveis de sessão
$_SESSION = [];

// Destroi a sessão
session_destroy();

// Redireciona para a tela de login
header("Location: /index.php");
exit();