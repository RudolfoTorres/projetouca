<?php
// Redireciona para o login se o usuário não estiver autenticado
if (!isset($_SESSION['user_usuario'])) {
    header("Location: /login.php");
    exit();
}

$user_nivel_permissao = $_SESSION['user_nivel_permissao'];
$user_id_logado = $_SESSION['user_id'];

// REGRA: Instrutores não têm acesso a cadastrar mídias
if ($user_nivel_permissao === 'INSTRUTOR') {
    // Apenas pode cadastrar mídias da sua própria autoria.
}

// Lógica para buscar as linhas e popular o filtro
$linhas_query_sql = "SELECT id, nome FROM linhas ORDER BY nome ASC";
$linhas_params = [];
if ($user_nivel_permissao !== 'GERENTE') {
    $linhas_query_sql = "SELECT l.id, l.nome FROM user_linhas ul JOIN linhas l ON ul.linha_id = l.id WHERE ul.user_id = ? ORDER BY l.nome ASC";
    $linhas_params = [$user_id_logado];
}
$linhas_query = $pdo->prepare($linhas_query_sql);
$linhas_query->execute($linhas_params);
$linhas = $linhas_query->fetchAll(PDO::FETCH_ASSOC);

// Lógica para buscar os sistemas
$sistemas_query_sql = "SELECT id, nome FROM sistemas ORDER BY nome ASC";
$sistemas_params = [];
if ($user_nivel_permissao !== 'GERENTE') {
    $linhas_do_usuario_query = $pdo->prepare("SELECT linha_id FROM user_linhas WHERE user_id = ?");
    $linhas_do_usuario_query->execute([$user_id_logado]);
    $linhas_do_usuario = $linhas_do_usuario_query->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($linhas_do_usuario)) {
        $in_placeholder = str_repeat('?,', count($linhas_do_usuario) - 1) . '?';
        $sistemas_query_sql = "
            SELECT id, nome
            FROM sistemas
            WHERE linha_id IN ($in_placeholder)
            ORDER BY nome ASC
        ";
        $sistemas_params = $linhas_do_usuario;
    } else {
        $sistemas = [];
    }
} else {
    $sistemas = $pdo->query($sistemas_query_sql)->fetchAll(PDO::FETCH_ASSOC);
}
$sistemas_query = $pdo->prepare($sistemas_query_sql);
$sistemas_query->execute($sistemas_params);
$sistemas = $sistemas_query->fetchAll(PDO::FETCH_ASSOC);

// Lógica para buscar as plataformas
$plataformas_query = $pdo->query("SELECT id, nome FROM plataformas ORDER BY nome ASC");
$plataformas = $plataformas_query->fetchAll(PDO::FETCH_ASSOC);

// Lógica para buscar os status de mídia
$status_query = $pdo->query("SELECT id, nome FROM status_midia ORDER BY nome ASC");
$status = $status_query->fetchAll(PDO::FETCH_ASSOC);

// Lógica para buscar os responsáveis (usuários) com base no nível de permissão
$responsavel_query_sql = "SELECT id, usuario FROM usuarios ORDER BY usuario ASC";
$responsavel_params = [];
$disabled_responsavel_field = false;

if ($user_nivel_permissao === 'SUPERVISOR') {
    // Supervisor pode selecionar qualquer INSTRUTOR de suas linhas
    $linhas_do_usuario_query = $pdo->prepare("SELECT linha_id FROM user_linhas WHERE user_id = ?");
    $linhas_do_usuario_query->execute([$user_id_logado]);
    $linhas_do_usuario = $linhas_do_usuario_query->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($linhas_do_usuario)) {
        $in_placeholder = str_repeat('?,', count($linhas_do_usuario) - 1) . '?';
        $responsavel_query_sql = "
            SELECT DISTINCT u.id, u.usuario
            FROM usuarios u
            JOIN user_linhas ul ON u.id = ul.user_id
            WHERE ul.linha_id IN ($in_placeholder) AND u.nivel_permissao = 'INSTRUTOR'
            ORDER BY u.usuario ASC
        ";
        $responsavel_params = $linhas_do_usuario;
    } else {
        $responsaveis = [];
    }
} elseif ($user_nivel_permissao === 'INSTRUTOR') {
    // Instrutor só pode selecionar a si mesmo
    $responsavel_query_sql = "SELECT id, usuario FROM usuarios WHERE id = ?";
    $responsavel_params = [$user_id_logado];
    $disabled_responsavel_field = true;
}

$responsavel_query = $pdo->prepare($responsavel_query_sql);
$responsavel_query->execute($responsavel_params);
$responsaveis = $responsavel_query->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="container mt-5">
        <div class="card p-4">
            <h3 class="mb-4">Cadastrar Nova Mídia</h3>
            <?php
            if (isset($_SESSION['media_error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['media_error'] . '</div>';
                unset($_SESSION['media_error']);
            } elseif (isset($_SESSION['media_success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['media_success'] . '</div>';
                unset($_SESSION['media_success']);
            }
            ?>
            <form action="/handle_cadastrar_midias.php" method="POST">
                <div class="mb-3">
                    <label for="titulo" class="form-label">Título da Mídia:</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" required>
                </div>
                <div class="mb-3">
                    <label for="link" class="form-label">Link:</label>
                    <input type="text" class="form-control" id="link" name="link" required>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="linha" class="form-label">Linha:</label>
                        <select class="form-select" id="linha" name="linha" required>
                            <option value="">Selecione a Linha</option>
                            <?php foreach ($linhas as $linha): ?>
                                <option value="<?php echo htmlspecialchars($linha['id']); ?>">
                                    <?php echo htmlspecialchars($linha['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="sistema" class="form-label">Sistema:</label>
                        <select class="form-select" id="sistema" name="sistema" required>
                            <option value="">Selecione o Sistema</option>
                            <?php foreach ($sistemas as $sistema): ?>
                                <option value="<?php echo htmlspecialchars($sistema['id']); ?>">
                                    <?php echo htmlspecialchars($sistema['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="plataforma" class="form-label">Plataforma:</label>
                        <select class="form-select" id="plataforma" name="plataforma" required>
                            <option value="">Selecione a Plataforma</option>
                            <?php foreach ($plataformas as $plataforma): ?>
                                <option value="<?php echo htmlspecialchars($plataforma['id']); ?>">
                                    <?php echo htmlspecialchars($plataforma['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status:</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Selecione o Status</option>
                            <?php foreach ($status as $st): ?>
                                <option value="<?php echo htmlspecialchars($st['id']); ?>">
                                    <?php echo htmlspecialchars($st['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3 mt-3">
                    <label for="responsavel" class="form-label">Responsável pela Gravação:</label>
                    <select class="form-select" id="responsavel" name="responsavel" required <?php echo $disabled_responsavel_field ? 'disabled' : ''; ?>>
                        <option value="">Selecione o Responsável</option>
                        <?php foreach ($responsaveis as $responsavel): ?>
                            <option value="<?php echo htmlspecialchars($responsavel['id']); ?>" <?php echo ($responsavel['id'] == $user_id_logado) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($responsavel['usuario']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($disabled_responsavel_field): ?>
                        <input type="hidden" name="responsavel" value="<?php echo htmlspecialchars($user_id_logado); ?>">
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-secondary">Cadastrar</button>
            </form>
        </div>
    </div>