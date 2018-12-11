<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

function cepautomatico_config()
{
    return array(
        'name' => 'CEP Automático', // Display name for your module
        'description' => 'Módulo de CEP Automático na realização de novos cadastros.', // Description displayed within the admin interface
        'author' => "<a href='https://h1code.com.br' target='_blank'><img src='https://i.imgur.com/2NmHjxv.png' height='50'/></a>", // Module author name
        'version' => '1.0', // Version number
        'fields' => array(
            // a text field type allows for single line text input
            'licenca' => array(
                'FriendlyName' => 'Licença',
                'Type' => 'text',
                'Size' => '30',
                'Description' => 'Digite a licença do seu módulo.',
            ),
        ),
    );

}

function cepautomatico_activate()
{

    $pdo = Capsule::connection()->getPdo();
    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare("CREATE TABLE IF NOT EXISTS `mod_cepautomatico` ( `localkey` TEXT NOT NULL ) ENGINE = MyISAM;");

        $statement->execute();

        $pdo->commit();
    } catch (\Exception $e) {
        echo "Uh oh! {$e->getMessage()}";
        $pdo->rollBack();
    }

    return array('status' => 'success', 'description' => 'O módulo CEP Automático foi instalado com sucesso.');
    return array('status' => 'error', 'description' => 'Erro ao instalar o módulo CEP Automático.');
}

function cepautomatico_deactivate()
{

    $pdo = Capsule::connection()->getPdo();
    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare("DROP TABLE `mod_cepautomatico`");

        $statement->execute();

        $pdo->commit();
    } catch (\Exception $e) {
        echo "Uh oh! {$e->getMessage()}";
        $pdo->rollBack();
    }

    return array('status' => 'success', 'description' => 'O módulo CEP Automático foi desinstalado com sucesso.');
    return array('status' => 'error', 'description' => 'Erro ao desinstalar o módulo CEP Automático.');
}
