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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body class="dashboard-page">

  <div class="menu">
    
  <div class="logo"><p>Bem-vindo(a), <strong><?php echo htmlspecialchars($usuario." ".$user_nivel_permissao); ?></strong>!</p></div>
    <div class="dropdown">
      <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        Menu
      </a>

      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="#">Cadastrar</a></li>
        <li><a class="dropdown-item" href="#">Buscar</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="#">Alterar senha</a></li>
        <li><a class="dropdown-item" href="index.php">Sair</a></li>
      </ul>
    </div>

  </div>

  <br>

  <div class="dashborad-container">

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>