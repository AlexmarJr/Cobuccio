<!-- Modal -->
<div class="modal fade" id="historyModel" tabindex="-1" aria-labelledby="historyModelLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="historyModelLabel">Histórico de Transações</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">

        <!-- Container das transações -->
        <div id="transactions-container" style="overflow-y: auto; max-height: 300px;">
            <div class="alert alert-info">Carregando histórico de transações...</div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script>
function formatCurrency(value) {
  return 'R$ ' + parseFloat(value).toFixed(2).replace('.', ',');
}

function loadHistory() {
    $('#transactions-container').html('<div class="alert alert-info">Carregando histórico de transações...</div>');

    $.ajax({
        url: "{{ route('history.list') }}",  // Ajuste essa URL conforme sua rota Laravel
        method: 'GET',
        success: function(data) {
            if (data.length === 0) {
                $('#transactions-container').html('<div class="alert alert-warning">Nenhuma transação encontrada.</div>');
                return;
            }

            let html = '';
            data.forEach(function(transaction) {
                // Readable transaction type
                let readableType = {
                    'pix_transfer': 'Pix',
                    'transfer': 'Transfer',
                    'deposit': 'Deposito',
                    'withdraw': 'Saque'
                }[transaction.transaction_type] || transaction.transaction_type;

                // CSS class for value color
                let valueColorClass = (transaction.transaction_type === 'deposit') ? 'text-success' :
                                    (transaction.transaction_type === 'withdraw') ? 'text-danger' : '';

                // Receiver info line (only if it exists)
                let receiverLine = transaction.receiver ? `<strong>Receiver:</strong> ${transaction.receiver.name}<br>` : '';

                // Refund button for Pix or Transfer only
                let refundButton = (['pix_transfer', 'transfer'].includes(transaction.transaction_type) && transaction.status !== 'refunded') ?
                    `<button class="btn btn-sm btn-danger" onclick="refundTransaction('${transaction.id}')">Extorno</button>` : '<small class="text-muted">Extornado</small>';

                html += `
                    <div class="border rounded p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between mb-1">
                            <div class="small">
                                <strong>Type:</strong> ${readableType}<br>
                                <strong>Amount:</strong> <span class="${valueColorClass}">${formatCurrency(transaction.amount)}</span><br>
                                ${receiverLine}
                            </div>
                            <div class="align-self-start">
                                ${refundButton}
                            </div>
                        </div>
                    </div>
                `;
            });

            $('#transactions-container').html(html);
        },
        error: function() {
            $('#transactions-container').html('<div class="alert alert-danger">Erro ao carregar as transações.</div>');
        }
    });
}

    function refundTransaction(transactionId) {
        Swal.fire({
            title: 'Tem certeza?',
            text: 'Você tem certeza que deseja solicitar o estorno desta transação?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, solicitar estorno',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('transactions.refund') }}", 
                method: 'POST',
                data: {
                    transaction_id: transactionId
                },
                success: function(response) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',  // aparece no canto superior direito
                        icon: 'success',
                        title: 'Success!',
                        text: 'Refund requested successfully!',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    updateBalance();
                    loadHistory();
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Erro ao solicitar o estorno. Tente novamente mais tarde.'
                    });
                }
            });
            }
        });
    }
</script>
