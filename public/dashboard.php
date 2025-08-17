<?php
session_start();
require_once __DIR__ . '/../app/config.php';

if (!isset($_SESSION['user_usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['user_usuario'];
$user_nivel_permissao = $_SESSION['user_nivel_permissao'];

// Lógica de roteamento: agora 'buscar_midias' é a página padrão
$page = isset($_GET['page']) ? $_GET['page'] : 'buscar_midias';

$allowed_pages = ['register', 'buscar_midias', 'cadastrar_midias', 'editar_midia']; // Adicione outras páginas aqui no futuro

if (!in_array($page, $allowed_pages)) {
    $page = 'buscar_midias'; // Redireciona para a página de busca se a URL for inválida
}

// O caminho para a página a ser incluída
$page_path = __DIR__ . '/' . $page . '.php';

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
    <div class="logo"><p>Bem-vindo(a), <strong><?php echo htmlspecialchars($usuario." - ".$user_nivel_permissao); ?></strong>!</p></div>
    <div class="dropdown">
      <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        Menu
      </a>

      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="dashboard.php?page=buscar_midias">Buscar Mídias</a></li>
        <li><a class="dropdown-item" href="dashboard.php?page=cadastrar_midias">Cadastrar Mídia</a></li>
        
        <?php if ($user_nivel_permissao === 'GERENTE' || $user_nivel_permissao === 'SUPERVISOR'): ?>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="dashboard.php?page=register">Cadastrar usuário</a></li>
        <?php endif; ?>

        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="#">Alterar senha</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="logout.php">Sair</a></li>
      </ul>
    </div>
  </div>

  <div class="main-content">
    <?php
      if (file_exists($page_path)) {
          include $page_path;
      } else {
          echo "Página não encontrada!";
      }
    ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/script.js"></script>
</body>
</html>