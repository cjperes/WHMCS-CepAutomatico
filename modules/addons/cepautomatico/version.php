<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

$apelido = 'cepautomatico';
$notificacao = "1"; // 0 - Notificação por e-mail | 1 - Desligamento do módulo (die ou exit)
$debug = "0"; // 0 - Desligado | 1 - Ligado

function cepautomatico_check_license($licensekey, $localkey = '')
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
                    if (
                        !$responseCode
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

$licensekey = Capsule::table('tbladdonmodules')->select('value')->where('module', $apelido)->where('setting', 'licenca')->first()->value;

// Validação da "Local Key" no banco de dados

$resultadoLocalKey = Capsule::table('mod_cepautomatico')->select('localkey')->first()->localkey;

$localkey = $resultadoLocalKey;


// Validate the license key information
$results = cepautomatico_check_license($licensekey, $localkey);

if ($debug === "1") {

    // Raw output of results for debugging purpose
    echo '<textarea cols="100" rows="20">' . print_r($results, true) . '</textarea>';
}

function enviarEmail($status)
{
    $nomeModulo = "CEP Automático";

    $resultado = Capsule::table('tbladmins')->select('email')->get();

    $mensagem = "A licença do módulo $nomeModulo está $status e o mesmo não está funcionando. Entre em contato com nosso suporte técnico em pagliahost.com.br ou h1code.com.br";

    foreach ($resultado as $resultado_filtrado) {

        foreach ($resultado_filtrado as $colunas => $emails) {

            mail($emails, "A licença do módulo $nomeModulo está $status", $mensagem, "Content-type: text/html; charset=UTF-8" . "\r\n");
        }
    }
}

function inserirStatusDB_cepautomatico($numeroStatus)
{

    $rowStatus = count(Capsule::table('mod_cepautomatico_status')->get());

    if ($rowStatus < 1) {
        Capsule::table('mod_cepautomatico_status')->insert([
            'status' => $numeroStatus
        ]);
    } else {
        Capsule::table('mod_cepautomatico_status')->whereRaw(1)
            ->update([
                'status' => $numeroStatus
            ]);
    }
}

function updateOrCreaateModule($localkeydata, $update = false)
{
    if ($update) {
        Capsule::table('mod_cepautomatico')->whereRaw(1)
            ->update([
                'localkey' => $localkeydata
            ]);
    } else {
        Capsule::table('mod_cepautomatico')
            ->insert([
                'localkey' => $localkeydata
            ]);
    }
}
// Interpret response
switch ($results['status']) {
    case "Active":
        $localkeydata = $results['localkey'];
        $row = count($resultadoLocalKey);

        if ($row < 1) {
            updateOrCreaateModule($localkeydata);
        } else {
            updateOrCreaateModule($localkeydata, true);
        }

        inserirStatusDB_cepautomatico("1");

        break;
    case "Invalid":

        if ($notificacao === "0") {
            inserirStatusDB_cepautomatico("0");
            enviarEmail("Inválida");
        } else {
            inserirStatusDB_cepautomatico("0");

            die("A licença do módulo 'CEP Automático' está 'Invalida', desative-o ou entre em contato com o desenvolvedor!");
        }
        break;
    case "Expired":

        if ($notificacao === "0") {
            inserirStatusDB_cepautomatico("3");
            enviarEmail("Expirada");
        } else {
            inserirStatusDB_cepautomatico("3");
            die("A licença do módulo 'CEP Automático' está 'Expirada', desative-o ou entre em contato com o desenvolvedor!");
        }

        break;
    case "Suspended":

        if ($notificacao === "0") {
            inserirStatusDB_cepautomatico("2");
            enviarEmail("Suspensa");
        } else {
            inserirStatusDB_cepautomatico("2");

            die("A licença do módulo 'CEP Automático' está 'Suspensa', desative-o ou entre em contato com o desenvolvedor!");
        }

        break;
    default:
        die("Resposta inválida");
        break;
}
