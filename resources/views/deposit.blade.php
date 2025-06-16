<div class="py-12 section" id="deposit-section" hidden>
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
     <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="min-height: 80vh; display: flex; flex-direction: column;">
        <div class="mb-6 flex items-center justify-between">
            <div class="mb-4 flex-shrink-0 d-flex align-items-center gap-3">
                <a href="#" onclick="navigate('home'); return false;" 
                    class="text-primary d-flex align-items-center gap-1" 
                    style="font-weight: 500; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <h3 class="text-2xl font-semibold">Deposito</h3>
            </div>
    </div>
      <div class="mb-4">
        <label for="depositValue" class="form-label">Valor do depósito (R$)</label>
        <input type="text" id="depositValue" class="form-control" placeholder="R$ 0,00" required>
        <div class="invalid-feedback">Por favor, insira um valor válido.</div>
      </div>
      <button type="button" class="btn btn-success" onclick="deposit()">Depositar</button>
  </div>
</div>
</div>

<script>
  $('#depositValue').mask('000.000.000.000.000,00', {reverse: true});

  function deposit() {
    const rawVal = $('#depositValue').val().trim();

    if (!rawVal) {
      $('#depositValue').addClass('is-invalid');
      return;
    }

    // Remove pontos e troca vírgula por ponto para transformar em número
    const depositAmount = parseFloat(rawVal.replace(/\./g, '').replace(',', '.'));

    if (isNaN(depositAmount) || depositAmount <= 0) {
      $('#depositValue').addClass('is-invalid');
      return;
    }

    $('#depositValue').removeClass('is-invalid');

    $.ajax({
      url: '/deposit',
      method: 'POST',
      data: {
        amount: depositAmount,
      },
      success: function(response) {
        $('#depositValue').val('');
        updateBalance();
        navigate('home');
        Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: response.message,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                customClass: {
                popup: 'colored-toast',
                background: '#38a169',
            }
        });
      },
      error: function(xhr) {
        alert('Erro ao realizar depósito. Tente novamente.');
        console.error(xhr.responseText);
      }
    });
  }
</script>
