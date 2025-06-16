@include('modals.pix_key')
@include('modals.pix_transfer')

<div class="py-12 section" id="pix-section" hidden>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="min-height: 80vh; display: flex; flex-direction: column;">

            <!-- Cabeçalho com link Voltar e título Pix na mesma linha -->
            <div class="mb-4 flex-shrink-0 d-flex align-items-center gap-3">
                <a href="#" onclick="navigate('home'); return false;" 
                   class="text-primary d-flex align-items-center gap-1" 
                   style="font-weight: 500; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <h3 class="mb-0">Pix</h3>
            </div>

            <!-- Opções Pix -->
            <div class="row text-center mb-4 flex-shrink-0">
                <div class="col-6 col-md-3 mb-3">
                    <button type="button" class="btn btn-primary w-100 py-3" data-bs-toggle="modal" data-bs-target="#pixKeyModal">Criar chave Pix</button>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <button type="button" class="btn btn-success w-100 py-3" data-bs-toggle="modal" data-bs-target="#pixTranferModal">Fazer Pix</button>
                </div>
            </div>

            <!-- Informações / Conteúdo Pix -->
            <div style="flex-grow: 1; display: flex; flex-direction: column;">
                <h3 class="mb-3 flex-shrink-0">Suas chaves Pix</h3>
                <div id="pix-keys-container" class="border rounded p-3 flex-grow-1" style="overflow-y: auto; max-height: 300px;">
                    <div class="alert alert-info">Carregando suas chaves Pix...</div>
                </div>
            </div>

        </div>

    </div>
</div>
<script>
    $(document).ready(function () {
    renderPixKeys();
    });

    function renderPixKeys() {
        $('#pix-keys-container').html('<div class="alert alert-info">Carregando suas chaves Pix...</div>');

        $.ajax({
        url: "{{ route('pix.list') }}",
        method: 'GET',
        success: function (data) {
            if (data.length === 0) {
                $('#pix-keys-container').html('<div class="alert alert-warning">Nenhuma chave Pix cadastrada.</div>');
                return;
            }

            let html = '';
            data.forEach(function (chave) {
                // Determina o tipo de chave Pix com base no tipo
                let tipo = {
                    cpf: 'CPF',
                    email: 'Email',
                    phone: 'Telefone',
                    random: 'Chave aleatória'
                }[chave.type] || 'Tipo desconhecido';

                // Rendeziriza as chaves Pix
                html += `
                    <div class="mb-2 flex justify-between items-center border rounded p-2 bg-gray-50">
                        <div>
                            <strong>${tipo}:</strong> ${chave.key ?? '<em>(gerada automaticamente)</em>'}
                        </div>
                        <button class="text-red-500 hover:text-red-700 delete-pix-key" onclick="deletePixKey('${chave.id}')" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            });

            $('#pix-keys-container').html(html);
        },
        error: function () {
            $('#pix-keys-container').html('<div class="alert alert-danger">Erro ao carregar as chaves Pix.</div>');
        }
        });
    }

    function deletePixKey(id) {
        Swal.fire({
            title: 'Tem certeza?',
            text: "Você deseja excluir esta chave Pix?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#aaa',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/pix.delete/${id}`,
                    type: 'DELETE',
                    success: function () {
                        renderPixKeys();

                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'success',
                            title: 'Chave Pix excluída com sucesso',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true,
                            background: '#38a169',
                            color: '#fff',
                        });
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: 'Erro ao excluir a chave Pix.'
                        });
                    }
                });
            }
        });
    }
</script>
