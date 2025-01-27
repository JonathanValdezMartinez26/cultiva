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
                IC.CODIGO_GPO AS CREDITO,
                IC.NOMBRE_GPO AS GRUPO,
                IC.NOM_SUCURSAL AS SUCURSAL,
                IC.PAGO_PARCIAL_LETRA AS REF_PAGO_OXXO,
                IC.REF_PAYCASH AS REF_PAGO_PAYCASH,
                IC.NOM_GERENTE_SUC AS REF_PAGO_BANCOPPEL,
                IC.CUENTA_BANCARIA AS REF_COMISION_OXXO,
                IC.NOM_ASESOR  AS REF_COMISION_PAYCASH,
                IC.SUPERVISOR AS REF_COMISION_BANCOPPEL
            FROM
                IMPCONT IC
            WHERE
                IC.CODIGO_EMP = 'EMPFIN'
                AND IC.TIPO_DOC = 'PAGO'
        SQL;

        if (isset($datos['credito']) && $datos['credito'] !== '') {
            $qry .= ' AND IC.CODIGO_GPO = :credito';
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
