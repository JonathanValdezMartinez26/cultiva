<?php

namespace Core;
// defined("APPPATH") or die("Access denied");

use Core\App;
use PDO;

/**
 * @class Conn
 */

class Database
{
    static $_instance;
    static $_mysqli;
    static $_debug;

    private function __construct()
    {
        $this->conectar();
    }

    public static function getInstance($debug = true)
    {
        self::$_debug = $debug;
        if (!(self::$_instance instanceof self)) self::$_instance = new self();
        return self::$_instance;
    }

    public static function getConexion()
    {
        return self::$_mysqli;
    }

    private static function conectar()
    {
        $dsn = 'oci:dbname=//mcm-server:1521/ESIACOM;charset=UTF8';
        $username = 'ESIACOM';
        $password = 'ESIACOM';

        try {
            self::$_mysqli =  new PDO($dsn, $username, $password);
        } catch (\PDOException $e) {
            if (self::$_debug)
                echo $e->getMessage();
            die();
        }
    }

    public function insert($sql)
    {

        $stmt = $this->_mysqli->prepare($sql);
        $result = $stmt->execute();

        if ($result) {
            echo '1';
        } else {
            echo "\nPDOStatement::errorInfo():\n";
            $arr = $stmt->errorInfo();
            print_r($arr);
        }
    }

    public function insertar($sql, $datos)
    {
        try {
            if (!$this->_mysqli->prepare($sql)->execute($datos)) {
                throw new \Exception("Error en insertar: " . print_r($this->_mysqli->errorInfo(), 1) . "\nSql : $sql \nDatos : " . print_r($datos, 1));
            }
        } catch (\PDOException $e) {
            throw new \Exception("Error en insertar: " . $e->getMessage() . "\nSql : $sql \nDatos : " . print_r($datos, 1));
        }
    }

    public function insertCheques($sql, $parametros)
    {
        $stmt = $this->_mysqli->prepare($sql);
        $result = $stmt->execute($parametros);

        if ($result) return $result;

        $arr = $stmt->errorInfo();
        return "PDOStatement::errorInfo():\n" . json_encode($arr);
    }

    public function insertaMultiple($sql, $registros, $validacion = null)
    {
        try {
            $this->_mysqli->beginTransaction();
            foreach ($registros as $i => $valores) {
                $stmt = $this->_mysqli->prepare($sql[$i]);
                $result = $stmt->execute($valores);
                if (!$result) {
                    $err = $stmt->errorInfo();
                    $this->_mysqli->rollBack();
                    throw new \Exception("Error: " . print_r($err, 1) . "\nSql : " . $sql[$i] . "\nDatos : " . print_r($valores, 1));
                }
            }

            if ($validacion != null) {
                $stmt = $this->_mysqli->prepare($validacion['query']);
                $stmt->execute($validacion['datos']);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $resValidacion = $validacion['funcion']($result);
                if ($resValidacion['success'] == false) {
                    $this->_mysqli->rollBack();
                    throw new \Exception($resValidacion['mensaje']);
                }
            }

            $this->_mysqli->commit();
            return true;
        } catch (\PDOException $e) {
            $this->_mysqli->rollBack();
            throw new \Exception("Error en insertaMultiple: " . $e->getMessage() . "\nSql : $sql");
        }
    }

    public function EjecutaSP($sp, $parametros)
    {
        try {
            $stmt = $this->_mysqli->prepare($sp);
            $outParam = 'OK';
            foreach ($parametros as $parametro => $valor) {
                if ($valor === "__RETURN__") {
                    $stmt->bindParam($parametro, $outParam, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
                } else {
                    $stmt->bindParam($parametro, $valor);
                }
            }
            $stmt->execute();
            return $outParam;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    public function eliminar($sql)
    {
        try {
            return $this->_mysqli->prepare($sql)->execute();
        } catch (\PDOException $e) {
            throw new \Exception("Error en eliminar: " . $e->getMessage() . "\nSql : $sql");
        }
    }

    public function queryOne($sql, $params = [])
    {
        try {
            $stmt = $this->_mysqli->prepare($sql);
            $stmt->execute($params);
            return array_shift($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (\PDOException $e) {
            self::muestraError($e, $sql, $params);
            return false;
        }
    }

    public function queryAll($sql, $params = [])
    {
        try {
            $stmt = $this->_mysqli->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            self::muestraError($e, $sql, $params);
            return false;
        }
    }

    public static function muestraError($e, $sql, $parametros)
    {
        echo "Error en DB: " . $e->getMessage() . "\nSql: $sql \nParametros:\n" . print_r($parametros, 1);
    }
}
