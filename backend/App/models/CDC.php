<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use Core\Database;
use Core\Model;

class CDC extends Model
{
    public static function GetResultadosCDC($datos)
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
                COL.CDGPOSTAL AS CP,
                CASE
                    WHEN BCC.AUTORIZACION_PDF IS NULL THEN 0
                    ELSE 1
                END AS AUTORIZACION,
                CASE
                    WHEN BCC.IDENTIFICACION_PDF IS NULL THEN 0
                    ELSE 1
                END AS IDENTIFICACION
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
                (:cliente, :folio, :resultado, SYSDATE, 'A', :usuario, _AUTORIZACION_, _INE_)
        SQL;

        $prm = [
            'cliente' => $datos['cliente'],
            'folio' => $datos['folio'],
            'resultado' => $datos['resultado'],
            'usuario' => $datos['usuario']
        ];

        $columnas = [];
        $parametros = [];

        if (!isset($datos['autorizacion'])) $qry = str_replace('_AUTORIZACION_', 'NULL', $qry);
        else {
            $prm['autorizacion'] = $datos['autorizacion'];
            $qry = str_replace('_AUTORIZACION_', 'EMPTY_BLOB()', $qry);
            $columnas[] = 'AUTORIZACION_PDF';
            $parametros[] = ':autorizacion';
        }

        if (!isset($datos['ine'])) $qry = str_replace('_INE_', 'NULL', $qry);
        else {
            $prm['ine'] = $datos['ine'];
            $qry = str_replace('_INE_', 'EMPTY_BLOB()', $qry);
            $columnas[] = 'IDENTIFICACION_PDF';
            $parametros[] = ':ine';
        }

        if (count($columnas) > 0) $qry .= 'RETURNING ' . implode(', ', $columnas) . ' INTO ' . implode(', ', $parametros);

        try {
            $db = new Database();
            $res = $db->insertarBlob($qry, $prm, ['autorizacion', 'ine'], ['resultado']);
            return self::Responde(true, "Consulta registrada exitosamente.", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al guardar los datos en la base.', null, $e->getMessage());
        }
    }

    public static function SetDocumentosCDC($datos)
    {
        $qry = <<<SQL
            UPDATE
                BITACORA_CIRCULO_CREDITO
            SET
                AUTORIZACION_PDF = :autorizacion,
                IDENTIFICACION_PDF = :ine
            WHERE
                CDGCL = :cliente
                AND FOLIO_CONSULTA = :folio
            RETURNING
                AUTORIZACION_PDF,
                IDENTIFICACION_PDF
            INTO
                :autorizacion,
                :ine
        SQL;

        $prm = [
            'cliente' => $datos['cliente'],
            'folio' => $datos['folio'],
            'autorizacion' => $datos['autorizacion'],
            'ine' => $datos['ine']
        ];

        try {
            $db = new Database();
            $res = $db->insertarBlob($qry, $prm, ['autorizacion', 'ine']);
            return self::Responde(true, "Registro actualizado exitosamente.", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al actualizar los datos en la base.', null, $e->getMessage());
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
            ORDER BY
                FECHA_CONSULTA DESC
        SQL;

        $prm = [
            'cliente' => $datos['cliente'],
            'folio' => $datos['folio']
        ];

        $columna = $datos['documento'] === 'autorizacion' ? 'AUTORIZACION_PDF' : 'IDENTIFICACION_PDF';
        $qry = str_replace('columna', $columna, $qry);

        try {
            $db = new Database();
            $res = $db->queryOne($qry, $prm);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al recuperar el documento de la base de datos.', null, $e->getMessage());
        }
    }
}
