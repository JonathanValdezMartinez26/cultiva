<?php

namespace App\models;

defined("APPPATH") or die("Access denied");

use Core\Database;

class ApiCondusef
{
    public static function GetProductos()
    {
        $query = <<<sql
            SELECT
                CODIGO,
                SUBPRODUCTO as producto
            FROM
                CAT_PROD_SERV_RED
        sql;

        $db = new Database();
        if ($db->db_activa == null) return [];
        $resultado =  $db->queryAll($query);
        if ($resultado == null) return [];
        return $resultado;
    }

    public static function GetCausas()
    {
        $query = <<<sql
            SELECT
                CODIGO,
                DESCRIPCION
            FROM
                CAT_CAUSA_QUEJA_RED
        sql;

        $db = new Database();
        if ($db->db_activa == null) return [];
        $resultado =  $db->queryAll($query);
        if ($resultado == null) return [];
        return $resultado;
    }
}
