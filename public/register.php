<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastro</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="register-container">
        <h2>Cadastro de Usuário</h2>

        <?php
            if (isset($_SESSION['register_error'])) {
                echo '<p style="color:red;">' . $_SESSION['register_error'] . '</p>';
                unset($_SESSION['register_error']);
            } elseif (isset($_SESSION['register_success'])) {
                echo '<p style="color:green;">' . $_SESSION['register_success'] . '</p>';
                unset($_SESSION['register_success']);
            }
        ?>

        <form action="../app/actions/register_action.php" method="POST">
            <input type="text" id="usuario" name="usuario" placeholder="Usuário" required><br>

            <input type="password" id="senha" name="senha" placeholder="Senha" required><br>

            <select id="nivel_permissao" name="nivel_permissao" placeholder="NIVEL" required>
                <option value="INSTRUTOR">INSTRUTOR</option>
                <option value="SUPERVISOR">SUPERVISOR</option>
                <option value="GERENTE">GERENTE</option>
            </select><br>

            <button type="submit">Cadastrar</button>
        </form>

        <p>Já tem conta? <a href="login.php">Fazer login</a></p>
    </div>
</body>
</html>