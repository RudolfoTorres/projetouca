<?php
// Este arquivo é carregado dentro de dashboard.php
// A conexão com o banco de dados ($pdo) e a sessão já estão disponíveis.

$user_nivel_permissao = $_SESSION['user_nivel_permissao'];
$user_id_logado = $_SESSION['user_id'];
$midia_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$midia = null;
$error_message = '';

// Variável para controlar o campo Responsável
$disabled_responsavel_field = ($user_nivel_permissao === 'INSTRUTOR');

if ($midia_id > 0) {
    try {
        $sql = "SELECT * FROM midias WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$midia_id]);
        $midia = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$midia) {
            $error_message = "Mídia não encontrada.";
        } else {
            // Verifica a permissão do usuário para editar esta mídia
            if ($user_nivel_permissao !== 'GERENTE') {
                $sql_perm = "SELECT COUNT(*) FROM user_linhas WHERE user_id = ? AND linha_id = ?";
                $stmt_perm = $pdo->prepare($sql_perm);
                $stmt_perm->execute([$user_id_logado, $midia['linha_id']]);
                if ($stmt_perm->fetchColumn() == 0) {
                    $error_message = "Você não tem permissão para editar esta mídia.";
                    $midia = null; // Impede a exibição do formulário
                }
            }
        }
    } catch (PDOException $e) {
        $error_message = "Erro ao carregar os dados da mídia: " . $e->getMessage();
    }
} else {
    $error_message = "ID da mídia não fornecido.";
}

// Lógica para buscar as opções dos SELECTs
$linhas_query_sql = "SELECT id, nome FROM linhas ORDER BY nome ASC";
$linhas_params = [];
if ($user_nivel_permissao !== 'GERENTE') {
    $linhas_query_sql = "SELECT l.id, l.nome FROM user_linhas ul JOIN linhas l ON ul.linha_id = l.id WHERE ul.user_id = ? ORDER BY l.nome ASC";
    $linhas_params = [$user_id_logado];
}
$linhas_query = $pdo->prepare($linhas_query_sql);
$linhas_query->execute($linhas_params);
$linhas = $linhas_query->fetchAll(PDO::FETCH_ASSOC);

$sistemas_query_sql = "SELECT id, nome FROM sistemas ORDER BY nome ASC";
$sistemas_params = [];
if ($user_nivel_permissao !== 'GERENTE') {
    $linhas_do_usuario_query = $pdo->prepare("SELECT linha_id FROM user_linhas WHERE user_id = ?");
    $linhas_do_usuario_query->execute([$user_id_logado]);
    $linhas_acesso = $linhas_do_usuario_query->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($linhas_acesso)) {
        $placeholders = implode(',', array_fill(0, count($linhas_acesso), '?'));
        $sistemas_query_sql = "SELECT id, nome FROM sistemas WHERE linha_id IN (" . $placeholders . ") ORDER BY nome ASC";
        $sistemas_params = $linhas_acesso;
    } else {
        $sistemas = [];
    }
}
$sistemas_query = $pdo->prepare($sistemas_query_sql);
$sistemas_query->execute($sistemas_params);
$sistemas = $sistemas_query->fetchAll(PDO::FETCH_ASSOC);

$plataformas_query_sql = "SELECT id, nome FROM plataformas ORDER BY nome ASC";
$plataformas_query = $pdo->prepare($plataformas_query_sql);
$plataformas_query->execute();
$plataformas = $plataformas_query->fetchAll(PDO::FETCH_ASSOC);

$responsaveis_query_sql = "SELECT id, usuario FROM usuarios WHERE nivel_permissao IN ('INSTRUTOR', 'SUPERVISOR') ORDER BY usuario ASC";
$responsaveis_params = [];
if ($user_nivel_permissao === 'INSTRUTOR') {
    $responsaveis_query_sql = "SELECT id, usuario FROM usuarios WHERE id = ?";
    $responsaveis_params = [$user_id_logado];
} else if ($user_nivel_permissao === 'SUPERVISOR') {
    $responsaveis_query_sql = "
        SELECT DISTINCT u.id, u.usuario FROM usuarios u
        LEFT JOIN user_linhas ul ON u.id = ul.user_id
        WHERE (u.nivel_permissao = 'INSTRUTOR' AND ul.linha_id IN (
            SELECT linha_id FROM user_linhas WHERE user_id = ?
        )) OR u.id = ?
        ORDER BY u.usuario ASC";
    $responsaveis_params = [$user_id_logado, $user_id_logado];
}
$responsaveis_query = $pdo->prepare($responsaveis_query_sql);
$responsaveis_query->execute($responsaveis_params);
$responsaveis = $responsaveis_query->fetchAll(PDO::FETCH_ASSOC);

$status_query_sql = "SELECT id, nome FROM status_midia ORDER BY nome ASC";
$status_query = $pdo->prepare($status_query_sql);
$status_query->execute();
$status = $status_query->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-4">
    <h3>Editar Mídia</h3>
    <div class="card p-4">
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php else: ?>
            <form id="edit-form" action="handle_editar_midia.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($midia['id']); ?>">
                
                <div class="mb-3">
                    <label for="titulo" class="form-label">Título da Mídia:</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($midia['titulo'] ?? ''); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="link" class="form-label">Link da Mídia:</label>
                    <input type="text" class="form-control" id="link" name="link" value="<?php echo htmlspecialchars($midia['link'] ?? ''); ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="linha_id" class="form-label">Linha:</label>
                        <select class="form-select" id="linha_id" name="linha_id" required>
                            <?php foreach ($linhas as $linha): ?>
                                <option value="<?php echo htmlspecialchars($linha['id']); ?>" <?php echo ($midia['linha_id'] == $linha['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($linha['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="sistema_id" class="form-label">Sistema:</label>
                        <select class="form-select" id="sistema_id" name="sistema_id" required>
                            <?php foreach ($sistemas as $sistema): ?>
                                <option value="<?php echo htmlspecialchars($sistema['id']); ?>" <?php echo ($midia['sistema_id'] == $sistema['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sistema['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="plataforma_id" class="form-label">Plataforma:</label>
                        <select class="form-select" id="plataforma_id" name="plataforma_id" required>
                            <?php foreach ($plataformas as $plataforma): ?>
                                <option value="<?php echo htmlspecialchars($plataforma['id']); ?>" <?php echo ($midia['plataforma_id'] == $plataforma['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($plataforma['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="status_id" class="form-label">Status da Mídia:</label>
                        <select class="form-select" id="status_id" name="status_id" required>
                            <?php foreach ($status as $st): ?>
                                <option value="<?php echo htmlspecialchars($st['id']); ?>" <?php echo ($midia['status_id'] == $st['id']) ? 'selected' : ''; ?>>
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
                            <option value="<?php echo htmlspecialchars($responsavel['id']); ?>" <?php echo ($midia['responsavel_id'] == $responsavel['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($responsavel['usuario']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($disabled_responsavel_field): ?>
                        <input type="hidden" name="responsavel" value="<?php echo htmlspecialchars($midia['responsavel_id']); ?>">
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-secondary">Salvar Alterações</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>