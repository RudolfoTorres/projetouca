<?php
// Este arquivo é carregado dentro de dashboard.php
// A conexão com o banco de dados ($pdo) e a sessão já estão disponíveis.

$user_nivel_permissao = $_SESSION['user_nivel_permissao'];
$user_id_logado = $_SESSION['user_id'];

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
// GERENTE: Vê todos os sistemas
// SUPERVISOR / INSTRUTOR: Vê apenas os sistemas das linhas que têm acesso
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

// Lógica para buscar as pessoas
// GERENTE: Vê todas as pessoas
// SUPERVISOR: Vê apenas pessoas das suas linhas
// INSTRUTOR: Vê apenas a si próprio
$pessoas_query_sql = "SELECT id, usuario FROM usuarios WHERE nivel_permissao != 'GERENTE' ORDER BY usuario ASC";
$pessoas_params = [];

if ($user_nivel_permissao === 'SUPERVISOR') {
    $supervisor_linhas_query = $pdo->prepare("SELECT linha_id FROM user_linhas WHERE user_id = ?");
    $supervisor_linhas_query->execute([$user_id_logado]);
    $supervisor_linhas = $supervisor_linhas_query->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($supervisor_linhas)) {
        $in_placeholder = str_repeat('?,', count($supervisor_linhas) - 1) . '?';
        $pessoas_query_sql = "SELECT DISTINCT u.id, u.usuario FROM usuarios u JOIN user_linhas ul ON u.id = ul.user_id WHERE ul.linha_id IN ($in_placeholder) AND u.nivel_permissao != 'GERENTE' ORDER BY u.usuario ASC";
        $pessoas_params = $supervisor_linhas;
    } else {
        $pessoas = [];
    }
} elseif ($user_nivel_permissao === 'INSTRUTOR') {
    $pessoas_query_sql = "SELECT id, usuario FROM usuarios WHERE id = ?";
    $pessoas_params = [$user_id_logado];
}

$pessoas_query = $pdo->prepare($pessoas_query_sql);
$pessoas_query->execute($pessoas_params);
$pessoas = $pessoas_query->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="dashboard-content container">
    <br>
    <h3>Buscar Mídias</h3>
    
    <div class="card p-4 mb-4">
        <form id="search-form">
            <div class="row g-3">
                <div class="col-12">
                    <label for="nome" class="form-label">Nome da Mídia:</label>
                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite o nome da mídia...">
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-4">
                    <label for="linhas" class="form-label">Linhas de Acesso:</label>
                    <select class="form-select" id="linhas" name="linhas[]" multiple>
                        <?php foreach ($linhas as $linha): ?>
                            <option value="<?php echo htmlspecialchars($linha['id']); ?>">
                                <?php echo htmlspecialchars($linha['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="sistemas" class="form-label">Sistemas:</label>
                    <select class="form-select" id="sistemas" name="sistemas[]" multiple>
                        <?php foreach ($sistemas as $sistema): ?>
                            <option value="<?php echo htmlspecialchars($sistema['id']); ?>">
                                <?php echo htmlspecialchars($sistema['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="pessoas" class="form-label">Pessoa:</label>
                    <select class="form-select" id="pessoas" name="pessoas[]" multiple>
                        <?php foreach ($pessoas as $pessoa): ?>
                            <option value="<?php echo htmlspecialchars($pessoa['id']); ?>">
                                <?php echo htmlspecialchars($pessoa['usuario']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-secondary">Buscar</button>
                </div>
            </div>
        </form>
    </div>

    <h4>Resultados</h4>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Nome da Mídia</th>
                    <th>Linha</th>
                    <th>Sistema</th>
                    <th>Pessoa</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="resultados-tabela">
                <tr>
                    <td colspan="6" class="text-center text-muted">Use os filtros para encontrar mídias.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="loading-spinner" class="d-none text-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
    </div>
</div>