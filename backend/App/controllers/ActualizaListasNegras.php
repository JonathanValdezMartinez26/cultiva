<?php

namespace App\controllers;

include 'C:/xampp/htdocs/cultiva/backend/App/models/ActualizaListasNegras.php';

use \App\models\ActualizaListasNegras as DAO;
use DateTime;
use DateTimeZone;

$validaHV = new DateTime('now', new DateTimeZone('America/Mexico_City'));
if ($validaHV->format('I')) date_default_timezone_set('America/Mazatlan');
else date_default_timezone_set('America/Mexico_City');

$aln = new ActualizaListasNegras();
$aln->MCMaCultiva();

class ActualizaListasNegras
{
    public function SaveLog($tdatos)
    {
        $archivo = "C:/xampp/ListasNegras.log";

        clearstatcache();
        if (file_exists($archivo) && filesize($archivo) > 10 * 1024 * 1024) { // 10 MB
            $nuevoNombre = "C:/xampp/ListasNegras" . date('Ymd') . ".log";
            rename($archivo, $nuevoNombre);
        }

        $log = fopen($archivo, "a");

        $infoReg = date("Y-m-d H:i:s") . " - job_fnc: " . debug_backtrace()[1]['function'] . " -> " . $tdatos;

        fwrite($log, $infoReg . PHP_EOL);
        fclose($log);
    }

    public function MCMaCultiva()
    {
        $listaMCM = DAO::GetListaNegraMCM();
        if ($listaMCM == null) return self::SaveLog("No se pudo obtener la lista de MCM");
        if (count($listaMCM) == 0) return self::SaveLog("No hay registros en la lista de MCM");

        $resultado = DAO::ValidaListaNegraCultiva($listaMCM);
        if ($resultado == null) return self::SaveLog("No se pudo obtener la lista de Cultiva");
        if (count($resultado) == 0) return self::SaveLog("No hay registros en la lista de Cultiva");

        $insertados = DAO::InsertaListaNegraCultiva($resultado);
        if ($insertados == null) return self::SaveLog("No se pudo insertar la lista en Cultiva");

        self::SaveLog("Registros en MCM: " . count($listaMCM) . " - Registros insertados en cultiva: " . count($resultado));
    }
}
