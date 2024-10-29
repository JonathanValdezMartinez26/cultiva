<?php

namespace Jobs\models;

include_once dirname(__DIR__) . "\..\Core\Model.php";
include_once dirname(__DIR__) . "\..\Core\Database.php";

use Core\Model;
use Core\Database;

class ConsultaUdiDolar extends Model
{
    public static function DiasFaltantes($fecha = null)
    {
        $fecha = $fecha ?? "2024-10-01";

        $qry = <<<SQL
            WITH FECHAS AS (
                SELECT
                    TO_DATE('$fecha', 'YYYY-MM-DD') + LEVEL AS DIA
                FROM
                    DUAL
                CONNECT BY LEVEL <= (SYSDATE - TO_DATE('$fecha', 'YYYY-MM-DD'))
            )
            SELECT
                TO_CHAR(DIA, 'YYYY-MM-DD') AS DIA
            FROM
                FECHAS
            LEFT JOIN UNIDAD UN ON FECHAS.DIA = UN.FECHA_CALC
            WHERE
                UN.FECHA_CALC IS NULL
            ORDER BY 
                FECHAS.DIA
        SQL;

        $db = new Database();
        if ($db->db_activa == null) return self::Responde(false, null, "Error al conectar a la base de datos.", null, ['query' => $qry]);

        $res = $db->queryAll($qry);
        return self::Responde(true, "Días faltantes obtenidos correctamente.", $res);
    }

    public static function AddUdiDolar($fecha, $dolar, $udi)
    {
        $db = new Database();
        if ($db->db_activa == null) return self::Responde(false, null, "Error al conectar a la base de datos.");
        $ret_dolar = "No se recibió valor para el dolar.";
        $ret_udi = "No se recibió valor para la UDI.";

        if ($dolar != 0) {
            $query_dolar = <<<SQL
                INSERT INTO
                    UNIDAD (
                        CODIGO,
                        DESCRIPCION,
                        VALOR,
                        FECHA_CALC,
                        ABREV,
                        CDGEM
                    )
                VALUES
                    (
                        'USD',
                        'MX: $dolar MXN = 1 USD $fecha BM Para pagos',
                        $dolar,
                        TIMESTAMP '$fecha 00:00:00.000000',
                        'USD',
                        'EMPFIN'
                    )
            SQL;

            $res = $db->insert($query_dolar);
            if ($res === true) $ret_dolar = self::Responde(true, "Dolar registrado correctamente.", ['query' => $query_dolar]);
            else $ret_dolar = self::Responde(false, "Error al registrar el dolar.", ['query' => $query_dolar], $res);
        }

        if ($udi != 0) {
            $query_udi = <<<SQL
                INSERT INTO
                    UNIDAD (
                        CODIGO,
                        DESCRIPCION,
                        VALOR,
                        FECHA_CALC,
                        ABREV,
                        CDGEM
                    )
                VALUES
                (
                    'UDI',
                    'MX: $udi UDIS $fecha BM',
                    $udi,
                    TIMESTAMP '$fecha 00:00:00.000000',
                    'UDI',
                    'EMPFIN'
                )
            SQL;

            $res = $db->insert($query_udi);
            if ($res === true) $ret_udi = self::Responde(true, "UDI registrada correctamente.", ['query' => $query_udi]);
            else $ret_udi = self::Responde(false, "Error al registrar la UDI.", ['query' => $query_dolar], $res);
        }

        return [$ret_dolar, $ret_udi];
    }
}
