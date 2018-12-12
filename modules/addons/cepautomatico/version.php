<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

$apelido = 'cepautomatico';
$notificacao = "1"; // 0 - Notificação por e-mail | 1 - Desligamento do módulo (die ou exit)
$debug = "0"; // 0 - Desligado | 1 - Ligado

function pagliahost_check_license($licensekey, $localkey = '')
{

    // -----------------------------------
    //  -- Configuration Values --
    // -----------------------------------

    // Enter the url to your WHMCS installation here
    $whmcsurl = 'https://pagliahost.com.br/cliente/';
    // Must match what is specified in the MD5 Hash Verification field
    // of the licensing product that will be used with this check.
    $licensing_secret_key = 'b4f5ce5f15ee985e946e2836c0e2c150';
    // The number of days to wait between performing remote license checks
    $localkeydays = 7;
    // The number of days to allow failover for after local key expiry
    $allowcheckfaildays = 5;

    // -----------------------------------
    //  -- Do not edit below this line --
    // -----------------------------------

    $check_token = time() . md5(mt_rand(1000000000, 9999999999) . $licensekey);
    $checkdate = date("Ymd");
    $domain = $_SERVER['SERVER_NAME'];
    $usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
    $dirpath = dirname(__FILE__);
    $verifyfilepath = 'modules/servers/ph_licenciamento/verificar.php';
    $localkeyvalid = false;
    if ($localkey) {
        $localkey = str_replace("\n", '', $localkey); # Remove the line breaks
        $localdata = substr($localkey, 0, strlen($localkey) - 32); # Extract License Data
        $md5hash = substr($localkey, strlen($localkey) - 32); # Extract MD5 Hash
        if ($md5hash == md5($localdata . $licensing_secret_key)) {
            $localdata = strrev($localdata); # Reverse the string
            $md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
            $localdata = substr($localdata, 32); # Extract License Data
            $localdata = base64_decode($localdata);
            $localkeyresults = unserialize($localdata);
            $originalcheckdate = $localkeyresults['checkdate'];
            if ($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
                $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
                if ($originalcheckdate > $localexpiry) {
                    $localkeyvalid = true;
                    $results = $localkeyresults;
                    $validdomains = explode(',', $results['validdomain']);
                    if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
                        $localkeyvalid = false;
                        $localkeyresults['status'] = "Invalid";
                        $results = array();
                    }
                    $validips = explode(',', $results['validip']);
                    if (!in_array($usersip, $validips)) {
                        $localkeyvalid = false;
                        $localkeyresults['status'] = "Invalid";
                        $results = array();
                    }
                    $validdirs = explode(',', $results['validdirectory']);
                    if (!in_array($dirpath, $validdirs)) {
                        $localkeyvalid = false;
                        $localkeyresults['status'] = "Invalid";
                        $results = array();
                    }
                }
            }
        }
    }
    if (!$localkeyvalid) {
        $responseCode = 0;
        $postfields = array(
            'licensekey' => $licensekey,
            'domain' => $domain,
            'ip' => $usersip,
            'dir' => $dirpath,
        );
        if ($check_token) {
            $postfields['check_token'] = $check_token;
        }

        $query_string = '';
        foreach ($postfields as $k => $v) {
            $query_string .= $k . '=' . urlencode($v) . '&';
        }
        if (function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $whmcsurl . $verifyfilepath);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        } else {
            $responseCodePattern = '/^HTTP\/\d+\.\d+\s+(\d+)/';
            $fp = @fsockopen($whmcsurl, 80, $errno, $errstr, 5);
            if ($fp) {
                $newlinefeed = "\r\n";
                $header = "POST " . $whmcsurl . $verifyfilepath . " HTTP/1.0" . $newlinefeed;
                $header .= "Host: " . $whmcsurl . $newlinefeed;
                $header .= "Content-type: application/x-www-form-urlencoded" . $newlinefeed;
                $header .= "Content-length: " . @strlen($query_string) . $newlinefeed;
                $header .= "Connection: close" . $newlinefeed . $newlinefeed;
                $header .= $query_string;
                $data = $line = '';
                @stream_set_timeout($fp, 20);
                @fputs($fp, $header);
                $status = @socket_get_status($fp);
                while (!@feof($fp) && $status) {
                    $line = @fgets($fp, 1024);
                    $patternMatches = array();
                    if (!$responseCode
                        && preg_match($responseCodePattern, trim($line), $patternMatches)
                    ) {
                        $responseCode = (empty($patternMatches[1])) ? 0 : $patternMatches[1];
                    }
                    $data .= $line;
                    $status = @socket_get_status($fp);
                }
                @fclose($fp);
            }
        }
        if ($responseCode != 200) {
            $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
            if ($originalcheckdate > $localexpiry) {
                $results = $localkeyresults;
            } else {
                $results = array();
                $results['status'] = "Invalid";
                $results['description'] = "Remote Check Failed";
                return $results;
            }
        } else {
            preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
            $results = array();
            foreach ($matches[1] as $k => $v) {
                $results[$v] = $matches[2][$k];
            }
        }
        if (!is_array($results)) {
            die("Invalid License Server Response");
        }
        if ($results['md5hash']) {
            if ($results['md5hash'] != md5($licensing_secret_key . $check_token)) {
                $results['status'] = "Invalid";
                $results['description'] = "MD5 Checksum Verification Failed";
                return $results;
            }
        }
        if ($results['status'] == "Active") {
            $results['checkdate'] = $checkdate;
            $data_encoded = serialize($results);
            $data_encoded = base64_encode($data_encoded);
            $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
            $data_encoded = strrev($data_encoded);
            $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
            $data_encoded = wordwrap($data_encoded, 80, "\n", true);
            $results['localkey'] = $data_encoded;
        }
        $results['remotecheck'] = true;
    }
    unset($postfields, $data, $matches, $whmcsurl, $licensing_secret_key, $checkdate, $usersip, $localkeydays, $allowcheckfaildays, $md5hash);
    return $results;
}

// Obtém a chave de licença e a chave local do armazenamento
// Estes são normalmente armazenados em arquivos simples ou em um banco de dados SQL

// Pegar licença no banco de dados e validar

$pdo = Capsule::connection()->getPdo();

$statement = $pdo->prepare("SELECT `value` FROM `tbladdonmodules` WHERE `module` = :APELIDO AND `setting` = 'licenca'");

$statement->bindParam(':APELIDO', $apelido);

$statement->execute();

$resultado = $statement->fetch(PDO::FETCH_ASSOC);

$licensekey = $resultado['value'];

// Validação da "Local Key" no banco de dados

$statement = $pdo->query("SELECT `localkey` FROM `mod_cepautomatico` WHERE 1");

$statement->execute();

$resultadoLocalKey = $statement->fetch(PDO::FETCH_ASSOC);

$localkey = $resultadoLocalKey['localkey'];

// Validate the license key information
$results = pagliahost_check_license($licensekey, $localkey);

if ($debug === "1") {

    // Raw output of results for debugging purpose
    echo '<textarea cols="100" rows="20">' . print_r($results, true) . '</textarea>';

}

function enviarEmail($status)
{
    $nomeModulo = "CEP Automático";

    $pdo = Capsule::connection()->getPdo();

    $statement = $pdo->query("SELECT `email` FROM `tbladmins` WHERE 1");

    $statement->execute();

    $resultado = $statement->fetchAll(PDO::FETCH_ASSOC);

    $mensagem = "A licença do módulo $nomeModulo está $status e o mesmo não está funcionando. Entre em contato com nosso suporte técnico em pagliahost.com.br ou h1code.com.br";

    foreach ($resultado as $resultado_filtrado) {

        foreach ($resultado_filtrado as $colunas => $emails) {

            mail($emails, "A licença do módulo $nomeModulo está $status", $mensagem, "Content-type: text/html; charset=UTF-8" . "\r\n");

        }

    }

}

function inserirStatusDB($numeroStatus)
{
    $pdo = Capsule::connection()->getPdo();

    $statement = $pdo->query("SELECT * FROM `mod_cepautomatico_status` WHERE 1");

    $statement->execute();

    $rowStatus = $statement->rowCount();

    if ($rowStatus < 1) {

        $statement = $pdo->prepare("INSERT INTO `mod_cepautomatico_status`(`status`) VALUE (:STATUS)");

        $statement->bindParam(':STATUS', $numeroStatus);

        $statement->execute();

    } else {

        $statement = $pdo->prepare("UPDATE `mod_cepautomatico_status` SET `status`=:STATUS WHERE 1");

        $statement->bindParam(':STATUS', $numeroStatus);

        $statement->execute();
    }

}

// Interpret response
switch ($results['status']) {
    case "Active":

        $localkeydata = $results['localkey'];

        $statement = $pdo->prepare("SELECT * FROM `mod_cepautomatico` WHERE 1");

        $statement->execute();

        $resultadoUpdate = $statement->fetch(PDO::FETCH_ASSOC);

        $row = $statement->rowCount();

        if ($row < 1) {

            $statement = $pdo->prepare("INSERT INTO `mod_cepautomatico` (localkey) VALUES (:LOCALKEY)");

            $statement->bindParam(':LOCALKEY', $localkeydata);

            $statement->execute();

        } else {

            $statement = $pdo->prepare("UPDATE `mod_cepautomatico` SET `localkey`= :NOVALOCALKEY WHERE 1");

            $statement->bindParam(':NOVALOCALKEY', $localkeydata);

            $statement->execute();

        }

        inserirStatusDB("1");

        break;
    case "Invalid":

        if ($notificacao === "0") {
            inserirStatusDB("0");
            enviarEmail("Inválida");
        } else {
            inserirStatusDB("0");

            die("Licença inválida!");
        }
        break;
    case "Expired":

        if ($notificacao === "0") {
            inserirStatusDB("3");
            enviarEmail("Expirada");
        } else {
            inserirStatusDB("3");
            die("Licença Expirada!");
        }

        break;
    case "Suspended":

        if ($notificacao === "0") {
            inserirStatusDB("2");
            enviarEmail("Suspensa");
        } else {
            inserirStatusDB("2");

            die("Licença Suspensa!");
        }

        break;
    default:
        die("Resposta inválida");
        break;
}
