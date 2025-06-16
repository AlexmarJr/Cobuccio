<!-- Modal Bootstrap -->
<div class="modal fade" id="pixKeyModal" tabindex="-1" aria-labelledby="pixKeyModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pixKeyModalLabel">Nova chave Pix</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">

        <form id="pixKeyForm" novalidate>
          <div class="mb-3">
            <label for="pixKeyType" class="form-label">Tipo de chave Pix</label>
            <select class="form-select" id="pixKeyType" required>
              <option value="" selected disabled>Selecione</option>
              <option value="cpf">CPF</option>
              <option value="email">Email</option>
              <option value="phone">Telefone</option>
              <option value="random">Chave aleatória</option>
            </select>
          </div>

          <div class="mb-3" id="pixKeyField">
            <label for="pixKeyValue" class="form-label">Chave</label>
            <input type="text" class="form-control" id="pixKeyValue" placeholder="Digite a chave" />
            <div id="pixKeyError" class="invalid-feedback d-none"></div>
          </div>

          <button type="submit" class="btn btn-success">Salvar</button>
        </form>

      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function () {

    function resetInput() {
      $('#pixKeyValue').val('').off('input').attr('type', 'text').removeClass('is-invalid');
      $('#pixKeyError').addClass('d-none').text('');
      $('#pixKeyField').show();
      $('#pixKeyValue').unmask();
    }

    $('#pixKeyType').on('change', function () {
      const type = $(this).val();

      resetInput();

      switch (type) {
        case 'cpf':
          $('#pixKeyValue').mask('000.000.000-00');
          $('#pixKeyValue').attr('type', 'text');
          $('#pixKeyField').show();
          break;

        case 'email':
          $('#pixKeyValue').attr('type', 'email');
          $('#pixKeyField').show();
          break;

        case 'phone':
          $('#pixKeyValue').mask('(00) 00000-0000');
          $('#pixKeyValue').attr('type', 'text');
          $('#pixKeyField').show();
          break;

        case 'random':
          $('#pixKeyField').hide();
          break;

        default:
          $('#pixKeyField').show();
          break;
      }
    });

    function validarCPF(cpf) {
      cpf = cpf.replace(/[^\d]+/g, '');

      if (cpf.length !== 11) return false;
      if (/^(\d)\1{10}$/.test(cpf)) return false;

      let soma = 0;
      let resto;

      for (let i = 1; i <= 9; i++) {
        soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
      }
      resto = (soma * 10) % 11;
      if (resto === 10 || resto === 11) resto = 0;
      if (resto !== parseInt(cpf.substring(9, 10))) return false;

      soma = 0;
      for (let i = 1; i <= 10; i++) {
        soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
      }
      resto = (soma * 10) % 11;
      if (resto === 10 || resto === 11) resto = 0;
      if (resto !== parseInt(cpf.substring(10, 11))) return false;

      return true;
    }

    $('#pixKeyForm').on('submit', function (e) {
      e.preventDefault();

      const type = $('#pixKeyType').val();
      const value = $('#pixKeyValue').val().trim();

      $('#pixKeyValue').removeClass('is-invalid');
      $('#pixKeyError').addClass('d-none').text('');

      if (!type) {
        alert('Selecione o tipo da chave Pix.');
        return;
      }

      // Só valida campo vazio se não for tipo "random"
      if (type !== 'random' && !value) {
        $('#pixKeyValue').addClass('is-invalid');
        $('#pixKeyError').removeClass('d-none').text('Campo obrigatório.');
        return;
      }

      if (type === 'cpf' && !validarCPF(value)) {
        $('#pixKeyValue').addClass('is-invalid');
        $('#pixKeyError').removeClass('d-none').text('CPF inválido.');
        return;
      }

      if (type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
          $('#pixKeyValue').addClass('is-invalid');
          $('#pixKeyError').removeClass('d-none').text('Email inválido.');
          return;
        }
      }

      if (type === 'phone') {
        const digits = value.replace(/\D/g, '');
        if (digits.length < 10) {
          $('#pixKeyValue').addClass('is-invalid');
          $('#pixKeyError').removeClass('d-none').text('Telefone inválido.');
          return;
        }
      }

      $.ajax({
        url: "{{ route('pix.save') }}",
        method: 'POST',
        data: {
          type: type,
          key: type === 'random' ? null : value,
        },
        success: function (response) {
            renderPixKeys();
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: response.message,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                customClass: {
                    popup: 'colored-toast'
                },
                background: '#38a169',
            });
            $('#pixKeyModal').modal('hide');
            $('#pixKeyForm')[0].reset();
        },
        error: function (xhr) {
            const errorMessage = xhr.responseJSON?.errors.key || 'Erro ao salvar a chave Pix.';
            $('#pixKeyValue').addClass('is-invalid');
            swal.fire({
                icon: 'error',
                title: 'Erro',
                text: errorMessage,
                customClass: {
                    popup: 'colored-toast'
                }
            });
        }
      });
    });
  });
</script>
