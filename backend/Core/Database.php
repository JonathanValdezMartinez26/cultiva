<?php

namespace Core;

use PDO;

/**
 * @class Conn
 */

class Database
{
    private $db_mcm;
    private $db_cultiva;
    public $db_activa;

    function __construct()
    {
        $this->DB_CULTIVA();
        $this->DB_MCM();

        // La base por defecto seria MCM
        // $this->db_activa = $this->db_mcm;

        // La base por defecto seria CULTIVA
        $this->db_activa = $this->db_cultiva;
    }

    private function Conecta($s, $u = null, $p = null)
    {
        $host = 'oci:dbname=//' . $s . ':1521/ESIACOM;charset=UTF8';
        $usuario = $u ?? 'ESIACOM';
        $password = $p ?? 'ESIACOM';
        try {
            return new PDO($host, $usuario, $password);
        } catch (\PDOException $e) {
            echo self::muestraError($e);
            return null;
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

    private function DB_MCM()
    {
        // $servidor = 'mcm-server';
        $servidor = '25.13.83.206';
        $this->db_mcm = self::Conecta($servidor);
    }

    private function DB_CULTIVA()
    {
        $servidor = '25.95.21.168';
        $this->db_cultiva = self::Conecta($servidor);
    }

    public function SetDB_MCM()
    {
        $this->db_activa = $this->db_mcm;
    }

    public function SetDB_CULTIVA()
    {
        $this->db_activa = $this->db_cultiva;
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
