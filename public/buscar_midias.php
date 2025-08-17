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

// Lógica para buscar as pessoas
$pessoas_query_sql = "SELECT id, usuario FROM usuarios WHERE nivel_permissao IN ('INSTRUTOR', 'SUPERVISOR') ORDER BY usuario ASC";
$pessoas_params = [];
if ($user_nivel_permissao === 'INSTRUTOR') {
    // Instrutores só podem ver a si mesmos
    $pessoas_query_sql = "SELECT id, usuario FROM usuarios WHERE id = ?";
    $pessoas_params = [$user_id_logado];
} else if ($user_nivel_permissao === 'SUPERVISOR') {
    // Supervisores vêem a si mesmos e instrutores das suas linhas
    $pessoas_query_sql = "
        SELECT DISTINCT u.id, u.usuario FROM usuarios u
        LEFT JOIN user_linhas ul ON u.id = ul.user_id
        WHERE (u.nivel_permissao = 'INSTRUTOR' AND ul.linha_id IN (
            SELECT linha_id FROM user_linhas WHERE user_id = ?
        )) OR u.id = ?
        ORDER BY u.usuario ASC";
    $pessoas_params = [$user_id_logado, $user_id_logado];
}

$pessoas_query = $pdo->prepare($pessoas_query_sql);
$pessoas_query->execute($pessoas_params);
$pessoas = $pessoas_query->fetchAll(PDO::FETCH_ASSOC);


// Lógica para buscar os status de mídia
$status_query_sql = "SELECT id, nome FROM status_midia ORDER BY nome ASC";
$status_query = $pdo->prepare($status_query_sql);
$status_query->execute();
$status = $status_query->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid py-4">
    <div class="card p-4 mb-4">
        <form id="search-form" action="/handle_buscar_midias.php" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">Nome da Mídia</label>
                    <input type="text" class="form-control" id="nome" name="nome">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="data_inicial" class="form-label">Data Inicial</label>
                    <input type="date" class="form-control" id="data_inicial" name="data_inicial">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="data_final" class="form-label">Data Final</label>
                    <input type="date" class="form-control" id="data_final" name="data_final">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="linhas" class="form-label">Linha</label>
                    <select class="form-select multi-boxes" id="linhas" name="linhas[]" multiple>
                        <?php foreach ($linhas as $linha): ?>
                            <option value="<?php echo htmlspecialchars($linha['id']); ?>">
                                <?php echo htmlspecialchars($linha['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="sistemas" class="form-label">Sistema</label>
                    <select class="form-select multi-boxes" id="sistemas" name="sistemas[]" multiple>
                        <?php foreach ($sistemas as $sistema): ?>
                            <option value="<?php echo htmlspecialchars($sistema['id']); ?>">
                                <?php echo htmlspecialchars($sistema['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="pessoas" class="form-label">Responsável</label>
                    <select class="form-select multi-boxes" id="pessoas" name="pessoas[]" multiple>
                        <?php foreach ($pessoas as $pessoa): ?>
                            <option value="<?php echo htmlspecialchars($pessoa['id']); ?>">
                                <?php echo htmlspecialchars($pessoa['usuario']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select multi-boxes" id="status" name="status[]" multiple>
                        <?php foreach ($status as $st): ?>
                            <option value="<?php echo htmlspecialchars($st['id']); ?>">
                                <?php echo htmlspecialchars($st['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-12 text-end mt-3 bi-footer">
                <small class="form-text text-muted">Use a tecla Ctrl para selecionar múltiplas opções.</small>
                <button type="submit" class="btn btn-secondary">Buscar</button>
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
                    <th>Status</th> <th>Ações</th>  </tr>
            </thead>
            <tbody id="resultados-tabela">
                <tr>
                    <td colspan="7" class="text-center text-muted">Use os filtros para encontrar mídias.</td>
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