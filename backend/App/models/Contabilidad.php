<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use Core\Database;
use Core\Model;

class Contabilidad extends Model
{
    public static function BuscaGrupo($datos)
    {
        $qry = <<<SQL
            SELECT
                PRC.CDGNS AS GRUPO,
                NS.NOMBRE AS NOMBRE_GRUPO,
                PRC.CDGCL AS CLIENTE,
                (PRC.CANTENTRE - NVL(SC.COMISION, 0)) AS PRESTAMO,
                NVL(SC.COMISION, 0) AS SEGURO_FINANCIADO,
                PRC.CANTENTRE AS TOTAL_CREDITO,
                (PRC.CANTENTRE - NVL(SC.COMISION, 0)) * .1 AS GARANTIA,
                TO_CHAR(PRC.SOLICITUD, 'DD/MM/YYYY') AS FECHA_INICIO
            FROM
                PRC
                INNER JOIN SC ON SC.CDGCL = PRC.CDGCL AND SC.CDGNS = PRC.CDGNS AND SC.CICLO = PRC.CICLO AND SC.SITUACION = 'A'
                INNER JOIN NS ON NS.CODIGO = PRC.CDGNS
            WHERE
                PRC.CDGNS = :grupo
                AND PRC.CICLO = :ciclo
            ORDER BY
                PRC.SOLICITUD ASC
        SQL;

        $prm = [
            'grupo' => $datos['grupo'],
            'ciclo' => $datos['ciclo']
        ];

        try {
            $db = new Database();
            $res = $db->queryAll($qry, $prm);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }
}
