<?php

namespace Core;

class Model
{
    public static function Responde($respuesta, $mensaje, $datos = null, $error = null)
    {
        $res = [
            "success" => $respuesta,
            "mensaje" => $mensaje
        ];

        if ($datos !== null) $res['datos'] = $datos;
        if ($error !== null) $res['error'] = $error;

        return $res;
    }

    public static function GetSucursales()
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
            return self::Responde(true, 'Sucursales obtenidas', $res);
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al obtener sucursales', null, $e->getMessage());
        }
    }
}
