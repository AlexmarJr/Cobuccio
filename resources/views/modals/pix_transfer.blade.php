<div class="modal fade" id="pixTranferModal" tabindex="-1" aria-labelledby="pixTranferModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pixTranferModalLabel">Transferência Pix</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">

        <div id="PixTransferStep1">
            <div class="mb-3">
            <label for="pixKeyType_pix" class="form-label">Tipo da chave Pix</label>
            <select class="form-select" id="pixKeyType_pix" name="pixKeyType_pix" required>
              <option value="" selected disabled>Selecione</option>
              <option value="cpf">CPF</option>
              <option value="email">Email</option>
              <option value="phone">Telefone</option>
              <option value="random">Chave aleatória</option>
            </select>
            <div class="invalid-feedback">Por favor, selecione o tipo da chave Pix.</div>
          </div>

          <div class="mb-3" id="pixKeyValueContainer_pix">
            <label for="pixKeyValue_pix" class="form-label">Chave Pix</label>
            <input type="text" class="form-control" id="pixKeyValue_pix" placeholder="Digite a chave" required>
            <div class="invalid-feedback">Por favor, insira a chave Pix.</div>
          </div>
        </div>

        <div id="PixTransferStep2" style="display: none;">
            <div>
                <h3 class="mb-0">Saldo Atual</h3>
                <h3 class="display-4" id="current_balance">R$ {{ number_format(Auth::user()->accounts[0]->balance, 2, ',', '.') }} </h3>
            </div>

            <div class="mb-3">
                <label class="form-label">Destinatário</label>
                <div class="border rounded p-3 bg-light">
                <p class="mb-1"><strong>Nome:</strong> <span id="PixTransferClientName"></span></p>
                <p class="mb-0"><strong>CPF:</strong> <span id="PixTransferClientCpf"></span></p>
                </div>
            </div>

            <div class="mb-3">
                <label for="pixValue" class="form-label">Valor da transferência (R$)</label>
                <input type="text" class="form-control" id="pixTransferValue" placeholder="0,00" required>
                <div class="invalid-feedback">Por favor, insira um valor válido.</div>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="addToFavorites">
              <label class="form-check-label" for="addToFavorites">
                  Adicionar aos favoritos
              </label>
            </div>
        </div>

        
        <div id="pixFavoritesContainer" style="display: none;">
          <div class="mb-3">
            <label for="favoritsSelect" class="form-label">Favoritos</label>
            <select class="form-select" id="favoritsSelect" name="favoritsSelect" onchange="selectedFavorite()" required>
              <option value="" selected disabled>Selecione</option>
            </select>
          </div>
        </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btn_favorits" onclick="openFavorites()">Favoritos</button>
        <button type="button" class="btn btn-success" id="btnNext_pix" onclick="nextStepPixTransfer()">Próximo</button>
      </div>
    </div>
  </div>
</div>
</div>

<script>
  let step_pix_transfer = 0;
  var favorites ;
  $(document).ready(function() {
    $('#pixTransferValue').mask('000.000.000.000.000,00', {reverse: true});

    function validateForm() {
      let valid = true;
      const type = $('#pixKeyType_pix').val();
      const key = $('#pixKeyValue_pix').val().trim();

      $('#pixKeyType_pix').removeClass('is-invalid');
      $('#pixKeyType_pix').siblings('.invalid-feedback').hide();
      $('#pixKeyValue_pix').removeClass('is-invalid');
      $('#pixKeyValue_pix').siblings('.invalid-feedback').hide();

      if (!type) {
        valid = false;
      }

      if (type !== 'random' && key === '') {
        valid = false;
      }

      return valid;
    }

    function applyMask(type) {
      $('#pixKeyValue_pix').unmask();
      switch(type) {
        case 'cpf':
          $('#pixKeyValue_pix').mask('000.000.000-00').attr('type', 'text').prop('required', true);
          $('#pixKeyValueContainer_pix').show();
          break;
        case 'email':
          $('#pixKeyValue_pix').attr('type', 'email').prop('required', true);
          $('#pixKeyValueContainer_pix').show();
          break;
        case 'phone':
          $('#pixKeyValue_pix').mask('(00) 00000-0000').attr('type', 'text').prop('required', true);
          $('#pixKeyValueContainer_pix').show();
          break;
        case 'random':
          $('#pixKeyValueContainer_pix').hide();
          $('#pixKeyValue_pix').prop('required', false);
          break;
        default:
          $('#pixKeyValueContainer_pix').show();
          $('#pixKeyValue_pix').prop('required', true);
      }
    }

    $('#pixKeyType_pix').on('change', function() {
      const type = $(this).val();
      applyMask(type);
      $('#btnNext_pix').prop('disabled', !validateForm());
    });

    $('#pixKeyValue_pix').on('input', function() {
      $('#btnNext_pix').prop('disabled', !validateForm());
    });

    $('#pixTransferForm_pix').on('submit', function(e) {
      e.preventDefault();

      const type = $('#pixKeyType_pix').val();
      const key = $('#pixKeyValue_pix').val().trim();

      let valid = true;

      if (!type) {
        $('#pixKeyType_pix').addClass('is-invalid');
        $('#pixKeyType_pix').siblings('.invalid-feedback').show();
        valid = false;
      }

      if (type !== 'random' && key === '') {
        $('#pixKeyValue_pix').addClass('is-invalid');
        $('#pixKeyValue_pix').siblings('.invalid-feedback').show();
        valid = false;
      }

        

      nextStepPixTransfer();
    });

  $('#btnNext_pix').prop('disabled', true);
});

  function nextStepPixTransfer() {
      //Swich case para controlar os passos da transferência Pix
      switch (step_pix_transfer) {
          case 0:
              $.ajax({
                  url: '/get-pix-client',
                  method: 'POST',
                  data: {
                  type: $('#pixKeyType_pix').val(),
                  key: $('#pixKeyValue_pix').val().trim(),
                  },
                  success: function (response) {
                      if (response) {
                          $('#PixTransferClientName').text(response.account.user.name || 'N/A');
                          //Mascara de CPF para ocultar os dígitos
                          $('#PixTransferClientCpf').text((response.account.user.cpf || '').replace(/^(\d{3})\.(\d{3})\.(\d{3})-(\d{2})$/, '***.$2.$3-**'));

                          $('#pixFavoritesContainer').hide();
                          $('#PixTransferStep1').hide();
                          $('#PixTransferStep2').show();
                      } else {
                          swal("Erro", response.message || "Cliente não encontrado.", "error");
                      }
                  },
                  error: function () {
                      swal("Erro", "Erro ao buscar os dados do cliente Pix.", "error");
                  },
                  complete: function () {
                      $('#btnNext_pix').prop('disabled', false);
                      $('#btnNext_pix').text('Enviar');
                  }
              });
              $('#PixTransferStep1').hide();
              break;
          case 1:
            const value = $('#pixTransferValue').val().trim();
            const parsedValue = parseFloat(value.replace(/\./g, '').replace(',', '.'));

            if (!value || parsedValue <= 0) {
              $('#pixTransferValue').addClass('is-invalid');
              return;
            } else {
              $('#pixTransferValue').removeClass('is-invalid');
            }

            $('#btnNext_pix').prop('disabled', true).text('Processando...');

            $.ajax({
              url: '/pixTransfer',
              method: 'POST',
              data: {
                type: $('#pixKeyType_pix').val(),
                pix_key: $('#pixKeyValue_pix').val().trim(),
                amount: parsedValue,
                add_to_favorites: $('#addToFavorites').is(':checked')
              },
              success: function (response) {
                  resetInputPix();

                  $('#pixTranferModal').modal('hide');
                  step_pix_transfer = 0;
                  $('#PixTransferStep1').show();
                  $('#PixTransferStep2').hide();
                  $('#pixFavoritesContainer').hide();
                  $('#btnNext_pix').text('Próximo').prop('disabled', true);

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
                const msg = xhr.responseJSON?.message || "Erro ao realizar a transferência Pix.";
                $('#btnNext_pix').prop('disabled', false);
                $('#btnNext_pix').text('Enviar');
                $('#pixTransferValue').val('');
                step_pix_transfer = 1;
                swal.fire({
                  icon: 'error',
                  title: 'Erro',
                  text: msg,
                  customClass: {
                    popup: 'colored-toast'
                  }
                });
              }
            });
              break;
      }
      step_pix_transfer++;
    }

   function openFavorites() {
      $.ajax({
        url: '/get-pix-favorites',
        method: 'GET',
        success: function(response) {
          favorites = response;
          
          $('#pixFavoritesContainer').show();
          $('#PixTransferStep1').hide();
          $('#PixTransferStep2').hide();
          $('#btnNext_pix').prop('disabled', false);
          //Gerar as opcoes no select de favoritos
          const $select = $('#favoritsSelect');
          $select.empty();
          $select.append(`<option value="" selected disabled>Selecione</option>`);
          response.forEach(function(favorite) {
            $select.append(`<option value="${favorite.id}">${favorite.name} - ${favorite.cpf}</option>`);
          });
        },
        error: function() {
          Swal.fire("Erro", "Erro ao buscar os dados do cliente.", "error");
        }
      });
    }

    function selectedFavorite() {
      const selectedId = $('#favoritsSelect').val();
      if (selectedId) {
        //buscar o favorito selecionado da varaivel favorites
        const favorite = favorites.find(fav => fav.id == selectedId);
        if (favorite) {
          $('#pixKeyType_pix').val(favorite.pix_type);
          $('#pixKeyValue_pix').val(favorite.pix_key);


          step_pix_transfer = 1;
          nextStepPixTransfer();

          $('#PixTransferClientName').text(favorite.name || 'N/A');
          $('#PixTransferClientCpf').text((favorite.cpf || '').replace(/^(\d{3})\.(\d{3})\.(\d{3})-(\d{2})$/, '***.$2.$3-**'));
          $('#pixFavoritesContainer').hide();
          $('#PixTransferStep1').hide();
          $('#PixTransferStep2').show();
        }
      }
    }

    function resetInputPix() {
      $('#pixKeyValue_pix').val('').removeAttr('type').removeClass('is-invalid').unmask();
      $('#pixKeyValueContainer_pix').show();
      $('#btnNext_pix').prop('disabled', true);
      $('#pixKeyValue_pix').val('');
      $('#pixKeyType_pix').val('')
      $('#pixTransferValue').val('')
      step_pix_transfer = 0;
    }
</script>
