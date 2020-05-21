<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

function cepautomatico_config()
{
    // Verificação do status da licença

    $pdo = Capsule::connection()->getPdo();

    try {

        $statement = $pdo->query("SELECT * FROM `mod_cepautomatico_status` LIMIT 1");

        $statement->execute();

        $resultado = $statement->execute();
        
        $resultado = $statement->fetch(PDO::FETCH_ASSOC);

        $status = $resultado['status'];

        switch ($status) {
            case '0':
                $resultado['status'] = "danger";
                $statusMensagem = "Inválida";
                break;

            case '1':
                $resultado['status'] = "success";
                $statusMensagem = "Ativa";
                break;

            case '2':
                $resultado['status'] = "warning";
                $statusMensagem = "Suspensa";
                break;

            case '3':
                $resultado['status'] = "default";
                $statusMensagem = "Expirada";
                break;

            default:
                $resultado['status'] = "info";
                $statusMensagem = "Será verificada na próxima utilização do módulo";

                break;
        }

    } catch (\Exception $e) {
        // echo "Uh oh! {$e->getMessage()}";
    }

    return array(
        'name' => 'CEP Automático', // Display name for your module
        'description' => 'Módulo de CEP Automático nos formulários de cadastro.', // Description displayed within the admin interface
        'author' => "<a href='https://h1code.com.br' target='_blank'><img src='https://i.imgur.com/2NmHjxv.png' height='50'/></a>", // Module author name
        'version' => '1.2', // Version number
        'fields' => array(
            // a text field type allows for single line text input
            'licenca' => array(
                'FriendlyName' => 'Licença',
                'Type' => 'text',
                'Size' => '30',
                'Description' => 'Digite a licença do seu módulo.',
            ),

            "licensestatus" => array(
                'FriendlyName' => "Status da Licença",
                'Description' => "<span class='label label-" . $resultado['status'] . "'>&nbsp;" . $statusMensagem . "&nbsp;</span>"
            ),
        ),
    );

}

function cepautomatico_activate()
{

    $pdo = Capsule::connection()->getPdo();
    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare("CREATE TABLE IF NOT EXISTS `mod_cepautomatico` ( `localkey` TEXT NOT NULL ) ENGINE = InnoDB;");

        $statement->execute();

        $statement = $pdo->prepare("CREATE TABLE IF NOT EXISTS `mod_cepautomatico_status` ( `status` TINYINT NOT NULL ) ENGINE = InnoDB;");

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
        $statement = $pdo->prepare("DROP TABLE IF EXISTS `mod_cepautomatico`");

        $statement->execute();

        $statement = $pdo->prepare("DROP TABLE IF EXISTS `mod_cepautomatico_status`");

        $statement->execute();

        $pdo->commit();
    } catch (\Exception $e) {
        echo "Uh oh! {$e->getMessage()}";
        $pdo->rollBack();
    }

    return array('status' => 'success', 'description' => 'O módulo CEP Automático foi desinstalado com sucesso.');
    return array('status' => 'error', 'description' => 'Erro ao desinstalar o módulo CEP Automático.');
}

function cepautomatico_upgrade($vars)
{

    $versao = $vars['version'];

    # Update da versão 1.0 para 1.1
    // if ($versao < 1.1) {

    //     $pdo = Capsule::connection()->getPdo();

    //     try {
    //         $statement = $pdo->query("");

    //         $statement->execute();

    //     } catch (\Exception $e) {
    //         echo "Uh oh! {$e->getMessage()}";
    //     }

    // }

}
