$(document).ready(function () {
    if ($('#inputCountry, #country').val() == 'BR') {
        $('#inputAddress1, label[for=inputAddress1], .fa-building-o, #address1, label[for=address1]').hide();
        $('#inputAddress2, label[for=inputAddress2], .fa-map-marker, #address2, label[for=address2]').hide();
        $('#inputCity, label[for=inputCity], .fa-building-o, #city, label[for=city]').hide();
        $('#stateselect, label[for=inputState], .fa-map-signs, label[for=state]').hide();
    }
    // $('label[for=address]').hide();
    function limpa_formulário_cep() {
        // Limpa valores do formulário de cep.
        $('#inputAddress1').val('');
        $('#inputAddress2').val('');
        $('#inputCity').val('');
        $('#stateselect').val('');
        $('#inputAddress1, label[for=inputAddress1], .fa-building-o, #address1, label[for=address1]').hide();
        $('#inputAddress2, label[for=inputAddress2], .fa-map-marker, #address2, label[for=address2]').hide();
        $('#inputCity, label[for=inputCity], .fa-building-o, #city, label[for=city]').hide();
        $('#stateselect, label[for=inputState], .fa-map-signs, label[for=state]').hide();
    }
    //Quando o campo cep perde o foco.
    $('#inputPostcode, #postcode').blur(function () {
        //Nova variável 'cep' somente com dígitos.
        var cep = $(this).val().replace(/\D/g, '');
        //Verifica se campo cep possui valor informado.
        if (cep != '') {
            //Expressão regular para validar o CEP.
            var validacep = /^[0-9]{8}$/;
            //Valida o formato do CEP.
            if (validacep.test(cep)) {
                //Preenche os campos com '...' enquanto consulta webservice.
                $('#address1').val('...');
                $('#inputAddress2, #address2').val('...');
                $('#inputCity, #city').val('...');
                $('#stateselect').val('...');
                //Consulta o webservice viacep.com.br/
                $.getJSON('https://viacep.com.br/ws/' + cep + '/json/?callback=?', function (dados) {
                    if (!('erro' in dados)) {
                        //Atualiza os campos com os valores da consulta.
                        $('#inputAddress1, #address1').val(dados.logradouro);
                        $('#inputAddress2, #address2').val(dados.bairro);
                        $('#inputCity, #city').val(dados.localidade);
                        $('#stateselect').val(dados.uf);
                        $('#inputAddress1, label[for=inputAddress1], .fa-building-o, #address1, label[for=address1]').show();
                        $('#inputAddress2, label[for=inputAddress2], .fa-map-marker, #address2, label[for=address2]').show();
                        $('#inputCity, label[for=inputCity], .fa-building-o, #city, label[for=city]').show();
                        $('#stateselect, label[for=inputState], .fa-map-signs, label[for=state]').show();
                    } //end if.
                    else {
                        //CEP pesquisado não foi encontrado.
                        limpa_formulário_cep();
                        alert('CEP não encontrado.');
                    }
                });
            } //end if.
            else {
                //cep é inválido.
                limpa_formulário_cep();
                alert('Formato de CEP inválido.');
            }
        } //end if.
        else {
            //cep sem valor, limpa formulário.
            limpa_formulário_cep();
        }
    });
});