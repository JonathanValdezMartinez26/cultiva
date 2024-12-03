<?php

namespace Jobs\controllers;

include_once dirname(__DIR__) . "\..\Core\Job.php";
include dirname(__DIR__) . '\models\SincronizaBloqueoClientes.php';

use Core\Job;
use Jobs\models\SincronizaBloqueoClientes as DAO;

class SincronizaBloqueoClientes extends Job
{
    public function __construct()
    {
        parent::__construct("SincronizaBloqueoClientes");
    }

    public function ListaNegra()
    {
        self::SaveLog("Inicio de sincronización de lista negra.");

        $respuesta = [
            "mcm_cultiva" => null,
            "cultiva_mcm" => null
        ];

        $listaMCM = DAO::GetListaNegra();
        if ($listaMCM["success"] == false) $respuesta["mcm_cultiva"] = $listaMCM["mensaje"];

        if ($listaMCM["success"]) {
            $resultado = DAO::ValidaListaNegra($listaMCM["datos"], false);
            if ($resultado["success"] == false) $respuesta["mcm_cultiva"] = $resultado["mensaje"];

            if ($resultado["success"]) {
                $insertados = DAO::InsertaListaNegra($resultado["datos"], false);
                if ($insertados["success"] == false) $respuesta["mcm_cultiva"] = $insertados["mensaje"];
                else $respuesta["mcm_cultiva"] = $resultado["datos"];
            }
        }

        $listaCultiva = DAO::GetListaNegra(false);
        if ($listaCultiva["success"] == false) $respuesta["cultiva_mcm"] = $listaCultiva["mensaje"];

        if ($listaCultiva["success"]) {
            $resultado = DAO::ValidaListaNegra($listaCultiva["datos"]);
            if ($resultado["success"] == false) $respuesta["cultiva_mcm"] = $resultado["mensaje"];

            if ($resultado["success"]) {
                $insertados = DAO::InsertaListaNegra($resultado["datos"]);
                if ($insertados["success"] == false) $respuesta["cultiva_mcm"] = $insertados["mensaje"];
                else $respuesta["cultiva_mcm"] = $resultado["datos"];
            }
        }

        self::SaveLog(json_encode($respuesta));
    }

    public function ListaGris()
    {
        self::SaveLog("Inicio de sincronización de lista gris.");

        $respuesta = [
            "mcm_cultiva" => null,
            "cultiva_mcm" => null
        ];

        $listaMCM = DAO::GetListaGris();
        if ($listaMCM["success"] == false) $respuesta["mcm_cultiva"] = $listaMCM["mensaje"];

        if ($listaMCM["success"]) {
            $resultado = DAO::ActualizaListaGris($listaMCM["datos"], false);
            $respuesta["mcm_cultiva"] = $resultado;
        }

        $listaCultiva = DAO::GetListaGris(false);
        if ($listaCultiva["success"] == false) $respuesta["cultiva_mcm"] = $listaCultiva["mensaje"];

        if ($listaCultiva["success"]) {
            $resultado = DAO::ActualizaListaGris($listaCultiva["datos"]);
            $respuesta["cultiva_mcm"] = $resultado;
        }

        self::SaveLog(json_encode($respuesta));
    }
}

if (isset($argv[1])) {
    $aln = new SincronizaBloqueoClientes();

    switch ($argv[1]) {
        case 'ListaNegra':
            $aln->ListaNegra();
            break;
        case 'ListaGris':
            $aln->ListaGris();
            break;
        case 'prueba_horario':
            echo date("Y-m-d H:i:s") . "\n";
            break;
        case 'help':
            echo "Jobs disponibles:\n";
            echo "ListaNegra: Actualiza la lista negra de clientes en las bases de datos de MCM y Cultiva.\n";
            echo "ListaGris: Actualiza la lista gris de clientes en las bases de datos de MCM y Cultiva.\n";
            break;
        default:
            echo "No se encontró el job solicitado.\n";
            break;
    }
} else echo "Debe especificar el job a ejecutar.\nEjecute 'php SincronizaBloqueoClientes.php help' para ver los jobs disponibles.\n";
