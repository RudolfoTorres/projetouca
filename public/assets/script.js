function editarMidia(id) {
    window.location.href = `dashboard.php?page=editar_midia&id=${id}`;
}

// Aguarda o DOM estar completamente carregado
document.addEventListener('DOMContentLoaded', function() {
    // Verifica se os elementos da busca existem na página
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        const resultadosTabela = document.getElementById('resultados-tabela');
        const loadingSpinner = document.getElementById('loading-spinner');
        const countResultsSpan = document.getElementById('count-results');

        // Função para buscar e exibir os resultados
        const buscarMidias = async () => {
            // Exibe o spinner e limpa a tabela
            loadingSpinner.classList.remove('d-none');
            resultadosTabela.innerHTML = '';
            countResultsSpan.textContent = '...';

            const formData = new FormData(searchForm);
            
            try {
                // CORREÇÃO: Chamando o endpoint dedicado
                const response = await fetch('buscar_midias_ajax.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Erro na requisição. Status: ' + response.status);
                }

                const data = await response.json();
                
                countResultsSpan.textContent = data.count;
                resultadosTabela.innerHTML = data.html;

            } catch (error) {
                countResultsSpan.textContent = '0';
                resultadosTabela.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Erro ao carregar dados: ${error.message}</td></tr>`;
            } finally {
                // Esconde o spinner
                loadingSpinner.classList.add('d-none');
            }
        };

        // Adiciona o evento de submissão do formulário para o AJAX
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Impede o envio padrão do formulário
            buscarMidias();
        });

        // Executa a busca inicial quando a página de busca é carregada
        buscarMidias();
    }
});