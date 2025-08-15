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
  <title>Dashboard - UCA Mídias</title>
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body class="dashboard-page">

<div class="menu">
  <div class="logo"><p>Bem-vindo(a), <strong><?php echo htmlspecialchars($usuario." ".$user_nivel_permissao); ?></strong>!</p></div>
  <ul>
    <li class="dropdown">
      <a href="#" class="dropbtn">Perfil</a>
      <div class="dropdown-content">
        <a href="#">Cadastrar</a>
        <a href="#">Consultar</a>
        <br>
        <a href="#">Alterar Senha</a>
        <a href="/logout.php">Sair</a>
      </div>
    </li>
  </ul>
</div>
<br>
<div class="dashborad-container">

</div>

</body>
</html>