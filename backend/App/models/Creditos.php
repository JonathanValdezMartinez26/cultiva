<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use Core\Database;
use Core\Model;

class Creditos extends Model
{

    public static function GetReporteReferencias($datos)
    {
        $qry = <<<SQL
            SELECT
                CODIGO_GPO AS CREDITO,
                NOMBRE_GPO AS GRUPO,
                NOM_SUCURSAL AS SUCURSAL,
                REF_
            FROM
                IMPCONT IC ON IC.CODIGO_GPO = PRC.CDGNS
            WHERE
                IC.CODIGO_EMP = 'EMPFIN'
                AND IC.TIPO_DOC = 'PAGO'
        SQL;

        $prm = [
            'fecha' => $datos['fecha']
        ];

        if (isset($datos['credito']) && $datos['credito'] !== '') {
            $qry .= ' AND IM.CODIGO_GPO :credito';
            $prm['credito'] = $datos['credito'];
        }

        try {
            $db = new Database();
            $res = $db->queryAll($qry, $prm);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }
}