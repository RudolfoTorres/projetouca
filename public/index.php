<?php
session_start();

// Supondo que a variável 'loggedin' é definida na sessão após o login
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Se o usuário está logado, redirecione para o dashboard
    header("Location: login.php");
    exit;
}
?>