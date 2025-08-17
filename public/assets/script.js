// Aguarda o DOM estar completamente carregado
document.addEventListener('DOMContentLoaded', function() {
    // Verifica se os elementos da busca existem na página
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        const resultadosTabela = document.getElementById('resultados-tabela');
        const loadingSpinner = document.getElementById('loading-spinner');

        // Função para buscar e exibir os resultados
        const buscarMidias = async () => {
            // Exibe o spinner e limpa a tabela
            loadingSpinner.classList.remove('d-none');
            resultadosTabela.innerHTML = '';

            const formData = new FormData(searchForm);
            
            try {
                const response = await fetch('/handle_buscar_midias.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Indica que é uma requisição AJAX
                    }
                });

                if (!response.ok) {
                    throw new Error('Erro na requisição. Status: ' + response.status);
                }

                const htmlResultados = await response.text();
                resultadosTabela.innerHTML = htmlResultados;

            } catch (error) {
                resultadosTabela.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Erro ao carregar dados: ${error.message}</td></tr>`;
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