<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Login - UCA Mídias</title>
    <link rel="stylesheet" href="assets/style.css" />
</head>
<body class="login-page">
    <h1 class="system-title"><i class="fas fa-clapperboard"></i>🎬 UCA Mídias!</h2>
    <br><br>
    <div class="login-container">
        <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="error-message">'.htmlspecialchars($_SESSION['error']).'</div>';
                unset($_SESSION['error']); // Limpa a mensagem para não aparecer sempre
            }
        ?>
        <h2>Login</h2>
        <form method="POST" action="/handle_login.php">
            <input type="text" id="usuario" name="usuario" placeholder="Usuário" required />

            <input type="password" id="senha" name="senha" placeholder="Senha" required />

            <button type="submit">Entrar</button>
        </form>

        <p style="text-align: center; margin-top: 10px;">
            <a href="register.php">Cadastre-se</a>
        </p>
    </div>
</body>
</html>