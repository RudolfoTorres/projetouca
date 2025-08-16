<?php
session_start();
require_once __DIR__ . '/../app/config.php';

// Redireciona para o login se o usuário não estiver autenticado
if (!isset($_SESSION['user_usuario'])) {
    header("Location: /login.php");
    exit();
}

// REGRA 1: Instrutores não têm acesso a cadastrar usuários
if ($_SESSION['user_nivel_permissao'] === 'INSTRUTOR') {
    header("Location: /dashboard.php");
    exit();
}

// Buscar a lista de linhas para o formulário
// Essa lista será filtrada para supervisores
$linhas_query = $pdo->query("SELECT id, nome FROM linhas ORDER BY nome ASC");
$linhas = $linhas_query->fetchAll(PDO::FETCH_ASSOC);

// Se o usuário logado for um SUPERVISOR, filtre as linhas
if ($_SESSION['user_nivel_permissao'] === 'SUPERVISOR') {
    $user_id = $_SESSION['user_id'];
    $linhas_acesso_query = $pdo->prepare("SELECT linha_id FROM user_linhas WHERE user_id = ?");
    $linhas_acesso_query->execute([$user_id]);
    $linhas_acesso = $linhas_acesso_query->fetchAll(PDO::FETCH_COLUMN);

    // Filtra as linhas para exibir apenas as que o supervisor tem acesso
    $linhas_disponiveis = array_filter($linhas, function($linha) use ($linhas_acesso) {
        return in_array($linha['id'], $linhas_acesso);
    });
} else {
    // Para o GERENTE, todas as linhas estão disponíveis
    $linhas_disponiveis = $linhas;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastro de usuário - UCA Mídias</title>
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
                <?php if ($_SESSION['user_nivel_permissao'] === 'SUPERVISOR'): ?>
                    <option value="INSTRUTOR">INSTRUTOR</option>
                <?php endif; ?>

                <?php if ($_SESSION['user_nivel_permissao'] === 'GERENTE'): ?>
                    <option value="INSTRUTOR">INSTRUTOR</option>
                    <option value="SUPERVISOR">SUPERVISOR</option>
                    <option value="GERENTE">GERENTE</option>
                <?php endif; ?>
            </select><br>

            <div class="mb-3">
                <label for="linhas" class="form-label">Linhas de Acesso</label>
                <select class="form-select" id="linhas" name="linhas[]" multiple required>
                    <?php foreach ($linhas_disponiveis as $linha): ?>
                        <option value="<?php echo htmlspecialchars($linha['id']); ?>">
                            <?php echo htmlspecialchars($linha['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Use a tecla Ctrl para selecionar múltiplas linhas.</small>
             </div>

            <button class="btn btn-primary" type="submit">Cadastrar</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>