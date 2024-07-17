<?php

namespace Jobs\controllers;

include 'C:/xampp/htdocs/cultiva/backend/Jobs/models/apu.php';

use \Jobs\models\apu as DAO;
use DateTime;
use DateTimeZone;

$validaHV = new DateTime('now', new DateTimeZone('America/Mexico_City'));
if ($validaHV->format('I')) date_default_timezone_set('America/Mazatlan');
else date_default_timezone_set('America/Mexico_City');

$aln = new Actualiza();
$aln->generaup();

class Actualiza
{
    public function SaveLog($tdatos)
    {
        $archivo = "C:/xampp/Logs Jobs/act.log";

        clearstatcache();
        if (file_exists($archivo) && filesize($archivo) > 10 * 1024 * 1024) { // 10 MB
            $nuevoNombre = "C:/xampp/Logs Jobs/act" . date('Ymd') . ".log";
            rename($archivo, $nuevoNombre);
        }

        $log = fopen($archivo, "a");

        $infoReg = date("Y-m-d H:i:s") . " - job_fnc: " . debug_backtrace()[1]['function'] . " -> " . $tdatos;

        fwrite($log, $infoReg . PHP_EOL);
        fclose($log);
    }

    public function generaup()
    {
        $lista = DAO::GetLista();
        if ($lista == null) return;

        $in = DAO::Actualiza($lista);
        self::SaveLog(json_encode($in));
    }
}
