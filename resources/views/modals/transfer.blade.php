<div class="modal fade" id="tranferModal" tabindex="-1" aria-labelledby="tranferModal" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tranferModal">Transferência</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">

        <div id="transferStep1">
          <div class="mb-3" id="accountSelectContainer">
            <label for="account_transfer" class="form-label">Conta</label>
            <input type="text" class="form-control" id="account_transfer" name="account_transfer" placeholder="Digite a conta" required>
            <div class="invalid-feedback">Por favor, insira a conta.</div>
          </div>
        </div>

        <div id="transferStep2" style="display: none;">
            <div>
                <h3 class="mb-0">Saldo Atual</h3>
                <h3 class="display-4" id="current_balance">R$ {{ number_format(Auth::user()->accounts[0]->balance, 2, ',', '.') }} </h3>
            </div>

            <div class="mb-3">
                <label class="form-label">Destinatário</label>
                <div class="border rounded p-3 bg-light">
                <p class="mb-1"><strong>Nome:</strong> <span id="transferClientName"></span></p>
                <p class="mb-0"><strong>CPF:</strong> <span id="transferClientCpf"></span></p>
                </div>
            </div>

            <div class="mb-3">
                <label for="transferValue" class="form-label">Valor da transferência (R$)</label>
                <input type="text" class="form-control" id="transferValue" placeholder="0,00" required>
                <div class="invalid-feedback" id="invalid_value">Por favor, insira um valor válido.</div>
            </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnNext_transfer" onclick="nextStepTransfer()">Próximo</button>
      </div>
    </div>
  </div>
</div>

<script>
    let step_transfer = 0;

    $(document).ready(function () {
        $('#transferValue').mask('000.000.000.000.000,00', { reverse: true });

        function validateForm() {
            let valid = true;
            const key = $('#transferValue').val().trim();

            $('#transferValue').removeClass('is-invalid');
            $('#transferValue').siblings('.invalid-feedback').hide();

            if (key === '') {
                valid = false;
            }

            return valid;
            }

            $('#transferValue').on('input', function () {
            $('#btnNext_transfer').prop('disabled', !validateForm());
        });
    });

    function nextStepTransfer() {
        switch (step_transfer) {
            case 0:
                $.ajax({
                url: '/get-account/' + $('#account_transfer').val().trim(),
                method: 'GET',
                data: {
                    account: $('#account_transfer').val().trim(),
                },
                success: function (response) {
                    if (response) {
                        $('#transferClientName').text(response.name || 'N/A');
                        $('#transferClientCpf').text(
                            (response.cpf || '').replace(
                                /^(\d{3})\.(\d{3})\.(\d{3})-(\d{2})$/,
                                '***.$2.$3-**'
                            )
                        );
                        
                        
                        step_transfer++;
                        console.log('Step changed to: ' + step_transfer);
                    } 

                    $('#transferStep1').hide();
                    $('#transferStep2').show();
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || "Erro ao buscar a conta.";

                    $('#account_transfer').addClass('is-invalid');
                    $('#account_transfer').siblings('.invalid-feedback').show();
                },
                complete: function () {
                    $('#btnNext_transfer').prop('disabled', false);
                    $('#btnNext_transfer').text('Enviar');
                }
                });
            break;

        case 1:
            const value = $('#transferValue').val().trim();
            const parsedValue = parseFloat(value.replace(/\./g, '').replace(',', '.'));

            if (!value || parsedValue <= 0) {
            $('#transferValue').addClass('is-invalid');
            return;
            } else {
            $('#transferValue').removeClass('is-invalid');
            }

            $('#btnNext_transfer').prop('disabled', true).text('Processando...');

            $.ajax({
            url: '/transfer',
            method: 'POST',
            data: {
                account: $('#account_transfer').val().trim(),
                amount: parsedValue
            },
            success: function (response) {
                resetInputTransfer();
                $('#tranferModal').modal('hide');
                step_transfer = 0;
                $('#transferStep1').show();
                $('#transferStep2').hide();
                $('#btnNext_transfer').text('Próximo').prop('disabled', true);

                Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Transferência feita com sucesso',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
                });

                updateBalance();
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || "Erro ao realizar a transferência.";
                $('#transferValue').addClass('is-invalid');
                $('#transferValue').siblings('.invalid-feedback').show();
                $("#invalid_value").text(msg);
                $('#btnNext_transfer').text('Enviar').prop('disabled', false);
            }
          });
          break;
      }
    }

    function resetInputTransfer() {
        $('#transferValue').val('').removeAttr('type').removeClass('is-invalid').unmask();
        $('#btnNext_transfer').prop('disabled', true);
        step_transfer = 0;
    }

</script>
