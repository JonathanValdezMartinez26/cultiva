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
        $s = $this->configuracion[$s] ?? $s;
        $host = 'oci:dbname=//' . ($s ?? $this->configuracion['SERVIDOR']) . ':1521/ESIACOM;charset=UTF8';
        $usuario = $u ?? $this->configuracion['USUARIO'];
        $password = $p ?? $this->configuracion['PASSWORD'];
        try {
            $this->db_activa =  new PDO($host, $usuario, $password);
        } catch (\PDOException $e) {
            self::baseNoDisponible($e->getMessage());
            $this->db_activa =  null;
        }
    }

    private function baseNoDisponible($mensaje)
    {
        http_response_code(503);
        echo <<<HTML
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Sistema fuera de línea</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        background-color: #f4f4f4;
                        color: #333;
                        margin: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                    }
                    .container {
                        background-color: #fff;
                        padding: 20px;
                        border-radius: 10px;
                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    }
                    h1 {
                        font-size: 2em;
                        color: #d9534f;
                    }
                    p {
                        font-size: 1.2em;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Sistema fuera de línea</h1>
                    <p>Estamos trabajando para resolver la situación. Por favor, vuelva a intentarlo más tarde.</p>
                </div>
                <input type="hidden" id="baseNoDisponible" value="$mensaje">
            </body>
            <script>
                window.onload = () => {
                    console.log(document.getElementById('baseNoDisponible').value)
                }
            </script>
            </html>
        HTML;
        exit();
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

    public function insertarBlob($sql, $datos, $blob = [], $clob = [])
    {
        if (!is_array($sql)) {
            $sql = [$sql];
            $datos = [$datos];
        }

        try {
            $this->db_activa->beginTransaction();

            foreach ($sql as $i => $value) {
                $stmt = $this->db_activa->prepare($sql[$i]);
                foreach ($datos[$i] as $key => $value) {
                    if (in_array($key, $blob)) $stmt->bindParam($key, $datos[$i][$key], PDO::PARAM_LOB);
                    else if (in_array($key, $clob)) $stmt->bindParam($key, $datos[$i][$key], PDO::PARAM_STR, strlen($datos[$i][$key]));
                    else $stmt->bindParam($key, $datos[$i][$key]);
                }

                if (!$stmt->execute()) throw new \Exception("Error en insertarBlob: " . print_r($this->db_activa->errorInfo(), 1) . "\nSql : $sql[$i] \nDatos : " . print_r($datos[$i], 1));
                if ($stmt->errorInfo()[0] != '00000') throw new \Exception("Error en insertarBlob: " . print_r($stmt->errorInfo(), 1) . "\nSql : $sql[$i] \nDatos : " . print_r($datos[$i], 1));
            }
            $this->db_activa->commit();
        } catch (\PDOException $e) {
            $this->db_activa->rollBack();
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            $this->db_activa->rollBack();
            throw new \Exception($e->getMessage());
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
