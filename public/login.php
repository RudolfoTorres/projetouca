<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Login - UCA Mídias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css" />
</head>
<body class="login-page">
    <h1 class="system-title"><i class="fas fa-clapperboard"></i>🎬 UCA Mídias!</h1>
    <br><br>
    <div class="login-container">
        <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="error-message">'.htmlspecialchars($_SESSION['error']).'</div>';
                unset($_SESSION['error']); // Limpa a mensagem para não aparecer sempre
            }
        ?>
        <h3>Login</h3>
        <form method="POST" action="/handle_login.php">
            <input class="form-control" type="text" id="usuario" name="usuario" placeholder="Usuário" required />

            <input class="form-control" type="password" id="senha" name="senha" placeholder="Senha" required />

            <button class="btn btn-primary" type="submit">Entrar</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>