<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}


add_hook("ClientAreaFooterOutput", 1, function ($vars) {


    if ($vars["filename"] == "register" || $vars["filename"] == "cart") {
        require_once 'version.php';

        $javascript = "
<script>
$(document).ready(function(){
    if($('#inputCountry').val() == 'BR'){
        var campos = ['inputAddress1','inputAddress2','inputCity','stateinput','stateselect'];
        for(i = 0; i < campos.length; i++){
			$('#'+campos[i]).css('display', 'none');
            $('#'+campos[i]).prev().css('display', 'none');
        }
        $('#inputPostcode').change(function(event){
                $.get('//ddd.pricez.com.br/cep/'+$('#inputPostcode').val()+'.json', function(data) {
        			data.payload.logradouro != null ? $('#'+campos[0]).val(data.payload.logradouro) : $('#'+campos[0]).addClass('cep-erro');
        			data.payload.bairro != null ? $('#'+campos[1]).val(data.payload.bairro) : $('#'+campos[1]).addClass('cep-erro');
        			data.payload.cidade != null ? $('#'+campos[2]).val(data.payload.cidade) : $('#'+campos[2]).addClass('cep-erro');
        			data.payload.estado != null ? $('#'+campos[4]).val(data.payload.estado) : $('#'+campos[3]).addClass('cep-erro');
        			if(data.payload.estado != null){
                        for(i = 0; i < campos.length; i++){
                            if(campos[i] != 'stateinput'){
								$('#'+campos[i]).css('display', 'block');
								$('.field-icon').css('display', 'block');
							}
                        }
        			}
        		}).fail(function() {
        			alert('Ocorreu um erro ao buscar seu CEP. Tente novamente.');
        		});
        });
    }
});
</script>";

        return $javascript;
    }
});
