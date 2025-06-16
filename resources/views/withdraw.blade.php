<div class="py-12 section" id="withdraw-section" hidden>
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
     <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="min-height: 80vh; display: flex; flex-direction: column;">
        <div class="mb-6 flex items-center justify-between">
            <div class="mb-4 flex-shrink-0 d-flex align-items-center gap-3">
                <a href="#" onclick="navigate('home'); return false;" 
                    class="text-primary d-flex align-items-center gap-1" 
                    style="font-weight: 500; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <h3 class="text-2xl font-semibold">Saque</h3>
            </div>
    </div>
      <div class="mb-4">
        <label for="withdrawValue" class="form-label">Valor do saque (R$)</label>
        <input type="text" id="withdrawValue" class="form-control" placeholder="R$ 0,00" required>
        <div class="invalid-feedback">Por favor, insira um valor válido.</div>
      </div>
      <button type="button" class="btn btn-success" onclick="withdraw()">Saque</button>
  </div>
</div>
</div>

<script>
  $('#withdrawValue').mask('000.000.000.000.000,00', {reverse: true});

  function withdraw() {
    const rawVal = $('#withdrawValue').val().trim();

    if (!rawVal) {
      $('#withdrawValue').addClass('is-invalid');
      return;
    }

    // Remove pontos e troca vírgula por ponto para transformar em número
    const withdrawAmount = parseFloat(rawVal.replace(/\./g, '').replace(',', '.'));

    if (isNaN(withdrawAmount) || withdrawAmount <= 0) {
      $('#withdrawValue').addClass('is-invalid');
      return;
    }

    $('#withdrawValue').removeClass('is-invalid');

    $.ajax({
      url: '/withdraw',
      method: 'POST',
      data: {
        amount: withdrawAmount,
      },
      success: function(response) {
        $('#withdrawValue').val('');
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
