<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use Core\Database;
use Core\Model;

class CDC extends Model
{
    public static function GetResultadoCDC($datos)
    {
        $qry = <<<SQL
            SELECT
                CL.CODIGO AS CLIENTE,
                CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
                TO_CHAR(BCC.FECHA_CONSULTA, 'DD/MM/YYYY') AS FECHA,
                BCC.FOLIO_CONSULTA AS FOLIO,
                BCC.RES_CONSULTA AS RESULTADO,
                TO_CHAR(BCC.FECHA_CONSULTA + 90, 'DD/MM/YYYY') AS CADUCIDAD,
                CL.NOMBRE1,
                CL.NOMBRE2,
                CL.PRIMAPE,
                CL.SEGAPE,
                TO_CHAR(CL.NACIMIENTO, 'DD/MM/YYYY') AS NACIMIENTO,
                CL.RFC,
                CL.CALLE,
                COL.NOMBRE AS COLONIA,
                MU.NOMBRE AS MUNICIPIO,
                LO.NOMBRE AS CIUDAD,
                EF.NOMBRE AS ESTADO_NOMBRE,
                EF.CCC AS ESTADO,
                COL.CDGPOSTAL AS CP
            FROM
                CL
                LEFT JOIN BITACORA_CIRCULO_CREDITO BCC ON CL.CODIGO = BCC.CDGCL
                LEFT JOIN COL ON CL.CDGCOL = COL.CODIGO AND CL.CDGLO = COL.CDGLO AND CL.CDGMU = COL.CDGMU AND CL.CDGEF = COL.CDGEF
                LEFT JOIN LO ON CL.CDGLO = LO.CODIGO AND CL.CDGMU = LO.CDGMU AND CL.CDGEF = LO.CDGEF
                LEFT JOIN MU ON CL.CDGMU = MU.CODIGO AND CL.CDGEF = MU.CDGEF 
                LEFT JOIN EF ON CL.CDGEF = EF.CODIGO
            WHERE
                CL.CODIGO = :cliente
            ORDER BY
                BCC.FECHA_CONSULTA DESC
        SQL;

        $prm = [
            'cliente' => $datos['cliente']
        ];

        try {
            $db = new Database();
            $res = $db->queryAll($qry, $prm);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }

    public static function SetResultadoCDC($datos)
    {
        $qry = <<<SQL
            INSERT INTO BITACORA_CIRCULO_CREDITO
                (CDGCL, FOLIO_CONSULTA, RES_CONSULTA, FECHA_CONSULTA, ESTATUS, CDGPE, AUTORIZACION_PDF, IDENTIFICACION_PDF)
            VALUES
                (:cliente, :folio, :resultado, SYSDATE, 'A', :usuario, EMPTY_BLOB(), EMPTY_BLOB())
            RETURNING AUTORIZACION_PDF, IDENTIFICACION_PDF INTO :autorizacion, :ine
        SQL;

        $prm = [
            'cliente' => $datos['cliente'],
            'folio' => $datos['folio'],
            'resultado' => $datos['resultado'],
            'usuario' => $datos['usuario'],
            'autorizacion' => $datos['autorizacion'] ?? null,
            'ine' => $datos['ine'] ?? null
        ];

        try {
            $db = new Database();
            $res = $db->insertarBlob($qry, $prm, ['autorizacion', 'ine'], ['resultado']);
            return self::Responde(true, "Consulta registrada exitosamente.", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al guardar los datos en la base.', null, $e->getMessage());
        }
    }

    public static function GetDocumento($datos)
    {
        $qry = <<<SQL
            SELECT
                columna AS PDF
            FROM
                BITACORA_CIRCULO_CREDITO
            WHERE
                CDGCL = :cliente
                AND FOLIO_CONSULTA = :folio
        SQL;

        $prm = [
            'cliente' => $datos['cliente'],
            'folio' => $datos['folio']
        ];

        $columna = $datos['documento'] == 'autorizacion' ? 'AUTORIZACION_PDF' : 'IDENTIFICACION_PDF';
        $qry = str_replace('columna', $columna, $qry);

        try {
            $db = new Database();
            $res = $db->queryOne($qry, $prm);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }

    public static function GetIdentificacion($datos)
    {
        $qry = <<<SQL
            SELECT
                IDENTIFICACION_PDF AS PDF
            FROM
                BITACORA_CIRCULO_CREDITO
            WHERE
                CDGCL = :cliente
                AND FOLIO_CONSULTA = :folio
        SQL;

        $prm = [
            'cliente' => $datos['cliente'],
            'folio' => $datos['folio']
        ];

        try {
            $db = new Database();
            $res = $db->queryOne($qry, $prm);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }
}
