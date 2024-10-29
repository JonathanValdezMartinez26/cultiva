<?php

namespace Jobs\controllers;

include_once dirname(__DIR__) . "\..\Core\Job.php";
include_once dirname(__DIR__) . "\models\ConsultaUdiDolar.php";

use Core\Job;
use Jobs\models\ConsultaUdiDolar as DAO;

class ConsultaUdiDolar extends Job
{
    const API_KEY = '722947a253b50ca6aab1e6a7e82cd36c8a8d7b7fb5b97a9f836ee47cf373c8e7';

    public function __construct()
    {
        parent::__construct("ConsultaUdiDolar");
    }

    /**
     * Consulta el valor del dólar y del UDI para una fecha específica y los guarda en la base de datos.
     *
     * @return void
     */
    public function GetUDI_Dolar()
    {
        $dias = DAO::DiasFaltantes();

        if (!$dias['success']) return self::SaveLog(json_encode($dias));
        foreach ($dias['datos'] as $dia) {
            $fecha = $dia['DIA'];

            // Obtener el valor del dólar y del UDI para una fecha específica
            $valorDolar = $this->obtenerValorPorFecha("SF63528", "$fecha");
            $valorUDI = $this->obtenerValorPorFecha("SP68257", "$fecha");

            // Guardar los valores en la base de datos
            $resultado =  ($valorDolar != 0 || $valorUDI != 0) ?
                DAO::AddUdiDolar($fecha, $valorDolar, $valorUDI) :
                "No se pudieron obtener los valores.";

            // Guardar el resultado en el log
            self::SaveLog(json_encode($resultado, JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Obtiene el valor de una serie para una fecha específica.
     *
     * @param string $serie Serie a consultar.
     * @param string $fecha Fecha para la que se desea obtener el valor.
     * @return string Valor de la serie para la fecha especificada.
     */
    private function obtenerValorPorFecha($serie, $fecha)
    {
        // Formatear la fecha en el formato requerido por la API (YYYY-MM-DD)
        $fechaFormateada = date("Y-m-d", strtotime($fecha));

        // URL de la API del Banco de México para obtener el valor para una fecha específica
        $url = "https://www.banxico.org.mx/SieAPIRest/service/v1/series/$serie/datos/$fechaFormateada/$fechaFormateada?token=" . self::API_KEY;

        // Inicializar cURL
        $ch = curl_init();

        // Configurar cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Ejecutar cURL
        $response = curl_exec($ch);

        // Cerrar cURL
        curl_close($ch);

        // Decodificar la respuesta JSON
        $data = json_decode($response, true);
        // Verificar si la respuesta contiene los datos esperados
        if (!isset($data["bmx"]["series"][0]["datos"][0]["dato"])) return "0";
        return $data["bmx"]["series"][0]["datos"][0]["dato"];
    }
}

$dolar_udi = new ConsultaUdiDolar();
$dolar_udi->GetUDI_Dolar();
