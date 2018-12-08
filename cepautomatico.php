<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function cepautomatico_config()
{
    return array(
        'name' => 'CEP Automático', // Display name for your module
        'description' => 'Módulo de CEP Automático.', // Description displayed within the admin interface
        'author' => 'H1 Code', // Module author name
        'version' => '1.0', // Version number
        // 'fields' => array(
        //     // a text field type allows for single line text input
        //     'Licenca' => array(
        //         'FriendlyName' => 'Licença',
        //         'Type' => 'text',
        //         'Size' => '30',
        //         // 'Default' => 'Default value',
        //         'Description' => 'Insira aqui sua chave de licença.',
        //     ),
        // ),
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
