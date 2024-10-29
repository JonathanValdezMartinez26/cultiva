<?php

namespace Core;

include_once dirname(__DIR__) . "/Core/App.php";

use PDO;

/**
 * @class Conn
 */

class Database
{
    private $configuracion;
    public $db_activa;

    function __construct($s = null, $u = null, $p = null)
    {
        $this->configuracion = App::getConfig();
        $this->Conecta($s, $u, $p);
    }

    private function Conecta($s = null, $u = null, $p = null)
    {
        $host = 'oci:dbname=//' . ($s ?? $this->configuracion['SERVIDOR']) . ':1521/ESIACOM;charset=UTF8';
        $usuario = $u ?? $this->configuracion['USUARIO'];
        $password = $p ?? $this->configuracion['PASSWORD'];
        try {
            $this->db_activa =  new PDO($host, $usuario, $password);
        } catch (\PDOException $e) {
            echo self::muestraError($e);
            $this->db_activa =  null;
        }
    }

    private function muestraError($e, $sql = null, $parametros = null)
    {
        $error = "Error en DB: " . $e->getMessage();

        if ($sql != null) $error .= "\nSql: " . $sql;
        if ($parametros != null) $error .= "\nDatos: " . print_r($parametros, 1);
        echo $error . "\n";
        return $error;
    }

    public function queryOne($sql, $params = [])
    {
        try {
            $stmt = $this->db_activa->prepare($sql);
            $stmt->execute($params);
            return array_shift($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (\PDOException $e) {
            self::muestraError($e, $sql, $params);
            return [];
        }
    }

    public function queryAll($sql, $params = [])
    {
        try {
            $stmt = $this->db_activa->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            self::muestraError($e, $sql, $params);
            return [];
        }
    }

    public function insert($sql, $params = [])
    {
        try {
            $stmt = $this->db_activa->prepare($sql);
            $stmt->execute($params);
            $err = $stmt->errorInfo();

            if ($err[0] != '00000')
                throw new \PDOException("Error en insert: " . print_r($err, 1) . "\nSql: $sql \nDatos: " . print_r($params, 1));

            return true;
        } catch (\PDOException $e) {
            self::muestraError($e, $sql, $params);
            return false;
        }
    }

    public function insertar($sql, $datos)
    {
        try {
            if (!$this->db_activa->prepare($sql)->execute($datos)) {
                throw new \Exception("Error en insertar: " . print_r($this->db_activa->errorInfo(), 1) . "\nSql : $sql \nDatos : " . print_r($datos, 1));
            }
        } catch (\PDOException $e) {
            throw new \Exception("Error en insertar: " . $e->getMessage() . "\nSql: $sql \nDatos: " . print_r($datos, 1));
        }
    }

    public function insertCheques($sql, $parametros)
    {
        $stmt = $this->db_activa->prepare($sql);
        $result = $stmt->execute($parametros);

        if ($result) return $result;

        $arr = $stmt->errorInfo();
        return "PDOStatement::errorInfo():\n" . json_encode($arr);
    }

    public function insertaMultiple($sql, $registros, $validacion = null)
    {
        try {
            $this->db_activa->beginTransaction();
            foreach ($registros as $i => $valores) {
                $stmt = $this->db_activa->prepare($sql[$i]);
                $result = $stmt->execute($valores);
                $err = $stmt->errorInfo();
                if (!$result || $err[0] != '00000')
                    throw new \PDOException("Error: " . print_r($err, 1) . "\nSql: " . $sql[$i] . "\nDatos: " . print_r($valores, 1));
            }

            if ($validacion != null) {
                $stmt = $this->db_activa->prepare($validacion['query']);
                $stmt->execute($validacion['datos']);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $resValidacion = $validacion['funcion']($result);
                if ($resValidacion['success'] == false) {
                    $this->db_activa->rollBack();
                    throw new \PDOException($resValidacion['mensaje']);
                }
            }

            return $this->db_activa->commit();
        } catch (\PDOException $e) {
            $this->db_activa->rollBack();
            self::muestraError($e);
            return false;
        }
    }

    public function eliminar($sql)
    {
        try {
            $stmt = $this->db_activa->prepare($sql);
            $stmt->execute();
            $err = $stmt->errorInfo();

            if ($err[0] != '00000')
                throw new \PDOException("Error en delete: " . print_r($err, 1) . "\nSql: $sql");

            return true;
        } catch (\PDOException $e) {
            self::muestraError($e, $sql);
            return false;
        }
    }
}
