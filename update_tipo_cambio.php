<?php
define('WP_USE_THEMES', false);
require('../wp-blog-header.php');
date_default_timezone_set('America/Lima');
class JobsWebservices
{
    public $PRECOR_URL = "https://tiendaenlinea.precor.pe/";
    public $MAXCO_URL = "https://maxco.pe/";
    function isMaxco($id_soc)
    {
        if ($id_soc == "EM01") {
            return true;
        } else if ($id_soc == "MA01") {
            return true;
        } else {
            return false;
        }
    }

    function isPrecor($id_soc)
    {
        if ($id_soc == "PR01") {
            return true;
        } else {
            return false;
        }
    }

    function getWPDB($id_soc)
    {
        if ($this->isMaxco($id_soc)) {
            /* maxco */
            return new wpdb('clg_wp_40wyu', 'z21*UxbE56ce0^xJ', 'clg_wp_my9nw', 'localhost:3306');
        } else if ($this->isPrecor($id_soc)) {
            /* precor */
            return new wpdb('clg_wp1','Q.MRIXVwjzFHnq6jeRx60','clg_wp1', 'localhost:3306');
        } else if ($id_soc == 999) {
            /* mi localhost */
            return new wpdb('root', '', 'maxcopunkuhr', 'localhost:3307');
        } else if ($id_soc == 1000) {
            return new wpdb("root", "root", "wordpress", "mysql");
        }
    }

    function getTiposCambioMaxcoPrecor(): array
    {
        $fecha_actual = date("Y-m-d");
        $sql = "SELECT * FROM wp_tipo_cambio WHERE DATE_FORMAT(created_at,'%Y-%m-%d') = '$fecha_actual' ORDER BY created_at DESC LIMIT 1";
        // $wdpbMaxco = $this->getWPDB("EM01");
        $wdpbPrecor = $this->getWPDB("PR01");
        // $resultMaxco = $wdpbMaxco->get_results($sql)[0];
        $resultPrecor = $wdpbPrecor->get_results($sql)[0];
        return [
            "precor" => $resultPrecor->tipo_cambio,
            // "maxco" => $resultMaxco->tipo_cambio
        ];
    }

    function updateTypeRateWebservice($urlDomain, $type_rate): bool
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlDomain . 'wp-json/webservices_precor/v1/update_currency_rate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{"user":"PRECOR","pass":"PRECOR2","rate":"' . $type_rate . '"}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        return $response["data"]["status"] == 200 ? true : false;
    }

    function executeJobUpdateTypeRate(): void
    {
        $tiposCambio = $this->getTiposCambioMaxcoPrecor();
        $tipoCambioPrecor = $tiposCambio["precor"];
        // $tipoCambioMaxco = $tiposCambio["maxco"];
        if ($tipoCambioPrecor != null) {
            $this->updateTypeRateWebservice($this->PRECOR_URL, $tipoCambioPrecor);
        }
        //  else if ($tipoCambioMaxco != null) {
        //     $this->updateTypeRateWebservice($this->MAXCO_URL, $tipoCambioMaxco);
        // }
        echo "Operacion realizada con exito";
        // print_r($tiposCambio);
    }
}
// instancia general
if ($_GET["exec"] == 1) {
    $job = new JobsWebservices();
    $job->executeJobUpdateTypeRate();
}
