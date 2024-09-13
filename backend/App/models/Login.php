<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use \Core\Database;

class Login
{
    public static function getById($usuario)
    {
        $query1 = <<<sql
        SELECT
            CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) NOMBRE,
            UT.CDGTUS PERFIL, PE.PUESTO , PE.CDGCO, PE.CODIGO 
        FROM
            PE,
            UT
        WHERE
            PE.CODIGO = UT.CDGPE
            AND PE.CDGEM = UT.CDGEM
            AND PE.CDGEM = 'EMPFIN'
            AND PE.ACTIVO = 'S'
            AND (PE.BLOQUEO = 'N' OR PE.BLOQUEO IS NULL)
            AND PE.CODIGO = :usuario
            AND PE.CLAVE LIKE (SELECT CODIFICA(:password) as pass FROM DUAL)
            AND (
                UT.CDGTUS = 'ADMIN' ------ USUARIO ADMIN
                OR UT.CDGTUS = 'OFCLD' ------- USUARIO CAJA (EXTRA)
                OR UT.CDGTUS = 'PLDCO' ----- USUARIO OCOF
            )
        sql;

        $params1 = array(
            ':usuario' => $usuario->_usuario,
            ':password' => $usuario->_password
        );


        $db = new Database;
        return [$db->queryOne($query1, $params1)];
    }

    public static function getUser($usuario)
    {
        $query = <<<sql
        SELECT
            CONCATENA_NOMBRE(PE.NOMBRE1, PE.NOMBRE2, PE.PRIMAPE, PE.SEGAPE) NOMBRE,
            UT.CDGTUS PERFIL, PE.PUESTO , PE.CDGCO, PE.CODIGO
        FROM
            PE,
            UT
        WHERE
            PE.CODIGO = UT.CDGPE
            AND PE.CDGEM = UT.CDGEM
            AND PE.CDGEM = 'EMPFIN'
            AND PE.ACTIVO = 'S'
            AND (PE.BLOQUEO = 'N' OR PE.BLOQUEO IS NULL)
            AND PE.CODIGO = '$usuario'
        sql;

        $db = new Database;
        return $db->queryAll($query);
    }
}
