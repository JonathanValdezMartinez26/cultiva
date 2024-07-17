<?php

namespace Jobs\models;

include 'C:/xampp/htdocs/cultiva/backend/Core/Database.php';

use \Core\Database;

class apu
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

    public static function GetLista()
    {
        $qry = <<<sql
            SELECT 
                MP.CDGNS, 
                MP.CICLO, 
                MP.FDEPOSITO, 
                MP.FREALDEP, 
                PRN.INICIO,
                CASE 
                    WHEN MP.FDEPOSITO = PRN.INICIO THEN '' 
                    ELSE 'NO' 
                END AS FDEPOSITO_NOT_EQUAL,
                CASE 
                    WHEN MP.FREALDEP = PRN.INICIO THEN '' 
                    ELSE 'NO' 
                END AS FREALDEP_NOT_EQUAL
            FROM MP
            INNER JOIN PRN ON PRN.CDGNS = MP.CDGCLNS AND PRN.CICLO = MP.CICLO 
            WHERE MP.TIPO = 'IN'
            AND PRN.INICIO > TIMESTAMP '2024-04-03 00:00:00.000000'
        sql;

        $db = new Database();
        if ($db->db_activa == null) return null;
        $db->SetDB_MCM();

        $resultado = $db->queryAll($qry);
        if ($resultado == null || $resultado == false) return null;
        return $resultado;
    }

    public static function Actualiza($datos)
    {
        $qry = <<<sql
            UPDATE MP
            SET FDEPOSITO = TO_DATE(:fdeposito, 'dd/mm/yy')
            WHERE CDGCLNS = :credito AND CICLO = :ciclo AND TIPO = 'IN'
        sql;
        // , FREALDEP = TO_DATE(:frealdep, 'dd/mm/yy')
        $db = new Database();
        if ($db->db_activa == null) return null;
        $db->SetDB_MCM();

        $v = [];
        foreach ($datos as $dato) {
            // if ($dato['FDEPOSITO_NOT_EQUAL'] != 'NO' && $dato['FREALDEP_NOT_EQUAL'] != 'NO') continue;
            if ($dato['FDEPOSITO_NOT_EQUAL'] != 'NO') continue;
            $valores = [
                'credito' => $dato['CDGNS'],
                'ciclo' => $dato['CICLO'],
                'fdeposito' => $dato['INICIO']
            ];

            // ,
            //     'frealdep' => $dato['INICIO']

            $resultado = $db->insert($qry, $valores);
            if ($resultado) array_push($v, $valores);
        }

        return $v;
    }
}
