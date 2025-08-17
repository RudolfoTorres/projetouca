<?php
// Este arquivo é carregado dentro de dashboard.php
// A conexão com o banco de dados ($pdo) e a sessão já estão disponíveis.

$user_id_logado = $_SESSION['user_id'];

// Lógica para exibir e limpar as mensagens de sessão (mesmo do buscar_midias.php)
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success mt-3" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger mt-3" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}

?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12 text-center">
            <h2 class="mt-3 mb-4">Alterar Senha</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <form id="alterar-senha-form" action="handle_alterar_senha.php" method="POST">
                <div class="mb-3">
                    <label for="senha_antiga" class="form-label">Senha Antiga:</label>
                    <input type="password" class="form-control" id="senha_antiga" name="senha_antiga" required>
                </div>
                <div class="mb-3">
                    <label for="nova_senha" class="form-label">Nova Senha:</label>
                    <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                </div>
                <div class="mb-3">
                    <label for="confirma_senha" class="form-label">Confirme a Nova Senha:</label>
                    <input type="password" class="form-control" id="confirma_senha" name="confirma_senha" required>
                </div>
                <button type="submit" class="btn btn-secondary">Alterar Senha</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alterarSenhaForm = document.getElementById('alterar-senha-form');

        if (alterarSenhaForm) {
            alterarSenhaForm.addEventListener('submit', function(event) {
                const novaSenha = document.getElementById('nova_senha').value;
                const confirmaSenha = document.getElementById('confirma_senha').value;

                if (novaSenha !== confirmaSenha) {
                    event.preventDefault(); // Impede o envio do formulário
                    alert('As senhas não coincidem. Por favor, verifique.');
                }
            });
        }
    });
</script>