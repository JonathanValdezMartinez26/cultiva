<?php

namespace App\models;

include 'C:/xampp/htdocs/cultiva/backend/Core/Database.php';

use \Core\Database;

class ActualizaListasNegras
{
    private static function Responde($respuesta, $mensaje, $datos = null, $error = null)
    {
        $res = array(
            "success" => $respuesta,
            "mensaje" => $mensaje
        );

        if ($datos != null) $res['datos'] = $datos;
        if ($error != null) $res['error'] = $error;

        return json_encode($res);
    }

    public static function GetListaNegraMCM()
    {
        $qry = <<<SQL
        SELECT
            PRC.CDGCL,
            CL.CURP 
        FROM
            PRN_LEGAL PRL
        JOIN
            PRC ON PRL.CDGCLNS = PRC.CDGNS AND PRL.CICLO = PRC.CICLO 
        JOIN 
            CL ON PRC.CDGCL = CL.CODIGO
        WHERE
            PRL.CDGEM = 'EMPFIN'
        UNION
        SELECT
            CM.CDGCL,
            CM.CURP
        FROM
            CL_MARCA CM
        WHERE
            CM.CDGEM = 'EMPFIN'
        SQL;

        $db = new Database();
        if ($db->db_activa == null) return null;
        $db->SetDB_MCM();
        $resultado =  $db->queryAll($qry);
        if ($resultado == null) return null;
        return $resultado;
    }

    public static function ValidaListaNegraCultiva($datos)
    {
        $tblTemp = "CREATE TABLE curps_lista_negra (CDGCL VARCHAR2(20), CURP VARCHAR2(20))";
        $in = "INSERT INTO curps_lista_negra (CDGCL, CURP) VALUES (:cdgcl, :curp)";

        $inserts = [];
        $valores = [];
        foreach ($datos as $dato) {
            array_push($inserts, $in);
            array_push($valores, ['cdgcl' => $dato['CDGCL'], 'curp' => $dato['CURP']]);
        }

        $qry = <<<SQL
            SELECT
                CLN.CDGCL,
                CLN.CURP
            FROM
                curps_lista_negra CLN
            WHERE
                NOT EXISTS (
                    SELECT
                        1
                    FROM
                        CL_MARCA CM
                    WHERE
                        CM.CURP = CLN.CURP
                )
        SQL;

        $dropTbl = "DROP TABLE curps_lista_negra";

        $db = new Database();
        if ($db->db_activa == null) return null;
        $db->SetDB_CULTIVA();
        $resultado = $db->insert($tblTemp);
        if ($resultado == false) return null;

        $resultado = $db->insertaMultiple($inserts, $valores);
        if ($resultado) $resultado = $db->queryAll($qry);
        $db->insert($dropTbl);
        if ($resultado == null || $resultado == false) return null;
        return $resultado;
    }

    public static function InsertaListaNegraCultiva($datos)
    {
        $qry = <<<SQL
        INSERT INTO CL_MARCA
            (CDGEM, SECUENCIA, CDGCL, CURP, TIPOMARCA, ESTATUS, MONTOMAX, ALTAPE, ALTA, BAJAPE, BAJA, FREGISTRO, CAUSA, CAUSABAJA, CDGCLNS, CICLO, CLNS)
        VALUES
            ('EMPFIN', (SELECT COUNT(*) + 1 FROM CL_MARCA WHERE TO_CHAR(FREGISTRO, 'YYYYMMDD') = TO_CHAR(SYSDATE, 'YYYYMMDD')), :cdgcl, :curp, 'LN', 'A', NULL, 'SYSTEM', TRUNC(SYSDATE), NULL, NULL, SYSDATE, '9', NULL, NULL, NULL, NULL)
        SQL;

        $inserts = [];
        $valores = [];

        foreach ($datos as $dato) {
            array_push($inserts, $qry);
            array_push($valores, [
                'cdgcl' => $dato['CDGCL'],
                'curp' => $dato['CURP']
            ]);
        }

        $db = new Database();
        if ($db->db_activa == null) return null;
        $db->SetDB_CULTIVA();
        $resultado = $db->insertaMultiple($inserts, $valores);
        if ($resultado == null || $resultado == false) return null;
        return $resultado;
    }
}
