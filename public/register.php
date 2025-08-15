<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastro de usuario - UCA Mídias</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="register-page">
    <div class="register-container">
        <h3>Cadastro de Usuário</h3>

        <?php
            if (isset($_SESSION['register_error'])) {
                echo '<p style="color:red;">' . $_SESSION['register_error'] . '</p>';
                unset($_SESSION['register_error']);
            } elseif (isset($_SESSION['register_success'])) {
                echo '<p style="color:green;">' . $_SESSION['register_success'] . '</p>';
                unset($_SESSION['register_success']);
            }
        ?>

        <form action="/handle_register.php" method="POST">
            <input class="form-control" type="text" id="usuario" name="usuario" placeholder="Usuário" required>

            <input class="form-control" type="password" id="senha" name="senha" placeholder="Senha" required>

            <select class="form-select" id="nivel_permissao" name="nivel_permissao" placeholder="NIVEL" required>
                <option value="INSTRUTOR">INSTRUTOR</option>
                <option value="SUPERVISOR">SUPERVISOR</option>
                <option value="GERENTE">GERENTE</option>
            </select><br>

            <button class="btn btn-primary" type="submit">Cadastrar</button>
        </form>
        <br>
        <p>Já tem conta? <a href="login.php">Fazer login</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>