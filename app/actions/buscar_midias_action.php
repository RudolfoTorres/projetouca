<?php
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_usuario'])) {
    http_response_code(401);
    echo "Sessão expirada. Por favor, faça login novamente.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    
    $user_nivel_permissao = $_SESSION['user_nivel_permissao'];
    $user_id_logado = $_SESSION['user_id'];
    $nome = trim($_POST['nome'] ?? '');
    
    // Agora, verificamos se os campos de seleção múltipla são arrays
    $linhas_selecionadas = $_POST['linhas'] ?? [];
    $sistemas_selecionados = $_POST['sistemas'] ?? [];
    $responsaveis_selecionados = $_POST['pessoas'] ?? [];
    $status_selecionados = $_POST['status'] ?? []; // Novo filtro
    $data_inicial = trim($_POST['data_inicial'] ?? ''); // Novo filtro
    $data_final = trim($_POST['data_final'] ?? '');     // Novo filtro

    $sql = "SELECT
                m.id, -- Adicionado para os botões de ação
                m.titulo,
                l.nome AS linha_nome,
                s.nome AS sistema_nome,
                u.usuario AS responsavel_nome,
                sm.nome AS status_nome, -- Adicionado para o novo filtro
                m.created_at
            FROM midias m
            LEFT JOIN linhas l ON m.linha_id = l.id
            LEFT JOIN sistemas s ON m.sistema_id = s.id
            LEFT JOIN usuarios u ON m.responsavel_id = u.id
            LEFT JOIN status_midia sm ON m.status_id = sm.id -- Novo JOIN
            WHERE 1=1";

    $params = [];
    
    if (!empty($nome)) {
        $sql .= " AND m.titulo LIKE ?";
        $params[] = '%' . $nome . '%';
    }

    if (!empty($linhas_selecionadas)) {
        $placeholders = implode(',', array_fill(0, count($linhas_selecionadas), '?'));
        $sql .= " AND m.linha_id IN (" . $placeholders . ")";
        $params = array_merge($params, $linhas_selecionadas);
    }
    
    if (!empty($sistemas_selecionados)) {
        $placeholders = implode(',', array_fill(0, count($sistemas_selecionados), '?'));
        $sql .= " AND m.sistema_id IN (" . $placeholders . ")";
        $params = array_merge($params, $sistemas_selecionados);
    }
    
    if (!empty($responsaveis_selecionados)) {
        $placeholders = implode(',', array_fill(0, count($responsaveis_selecionados), '?'));
        $sql .= " AND m.responsavel_id IN (" . $placeholders . ")";
        $params = array_merge($params, $responsaveis_selecionados);
    }

    if (!empty($status_selecionados)) {
        $placeholders = implode(',', array_fill(0, count($status_selecionados), '?'));
        $sql .= " AND m.status_id IN (" . $placeholders . ")";
        $params = array_merge($params, $status_selecionados);
    }

    // Adiciona os filtros de data, se existirem
    if (!empty($data_inicial)) {
        $sql .= " AND DATE(m.created_at) >= ?";
        $params[] = $data_inicial;
    }

    if (!empty($data_final)) {
        $sql .= " AND DATE(m.created_at) <= ?";
        $params[] = $data_final;
    }

    if ($user_nivel_permissao !== 'GERENTE') {
        $linhas_do_usuario_query = $pdo->prepare("SELECT linha_id FROM user_linhas WHERE user_id = ?");
        $linhas_do_usuario_query->execute([$user_id_logado]);
        $linhas_acesso = $linhas_do_usuario_query->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($linhas_acesso)) {
            $placeholders = implode(',', array_fill(0, count($linhas_acesso), '?'));
            $sql .= " AND m.linha_id IN (" . $placeholders . ")";
            $params = array_merge($params, $linhas_acesso);
        } else {
            echo "<tr><td colspan='7' class='text-center text-muted'>Nenhum resultado encontrado.</td></tr>";
            exit();
        }
    }

    $sql .= " ORDER BY m.created_at DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultados) > 0) {
            foreach ($resultados as $midia) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($midia['titulo']) . "</td>";
                echo "<td>" . htmlspecialchars($midia['linha_nome']) . "</td>";
                echo "<td>" . htmlspecialchars($midia['sistema_nome']) . "</td>";
                echo "<td>" . htmlspecialchars($midia['responsavel_nome']) . "</td>";
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($midia['created_at']))) . "</td>";
                echo "<td>" . htmlspecialchars($midia['status_nome']) . "</td>";
                echo "<td>";
                echo "<button class='btn btn-warning btn-sm me-2' onclick='editarMidia(" . htmlspecialchars($midia['id']) . ")'>Editar</button>";
                echo "<button class='btn btn-danger btn-sm' onclick='excluirMidia(" . htmlspecialchars($midia['id']) . ")'>Excluir</button>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7' class='text-center text-muted'>Nenhum resultado encontrado.</td></tr>";
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo "<tr><td colspan='7' class='text-center text-danger'>Erro no banco de dados: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
    }
} else {
    http_response_code(403);
    echo "Acesso não autorizado.";
}
?>