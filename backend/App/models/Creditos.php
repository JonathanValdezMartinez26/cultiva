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
                DT.CREDITO,
                NS.NOMBRE AS GRUPO,
                DT.SUCURSAL,
                DT.REGION,
                PPR.REF_PAYCASH AS REF_PAGO_PAYCASH,
                DT.PAGO_BANCOPPEL || FN_DV(DT.PAGO_BANCOPPEL) AS REF_PAGO_BANCOPPEL,
                DT.PAGO_OXXO || FN_DVREV(DT.PAGO_OXXO) AS REF_PAGO_OXXO,
                CPR.REF_PAYCASH AS REF_COMISION_PAYCASH,
                DT.COMISION_BANCOPPEL || FN_DV(DT.COMISION_BANCOPPEL) AS REF_COMISION_BANCOPPEL,
                DT.COMISION_OXXO || FN_DVREV(DT.COMISION_OXXO) AS REF_COMISION_OXXO
            FROM
                (SELECT
                    PRN.CDGNS AS CREDITO,
                    PRN.CDGTPC,
                    CO.CODIGO || '-' || CO.NOMBRE AS SUCURSAL,
                    RG.CODIGO || '-' || RG.NOMBRE AS REGION,
                    'P' || PRN.CDGNS || PRN.CDGTPC AS PAGO_BANCOPPEL,
                    '1100000000001' || PRN.CDGNS || PRN.CDGTPC AS PAGO_OXXO,
                    '0' || PRN.CDGNS || PRN.CDGTPC AS COMISION_BANCOPPEL,
                    '1100000000000' || PRN.CDGNS || PRN.CDGTPC AS COMISION_OXXO
                FROM
                    PRN
                    JOIN CO ON CO.CODIGO = PRN.CDGCO
                    JOIN RG ON RG.CODIGO = CO.CDGRG 
                WHERE
                    PRN.CDGEM = 'EMPFIN'
                    FILTROS
                GROUP BY 
                    PRN.CDGNS,
                    PRN.CDGTPC,
                    CO.CODIGO,
                    CO.NOMBRE,
                    RG.CODIGO,
                    RG.NOMBRE) DT
            JOIN PAYCASH_REF PPR
                ON PPR.CDGEM = 'EMPFIN'
                AND PPR.CDGCLNS = DT.CREDITO
                AND PPR.CDGTPC = DT.CDGTPC
                AND PPR.TIPO = '1'
            JOIN PAYCASH_REF CPR
                ON CPR.CDGEM = 'EMPFIN'
                AND CPR.CDGCLNS = DT.CREDITO
                AND CPR.CDGTPC = DT.CDGTPC
                AND CPR.TIPO = '0'
            JOIN NS ON NS.CDGEM = 'EMPFIN' AND NS.CODIGO = DT.CREDITO
        SQL;

        $filtros = '';
        if (isset($datos['credito']) && $datos['credito'] !== '') {
            $filtros .= ' AND PRN.CDGNS = :credito';
            $prm['credito'] = $datos['credito'];
        }

        if ($filtros == '') $filtros = " AND PRN.SITUACION = 'E'";
        $qry = str_replace('FILTROS', $filtros, $qry);

        try {
            $db = new Database();
            $res = $db->queryAll($qry, $prm);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }
}
