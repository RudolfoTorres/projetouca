<?php
session_start();

if (!isset($_SESSION['user_usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['user_usuario'];
$user_nivel_permissao = $_SESSION['user_nivel_permissao'];

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #f6f6f6;
    }

    .menu {
        background-color: #461F71;
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .menu .logo {
        font-size: 20px;
        font-weight: bold;
    }

    .menu ul {
        list-style: none;
        display: flex;
        margin: 0;
        padding: 0;
    }

    .menu ul li {
        margin-left: 20px;
    }

    .menu ul li a {
        color: white;
        text-decoration: none;
        transition: 0.3s;
    }

    .menu ul li a:hover {
        text-decoration: underline;
    }

    .container {
        padding: 40px;
        text-align: center;
    }
  </style>
</head>
<body>

<div class="menu">
  <div class="logo"><p>Bem-vindo(a), <strong><?php echo htmlspecialchars($usuario." ".$user_nivel_permissao); ?></strong>!</p></div>
  <ul>
    <li><a href="#">Início</a></li>
    <li><a href="#">Perfil</a></li>
    <li><a href="#">Configurações</a></li>
    <li><a href="../app/actions/logout.php">Sair</a></li>
  </ul>
</div>

<div class="container">
  <h2>Área restrita</h2>
  <p>Conteúdo da dashboard vai aqui.</p>
</div>

</body>
</html>