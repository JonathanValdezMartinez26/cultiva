<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use Core\Database;
use Core\Model;

class Creditos extends Model
{
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

    public static function GetReporteReferencias($datos)
    {
        $qry = <<<SQL
            SELECT
                NS.NOMBRE AS GRUPO,
                DT.CREDITO,
                DT.CICLO AS ULTIMO_CICLO,
                CASE PRN2.SITUACION
                    WHEN 'E' THEN 'ENTREGADO'
                    WHEN 'L' THEN 'LIQUIDADO'
                    ELSE 'N/A'
                END AS SITUACION,
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
                    MAX(PRN.CICLO) AS CICLO,
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
                    RG.NOMBRE
                ORDER BY
                    PRN.CDGNS) DT
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
            JOIN PRN PRN2 ON PRN2.CDGNS = DT.CREDITO AND PRN2.CICLO = DT.CICLO
        SQL;

        $filtros = '';

        if (isset($datos['region']) && $datos['region'] !== '') {
            $filtros .= ' AND RG.CODIGO = :region';
            $prm['region'] = $datos['region'];
        }

        if (isset($datos['sucursal']) && $datos['sucursal'] !== '') {
            $filtros .= ' AND CO.CODIGO = :sucursal';
            $prm['sucursal'] = $datos['sucursal'];
        }


        if (!isset($datos['situacion'])) $filtros .= " AND PRN.SITUACION IN ('E', 'L')";
        else {
            if ($datos['situacion'] === '') $filtros .= " AND PRN.SITUACION IN ('E', 'L')";
            else {
                $filtros .= ' AND PRN.SITUACION = :situacion';
                $prm['situacion'] = $datos['situacion'];
            }
        }

        if (isset($datos['credito']) && $datos['credito'] !== '') {
            $filtros .= ' AND PRN.CDGNS = :credito';
            $prm['credito'] = $datos['credito'];
        }

        $qry = str_replace('FILTROS', $filtros, $qry);

        try {
            $db = new Database();
            $res = $db->queryAll($qry, $prm);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }

    public static function SelectSucursalAllCreditoCambioSuc($datos)
    {
        $qry = <<<SQL
            SELECT 
                PRN.CDGNS CREDITO
                , NS.NOMBRE GRUPO
                , PRN.CICLO
                , PRN.CANTENTRE MONTO
                , PRN.SITUACION
                , PRN.CDGCO ID_SUCURSAL
                , GET_NOMBRE_SUCURSAL(PRN.CDGCO) SUCURSAL
                , GET_NOMBRE_EMPLEADO(PRN.CDGOCPE) EJECUTIVO
            FROM 
                PRN
                JOIN NS ON NS.CODIGO = PRN.CDGNS
            WHERE
                PRN.CDGNS = :credito
                AND PRN.CDGEM = 'EMPFIN'
                AND PRN.SITUACION != 'T'
            order BY
                PRN.INICIO DESC
        SQL;

        $prm = [
            'credito' => $datos['credito']
        ];

        try {
            $db = new Database();
            $res = $db->queryOne($qry, $prm);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }

    public static function ListaSucursales()
    {
        $qry = <<<SQL
            SELECT DISTINCT 
                RG.CODIGO ID_REGION,
                RG.NOMBRE REGION,
                CO.CODIGO ID_SUCURSAL,
                CO.NOMBRE SUCURSAL
            FROM
                PCO, CO, RG
            WHERE
                PCO.CDGCO = CO.CODIGO
                AND CO.CDGRG = RG.CODIGO 
                AND PCO.CDGEM = 'EMPFIN'
            ORDER BY
                    SUCURSAL ASC
        SQL;

        try {
            $db = new Database();
            $res = $db->queryAll($qry);
            return self::Responde(true, "Consulta exitosa", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }

    public static function UpdateSucursal($datos)
    {
        $sp = "SPACTUALIZASUC('EMPFIN', :credito, :ciclo, :sucursal, :output)";

        $prm = [
            'credito' => $datos['credito'],
            'ciclo' => $datos['ciclo'],
            'sucursal' => $datos['sucursal']
        ];

        try {
            $db = new Database();
            $res = $db->EjecutaSP($sp, $prm);
            if ($res == '1 Proceso realizado exitosamente')
                return self::Responde(true, "Sucursal actualizada correctamente", $res);
            else
                return self::Responde(false, "Error al actualizar la sucursal", $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al ejecutar la consulta', null, $e->getMessage());
        }
    }

    public static function GetReportePrestamos($datos)
    {
        $qry = <<<SQL
            SELECT
                Q1.*
                , CASE WHEN Q1.CAPITAL_PAGADO IS NOT NULL THEN Q1.CAPITAL_PAGADO + Q1.INT_PAGADO ELSE NULL END AS TOTAL_PAGADO
                , CASE WHEN Q1.INT_GEN IS NOT NULL THEN Q1.INT_GEN - Q1.INT_PAGADO ELSE NULL END AS SALDO_INT
            FROM (
                SELECT
                    SN.CDGNS CREDITO
                    , SN.CICLO
                    , NS.NOMBRE
                    , (SELECT COUNT(*) FROM CL, SC WHERE CL.CDGEM = SC.CDGEM AND CL.CODIGO = SC.CDGCL AND SC.CDGNS = SN.CDGNS AND SC.CDGEM = SN.CDGEM AND SC.CICLO = SN.CICLO AND SC.SITUACION = SN.SITUACION AND CL.SEXO IN ('F','E') AND SC.CDGEM = SN.CDGEM AND SC.CDGNS = SN.CDGNS AND SC.CICLO = SN.CICLO) MUJERES
                    , (SELECT COUNT(*) FROM CL, SC WHERE CL.CDGEM = SC.CDGEM AND CL.CODIGO = SC.CDGCL AND SC.CDGNS = SN.CDGNS AND SC.CDGEM = SN.CDGEM AND SC.CICLO = SN.CICLO AND SC.SITUACION = SN.SITUACION AND CL.SEXO IN ('M','H') AND SC.CDGEM = SN.CDGEM AND SC.CDGNS = SN.CDGNS AND SC.CICLO = SN.CICLO) HOMBRES
                    , RG.CODIGO || ' - ' || RG.NOMBRE AS REGION
                    , CO.CODIGO || ' - ' || CO.NOMBRE AS SUCURSAL
                    , TO_CHAR(SN.SOLICITUD, 'DD/MM/YYYY') SOLICITUD
                    , TO_CHAR(PRN.FAUTCAR, 'DD/MM/YYYY') AUTORIZACION
                    , CASE WHEN PRN.CANTENTRE IS NOT NULL THEN PRN.AUTCARPE || ' - ' || GET_NOMBRE_EMPLEADO(PRN.AUTCARPE) ELSE NULL END AUTORIZO
                    , SN.DURACION
                    , SN.TASA
                    , TO_CHAR(SN.INICIO, 'DD/MM/YYYY') INICIO
                    , TO_CHAR(FNFECHAPROXPAGO(SN.INICIO, SN.PERIODICIDAD, SN.DURACION), 'DD/MM/YYYY') FIN_CICLO
                    , CASE SN.SITUACION
                        WHEN 'A' THEN 'AUT. CARTE.'
                        WHEN 'S' THEN 'SOLICITADO'
                        WHEN 'R' THEN 'RECHAZADO'
                        ELSE ''
                    END SITUACION_SOLICITUD
                    , CASE PRN.SITUACION
                        WHEN 'A' THEN 'AUT. CARTE.'
                        WHEN 'D' THEN 'DEVUELTO'
                        WHEN 'E' THEN 'ENTREGADO'
                        WHEN 'L' THEN 'LIQUIDADO'
                        WHEN 'T' THEN 'AUT. TESOR.'
                        WHEN 'S' THEN 'SOLICITADO'
                        WHEN 'R' THEN 'RECHAZADO'
                        ELSE ''
                    END SITUACION_PRESTAMO
                    , ROUND(PARCIALIDADPRN(PRN.CDGEM, PRN.CDGNS, PRN.CICLO, NVL(PRN.CANTENTRE,PRN.CANTAUTOR), PRN.TASA, PRN.PLAZO, PRN.PERIODICIDAD, PRN.CDGMCI, PRN.INICIO, PRN.DIAJUNTA, PRN.MULTPER, PRN.PERIGRCAP, PRN.PERIGRINT, PRN.DESFASEPAGO, PRN.CDGTI, NULL),2) PARCIALIDAD
                    , SN.CANTSOLIC
                    , NULL DIAS_MORA
                    , PRN.CANTENTRE
                    , ROUND(CASE PRN.PERIODICIDAD
                        WHEN 'S' THEN NVL(PRN.CANTENTRE,0) + (NVL(PRN.TASA,0) * NVL(PRN.PLAZO,0) * NVL(PRN.CANTENTRE,0))/(4 * 100)
                        WHEN 'Q' THEN NVL(PRN.CANTENTRE,0) + (NVL(PRN.TASA,0) * NVL(PRN.PLAZO,0) * NVL(PRN.CANTENTRE,0) * 15)/(30 * 100)
                        WHEN 'C' THEN NVL(PRN.CANTENTRE,0) + (NVL(PRN.TASA,0) * NVL(PRN.PLAZO,0) * NVL(PRN.CANTENTRE,0))/(2 * 100)
                        WHEN 'M' THEN NVL(PRN.CANTENTRE,0) + (NVL(PRN.TASA,0) * NVL(PRN.PLAZO,0) * NVL(PRN.CANTENTRE,0))/(100)
                        ELSE 0
                    END - CANTENTRE, 2) INT_GEN
                    , NULL NO_PUEDO_PAGAR
                    , CASE WHEN PRN.CANTENTRE IS NOT NULL THEN PAGADOCAPITALPRN(PRN.CDGEM, PRN.CDGNS, PRN.CICLO, PRN.CDGMCI, TO_DATE(:fechaF, 'YYYY-MM-DD'), 'N') ELSE NULL END CAPITAL_PAGADO
                    , CASE WHEN PRN.CANTENTRE IS NOT NULL THEN PAGADOINTERESPRN(PRN.CDGEM, PRN.CDGNS, PRN.CICLO, TO_DATE(:fechaF, 'YYYY-MM-DD')) ELSE NULL END INT_PAGADO
                    , CASE WHEN PRN.CANTENTRE IS NOT NULL THEN SALDOCAPITALPRN(PRN.CDGEM, PRN.CDGNS, PRN.CICLO, PRN.CANTENTRE, PRN.TASA, PRN.PLAZO, PRN.PERIODICIDAD, PRN.CDGMCI, PRN.INICIO, PRN.DIAJUNTA, PRN.MULTPER, PRN.PERIGRCAP, PRN.PERIGRINT, PRN.DESFASEPAGO, PRN.CDGTI, PRN.MODOAPLIRECA, TO_DATE(:fechaF, 'YYYY-MM-DD'), NULL, 'N') ELSE NULL END SALDO_CAP
                    , CASE WHEN PRN.CANTENTRE IS NOT NULL THEN SALDOTOTALPRN(PRN.CDGEM, PRN.CDGNS, PRN.CICLO, PRN.CANTENTRE, PRN.TASA, PRN.PLAZO, PRN.PERIODICIDAD, PRN.CDGMCI, PRN.INICIO, PRN.DIAJUNTA, PRN.MULTPER, PRN.PERIGRCAP, PRN.PERIGRINT, PRN.DESFASEPAGO, PRN.CDGTI, PRN.MODOAPLIRECA, TO_DATE(:fechaF, 'YYYY-MM-DD')) ELSE NULL END SALDO_TOT
                    , CASE WHEN PRN.CANTENTRE IS NOT NULL THEN ROUND(SALDOVENCIDOCAPITALPRN(PRN.CDGEM, PRN.CDGNS, PRN.CICLO, PRN.CANTENTRE, PRN.TASA, PRN.PLAZO, PRN.PERIODICIDAD, PRN.CDGMCI, PRN.INICIO, PRN.DIAJUNTA, PRN.MULTPER, PRN.PERIGRCAP, PRN.PERIGRINT, PRN.DESFASEPAGO, PRN.CDGTI, PRN.MODOAPLIRECA, TO_DATE(:fechaF, 'YYYY-MM-DD'), NULL, 'S'), 2) ELSE NULL END MORA_TOT
                    , CASE WHEN PRN.CANTENTRE IS NOT NULL AND SN.CICLO = (SELECT MAX(A.CICLO) CICLO FROM PRN A WHERE A.CDGEM = PRN.CDGEM AND A.SITUACION <> 'D' AND A.CDGNS = PRN.CDGNS) THEN FNSDOGARANTIA(PRN.CDGEM, PRN.CDGNS, PRN.CICLO, 'G', TO_DATE(:fechaF, 'YYYY-MM-DD')) ELSE NULL END SALDO_GL
                    , SN.CDGOCPE || ' - ' || GET_NOMBRE_EMPLEADO(SN.CDGOCPE) ASESOR
                    , CO.CDGPE || ' - ' || GET_NOMBRE_EMPLEADO(CO.CDGPE) NOM_GERENTE
                    , TPC.NOMBRE TIPO_CARTERA
                FROM
                    SN
                    LEFT JOIN PRN ON PRN.CDGNS = SN.CDGNS AND PRN.CICLO = SN.CICLO AND PRN.SOLICITUD = SN.SOLICITUD
                    LEFT JOIN TPC ON TPC.CDGEM = PRN.CDGEM AND TPC.CODIGO = PRN.CDGTPC
                    LEFT JOIN NS ON NS.CODIGO = SN.CDGNS
                    LEFT JOIN CO ON CO.CODIGO = SN.CDGCO
                    LEFT JOIN RG ON RG.CODIGO = CO.CDGRG
                WHERE
                    SN.SOLICITUD BETWEEN TO_DATE(:fechaI, 'YYYY-MM-DD') AND TO_DATE(:fechaF, 'YYYY-MM-DD')
                    FILTROS
            ) Q1
            ORDER BY
                Q1.SOLICITUD DESC
        SQL;

        $filtros = '';
        $prm = [
            'fechaI' => $datos['fechaI'],
            'fechaF' => $datos['fechaF']
        ];

        if (isset($datos['region']) && $datos['region'] !== '') {
            $filtros .= ' AND RG.CODIGO = :region';
            $prm['region'] = $datos['region'];
        }

        if (isset($datos['sucursal']) && $datos['sucursal'] !== '') {
            $filtros .= ' AND CO.CODIGO = :sucursal';
            $prm['sucursal'] = $datos['sucursal'];
        }

        if (isset($datos['sitSolicitud']) && $datos['sitSolicitud'] !== '') {
            $filtros .= ' AND SN.SITUACION = :sitSolicitud';
            $prm['sitSolicitud'] = $datos['sitSolicitud'];
        }

        if (isset($datos['sitPrestamo']) && $datos['sitPrestamo'] !== '') {
            $filtros .= ' AND PRN.SITUACION = :sitPrestamo';
            $prm['sitPrestamo'] = $datos['sitPrestamo'];
        }

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
