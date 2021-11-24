<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

function cepautomatico_config()
{
    return array(
        'name' => 'CEP Automático',
        'description' => 'Módulo de CEP Automático WHMCS - Projeto Open Source: <a href="https://github.com/agenciah1code/WHMCS-CepAutomatico">https://github.com/agenciah1code/WHMCS-CepAutomatico</a>',
        'author' => "<a href='https://h1code.com.br' target='_blank'><img src='https://i.imgur.com/2NmHjxv.png' height='50'/></a>",
        'version' => '1.8',
    );
}

function cepautomatico_activate()
{
    return array('status' => 'success', 'description' => 'O módulo CEP Automático foi instalado com sucesso.');
    return array('status' => 'error', 'description' => 'Erro ao instalar o módulo CEP Automático.');
}

function cepautomatico_deactivate()
{
    return array('status' => 'success', 'description' => 'O módulo CEP Automático foi desinstalado com sucesso.');
    return array('status' => 'error', 'description' => 'Erro ao desinstalar o módulo CEP Automático.');
}
