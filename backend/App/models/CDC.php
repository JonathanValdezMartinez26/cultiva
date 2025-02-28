<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use Core\Database;
use Core\Model;

class CDC extends Model
{
    public static function GetConsultaCliente($datos)
    {
        $qry = <<<SQL
            SELECT
                CL.CODIGO AS CLIENTE,
                CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
                BCC.FOLIO_CONSULTA AS FOLIO,
                BCC.RES_CONSULTA AS RESULTADO,
                TO_CHAR(BCC.FECHA_CONSULTA, 'DD/MM/YYYY') AS FECHA,
                TO_CHAR(BCC.FECHA_CONSULTA + 90, 'DD/MM/YYYY') AS CADUCIDAD,
                CL.NOMBRE1,
                CL.NOMBRE2,
                CL.PRIMAPE,
                CL.SEGAPE,
                TO_CHAR(CL.NACIMIENTO, 'DD/MM/YYYY') AS NACIMIENTO,
                TO_CHAR(CL.NACIMIENTO, 'YYYY-MM-DD') AS NACIMIENTO_CDC,
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

    public static function GetConsultaGlobal($datos)
    {
        $qry = <<<SQL
            SELECT
                RG.CODIGO || ' - ' || RG.NOMBRE  AS REGION,
                CO.CODIGO || ' - ' || CO.NOMBRE AS SUCURSAL,
                NS.CODIGO || ' - ' || NS.NOMBRE AS GRUPO,
                CL.CODIGO AS CLIENTE,
                CONCATENA_NOMBRE(CL.NOMBRE1, CL.NOMBRE2, CL.PRIMAPE, CL.SEGAPE) AS NOMBRE,
                BCC.FOLIO_CONSULTA AS FOLIO,
                TO_CHAR(BCC.FECHA_CONSULTA, 'DD/MM/YYYY') AS FECHA,
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
                LEFT JOIN COL ON CL.CDGCOL = COL.CODIGO AND CL.CDGLO = COL.CDGLO AND CL.CDGMU = COL.CDGMU AND CL.CDGEF = COL.CDGEF
                LEFT JOIN LO ON CL.CDGLO = LO.CODIGO AND CL.CDGMU = LO.CDGMU AND CL.CDGEF = LO.CDGEF
                LEFT JOIN MU ON CL.CDGMU = MU.CODIGO AND CL.CDGEF = MU.CDGEF 
                LEFT JOIN EF ON CL.CDGEF = EF.CODIGO
                LEFT JOIN CN ON CL.CODIGO = CN.CDGCL
                LEFT JOIN NS ON CN.CDGNS = NS.CODIGO
                LEFT JOIN CO ON NS.CDGCO = CO.CODIGO
                LEFT JOIN RG ON CO.CDGRG = RG.CODIGO
                LEFT JOIN BITACORA_CIRCULO_CREDITO BCC ON CL.CODIGO = BCC.CDGCL AND BCC.FECHA_CONSULTA = (
                                  SELECT MAX(BCC2.FECHA_CONSULTA) 
                                  FROM BITACORA_CIRCULO_CREDITO BCC2 
                                  WHERE BCC2.CDGCL = BCC.CDGCL)
            WHERE
                CL.CODIGO = BCC.CDGCL
        SQL;

        $prm = [];

        if (isset($datos['region']) && $datos['region'] !== '') {
            $qry .= ' AND RG.CODIGO = :region';
            $prm['region'] = $datos['region'];
        }

        if (isset($datos['sucursal']) && $datos['sucursal'] !== '') {
            $qry .= ' AND CO.CODIGO = :sucursal';
            $prm['sucursal'] = $datos['sucursal'];
        }

        if (isset($datos['grupo']) && $datos['grupo'] !== '') {
            $qry .= " AND (NS.CODIGO = :grupo OR LOWER(NS.NOMBRE) LIKE '%' || LOWER(:grupo) || '%')";
            $prm['grupo'] = $datos['grupo'];
        }

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
                (:cliente, :folio, :resultado, SYSDATE, 'A', :usuario, _AUTORIZACION_, _IDENTIFICACION_)
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

        if (!isset($datos['identificacion'])) $qry = str_replace('_IDENTIFICACION_', 'NULL', $qry);
        else {
            $prm['identificacion'] = $datos['identificacion'];
            $qry = str_replace('_IDENTIFICACION_', 'EMPTY_BLOB()', $qry);
            $columnas[] = 'IDENTIFICACION_PDF';
            $parametros[] = ':identificacion';
        }

        if (count($columnas) > 0) $qry .= 'RETURNING ' . implode(', ', $columnas) . ' INTO ' . implode(', ', $parametros);

        try {
            $db = new Database();
            $res = $db->insertarBlob($qry, $prm, ['autorizacion', 'identificacion'], ['resultado']);
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
                AUTORIZACION_PDF = _AUTORIZACION_PDF_,
                IDENTIFICACION_PDF = _IDENTIFICACION_PDF_
            WHERE
                CDGCL = :cliente
                AND FOLIO_CONSULTA = :folio
        SQL;

        $prm = [
            'cliente' => $datos['cliente'],
            'folio' => $datos['folio'],
        ];

        $filtro = [];
        $retorno1 = [];
        $retorno2 = [];

        if (!isset($datos['autorizacion'])) $qry = str_replace('_AUTORIZACION_PDF_', 'AUTORIZACION_PDF', $qry);
        else {
            $prm['autorizacion'] = $datos['autorizacion'];
            $qry = str_replace('_AUTORIZACION_PDF_', 'EMPTY_BLOB()', $qry);
            $filtro[] = 'AUTORIZACION_PDF IS NULL';
            $retorno1[] = 'AUTORIZACION_PDF';
            $retorno2[] = ':autorizacion';
        }

        if (!isset($datos['identificacion'])) $qry = str_replace('_IDENTIFICACION_PDF_', 'IDENTIFICACION_PDF', $qry);
        else {
            $prm['identificacion'] = $datos['identificacion'];
            $qry = str_replace('_IDENTIFICACION_PDF_', 'EMPTY_BLOB()', $qry);
            $filtro[] = 'IDENTIFICACION_PDF IS NULL';
            $retorno1[] = 'IDENTIFICACION_PDF';
            $retorno2[] = ':identificacion';
        }

        $f = '';
        if (count($filtro) > 0) $f .= ' AND (' . implode(' OR ', $filtro) . ')';

        $r = '';
        if (count($retorno1) > 0) $r = ' RETURNING ' . implode(', ', $retorno1) . ' INTO ' . implode(', ', $retorno2);

        $qry .= $f . $r;

        try {
            $db = new Database();
            $res = $db->insertarBlob($qry, $prm, ['autorizacion', 'identificacion']);
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

    public static function GetRegionSucursal()
    {
        $qry = <<<SQL
            SELECT
                RG.CODIGO AS REGION,
                RG.NOMBRE AS NOMBRE_REGION,
                CO.CODIGO AS SUCURSAL,
                CO.NOMBRE AS NOMBRE_SUCURSAL
            FROM
                RG
                JOIN CO ON CO.CDGRG = RG.CODIGO
            WHERE
                RG.CDGEM = 'EMPFIN'
                AND CO.CDGEM = 'EMPFIN'
            ORDER BY
                RG.CODIGO,
                CO.CODIGO
        SQL;

        try {
            $db = new Database();
            $res = $db->queryAll($qry);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }

    public static function GetQrysCargaDocMasiva($datos)
    {
        $qry = <<<SQL
            UPDATE
                BITACORA_CIRCULO_CREDITO
            SET
                AUTORIZACION_PDF = _AUTORIZACION_PDF_,
                IDENTIFICACION_PDF = _IDENTIFICACION_PDF_
            WHERE
                CDGCL = :cliente
                AND FECHA_CONSULTA = (
                    SELECT MAX(FECHA_CONSULTA)
                    FROM BITACORA_CIRCULO_CREDITO
                    WHERE CDGCL = :cliente
                )
        SQL;

        $prm = [
            'cliente' => $datos['cliente']
        ];

        $filtro = [];
        $retorno1 = [];
        $retorno2 = [];

        if (!isset($datos['autorizacion'])) $qry = str_replace('_AUTORIZACION_PDF_', 'AUTORIZACION_PDF', $qry);
        else {
            $prm['autorizacion'] = $datos['autorizacion'];
            $qry = str_replace('_AUTORIZACION_PDF_', 'EMPTY_BLOB()', $qry);
            $filtro[] = 'AUTORIZACION_PDF IS NULL';
            $retorno1[] = 'AUTORIZACION_PDF';
            $retorno2[] = ':autorizacion';
        }

        if (!isset($datos['identificacion'])) $qry = str_replace('_IDENTIFICACION_PDF_', 'IDENTIFICACION_PDF', $qry);
        else {
            $prm['identificacion'] = $datos['identificacion'];
            $qry = str_replace('_IDENTIFICACION_PDF_', 'EMPTY_BLOB()', $qry);
            $filtro[] = 'IDENTIFICACION_PDF IS NULL';
            $retorno1[] = 'IDENTIFICACION_PDF';
            $retorno2[] = ':identificacion';
        }

        $f = '';
        if (count($filtro) > 0) $f .= ' AND (' . implode(' OR ', $filtro) . ')';

        $r = '';
        if (count($retorno1) > 0) $r = ' RETURNING ' . implode(', ', $retorno1) . ' INTO ' . implode(', ', $retorno2);

        return [$qry . $f . $r, $prm];
    }

    public static function CargaDocMasiva($qrys, $prms)
    {
        try {
            $db = new Database();
            $res = $db->insertarBlob($qrys, $prms, ['autorizacion', 'identificacion']);
            return self::Responde(true, "Documentos cargados exitosamente.", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al cargar los documentos en la base.', null, $e->getMessage());
        }
    }
}
