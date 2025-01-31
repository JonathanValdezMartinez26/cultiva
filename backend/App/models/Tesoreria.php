<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use Core\Database;
use Core\Model;

class Tesoreria extends Model
{
    public static function ConsultaGruposCultiva($datos)
    {
        $qry = <<<SQL
            SELECT
                CO.NOMBRE AS SUCURSAL,
                SC.CDGNS,
                NS.NOMBRE as NOMBRE_GRUPO,
                SC.CICLO,
                CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS CLIENTE,
                CL.CALLE AS DOMICILIO,
                TO_CHAR(SC.SOLICITUD, 'DD/MM/YYYY HH24:MI:SS') AS SOLICITUD,
                NS.CODIGO AS CDGNS
            FROM
                SC
                INNER JOIN NS ON NS.CODIGO = SC.CDGNS
                INNER JOIN CL ON CL.CODIGO = SC.CDGCL
                INNER JOIN CO ON CO.CODIGO = NS.CDGCO
            WHERE
                SC.SOLICITUD BETWEEN TO_DATE(:inicio, 'YY-mm-dd') AND TO_DATE(:fin, 'YY-mm-dd')
            ORDER BY
                SC.SOLICITUD ASC
        SQL;

        $prm = [
            'inicio' => $datos['fechaI'],
            'fin' => $datos['fechaF']
        ];

        try {
            $db = new Database();
            $res = $db->queryAll($qry, $prm);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }

    public static function ReingresarClientesCredito($credito)
    {
        $query = <<<SQL
            SELECT
                CDGNS,
                CDGCL,
                NOMBRE_CLIENTE,
                INICIO,
                FECHA_BAJA,
                FECHA_BAJA_REAL,
                CODIGO_MOTIVO,
                MOTIVO_BAJA
            FROM
                (
                    SELECT
                        CDGNS,
                        CDGCL,
                        (
                            NOMBRE1 || ' ' || NOMBRE2 || ' ' || PRIMAPE || ' ' || SEGAPE
                        ) AS NOMBRE_CLIENTE,
                        INICIO,
                        TO_CHAR(FIN, 'DD-MM-YYYY') AS FECHA_BAJA,
                        FIN AS FECHA_BAJA_REAL,
                        m.CODIGO AS CODIGO_MOTIVO,
                        UPPER(m.DESCRIPCION) AS MOTIVO_BAJA,
                        ROW_NUMBER() OVER (
                            PARTITION BY CDGCL
                            ORDER BY
                                FIN DESC
                        ) AS RN
                    FROM
                        CN c
                        INNER JOIN MS m ON m.CODIGO = c.CDGMS
                        INNER JOIN CL c2 ON c2.CODIGO = c.CDGCL
                    WHERE
                        CDGNS = '$credito'
                ) sub
            WHERE
                RN = 1
        SQL;

        $query2 = <<<SQL
            SELECT 
                NOMBRE
            FROM
                NS
            WHERE
                CODIGO = '$credito'
        SQL;

        $db = new Database();
        if ($db->db_activa == null) return [];
        return [$db->queryAll($query), $db->queryOne($query2)];
    }
}
