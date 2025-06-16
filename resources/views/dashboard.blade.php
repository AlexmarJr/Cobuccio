<x-app-layout>
    @include('pix')
    @include('deposit') 
    @include('withdraw') 
    @include('modals.transfer') 
    @include('modals.history') 

    <div class="py-12 section" id="home-section">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="min-height: 80vh; display: flex; flex-direction: column;">

                <!-- Saldo e Histórico -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-shrink-0">
                    <div>
                        <h3 class="mb-0">Saldo Atual</h3>
                        <h1 class="display-4" id="current_balance_home">R$ {{ number_format(Auth::user()->accounts[0]->balance, 2, ',', '.') }} </h1>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#historyModel" onclick="loadHistory()">Histórico</button>
                    </div>
                </div>

                <!-- Opções -->
                <div class="row text-center mb-4 flex-shrink-0">
                    <div class="col-6 col-md-3 mb-3">
                        <button type="button" class="btn btn-primary w-100 py-3" data-bs-toggle="modal" data-bs-target="#tranferModal">Transferência</button>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <button type="button" class="btn btn-success w-100 py-3" onclick="navigate('pix')">Pix</button>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <button type="button" class="btn btn-warning w-100 py-3" onclick="navigate('deposit')">Depósito</button>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <button type="button" class="btn btn-danger w-100 py-3" onclick="navigate('withdraw')">Saque</button>
                    </div>
                </div>

                <!-- Notícias com altura fixa e scroll interno -->
                <div style="flex-grow: 1; display: flex; flex-direction: column;">
                    <h3 class="mb-3 flex-shrink-0">Últimas Notícias de Investimento</h3>
                    <div id="news-container" class="border rounded p-3 flex-grow-1" style="overflow-y: auto; max-height: 300px;">
                        <div class="alert alert-info">Carregando notícias...</div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });

        $(function () {
            const newsContainer = $('#news-container');
            $.getJSON('/noticias')
                .done(function (data) {
                    if (data.status === "ok" && data.articles.length > 0) {
                        let html = '';
                        data.articles.forEach(article => {
                            html += `
                                <div class="mb-4 border-bottom pb-3">
                                    <h5><a href="${article.url}" target="_blank" class="text-decoration-none">${article.title}</a></h5>
                                    <p class="text-muted mb-1">${article.source.name} - ${new Date(article.publishedAt).toLocaleDateString()}</p>
                                    <p>${article.description || ''}</p>
                                </div>
                            `;
                        });
                        newsContainer.html(html);
                    } else {
                        newsContainer.html('<div class="alert alert-warning">Nenhuma notícia encontrada.</div>');
                    }
                })
                .fail(function () {
                    newsContainer.html('<div class="alert alert-danger">Erro ao carregar notícias.</div>');
                });
        });

        function navigate(section) {
            // Função para navegar entre seções
            $('.section').attr('hidden', true);
            switch (section) {
                case 'home':
                    updateBalance();
                    $('#home-section').removeAttr('hidden');
                    break;
                case 'pix':
                    $('#pix-section').removeAttr('hidden');
                    break;
                case 'deposit':
                    $('#deposit-section').removeAttr('hidden');
                    break;
                case 'withdraw':
                    $('#withdraw-section').removeAttr('hidden');
                    break;
            }
        }

        function updateBalance() {
            //Atualiza o saldo na seção home
            $.ajax({
                url: '/user/balance',
                method: 'GET',
                success: function(response) {
                    const balance = parseFloat(response.balance);
                    $('#current_balance_home').text(`R$ ${balance.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);   
                },
                error: function() {
                    console.error('Erro ao buscar saldo');
                }
            });
        }
    </script>
</x-app-layout>
