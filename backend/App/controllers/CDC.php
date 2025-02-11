<?php

namespace App\controllers;

defined("APPPATH") or die("Access denied");

use Core\View;
use Core\Controller;
use App\models\CDC as CDCDao;
use Core\App;

class CDC extends Controller
{
    private $_contenedor;
    private $cnfg;

    function __construct()
    {
        parent::__construct();
        $this->_contenedor = new Contenedor;
        $this->cnfg = App::getConfig();
    }

    public function ConsultaAdmin()
    {
        $js = <<<HTML
            <script>
                {$this->mensajes}
                {$this->configuraTabla}
                {$this->actualizaDatosTabla}
                {$this->consultaServidor}
                {$this->respuestaError}
                {$this->muestraPDF}

                const idTabla = "tablaPrincipal"

                const getParametrosBusqueda = () => {
                    if (!$("#cliente").val()) return showError("Debe proporcionar un número de cliente.")
                    
                    const p = {}

                    p.cliente = $("#cliente").val()

                    return p
                }

                const getParametrosConsulta = () => {
                    if (!$("#autorizacion")[0].files[0]) return showError("Debe seleccionar el archivo de autorización.")
                    if (!$("#ine")[0].files[0]) return showError("Debe seleccionar el archivo de INE.")

                    const datos = new FormData();

                    datos.append("cliente", $("#noCliente").val())
                    datos.append("nombre1", $("#nombre1").val())
                    datos.append("nombre2", $("#nombre2").val() ?? null)
                    datos.append("apellido1", $("#apellido1").val())
                    datos.append("apellido2", $("#apellido2").val())
                    datos.append("fecha", $("#fechaN").val())
                    datos.append("rfc", $("#rfc").val())
                    datos.append("calle", $("#calle").val())
                    datos.append("colonia", $("#colonia").val())
                    datos.append("municipio", $("#municipio").val())
                    datos.append("ciudad", $("#ciudad").val())
                    datos.append("estado", $("#estado").val())
                    datos.append("cp", $("#cp").val())

                    datos.append("autorizacion", $("#autorizacion")[0].files[0])
                    datos.append("ine", $("#ine")[0].files[0])

                    return datos
                }

                const buscarConsultas = () => {
                    const parametros = getParametrosBusqueda()

                    consultaServidor("/CDC/GetResultadosCDC", parametros, (res) => {
                        if (!res.success) return respuestaError(idTabla, res.mensaje)
                        if (res.datos.length === 0) return respuestaError(idTabla, "No se encontró información para el número de cliente proporcionado.")

                        const datos = res.datos.map((item, fila) => {
                            let boton = ""

                            if (item.FOLIO === null) {
                                boton += "<button type='button' class='btn btn-primary' onclick=capturaDatosConsulta(" + fila + ")>Consultar</button>"
                                boton += "<input type='hidden' value='" + JSON.stringify(item) + "' id='" + fila + "'/>"
                            } else {
                                if (fila === 0 && validaCaducidad(item.CADUCIDAD)) {
                                    boton += "<button type='button' class='btn btn-danger' onclick=capturaDatosConsulta(" + fila + ")><i class='glyphicon glyphicon-refresh'>&nbsp;</i>Actualiza consulta</button>"
                                    boton += "<input type='hidden' value='" + JSON.stringify(item) + "' id='" + fila + "'/>"
                                } else {
                                    boton += "<button type='button' class='btn btn-primary' onclick='verPDF(" + item.FOLIO + ", \"autorizacion\")'><i class='glyphicon glyphicon-file'>&nbsp;</i>Autorización</button>"
                                    boton += "<button type='button' class='btn btn-primary' onclick='verPDF(" + item.FOLIO + ",  \"identificacion\")'><i class='glyphicon glyphicon-user'>&nbsp;</i>Identificación</button>"
                                    boton += "<button type='button' class='btn btn-success' onclick='verPDF(" + item.FOLIO + ",  \"reporte\")'><i class='glyphicon glyphicon-eye-open'>&nbsp;</i>Reporte</button>"
                                }
                            }
                            
                            return [
                                item.CLIENTE,
                                item.NOMBRE,
                                item.FOLIO,
                                item.FECHA,
                                item.CADUCIDAD,
                                boton
                            ]
                        })


                        actualizaDatosTabla(idTabla, datos)
                        $(".resultado").toggleClass("conDatos", true)
                    })
                }

                const validaCaducidad = (fecha) => {
                    const partes = fecha.split("/")
                    const caducidad = new Date(
                        parseInt(partes[2], 10),
                        parseInt(partes[1], 10) - 1,
                        parseInt(partes[0], 10)
                    )
                    const hoy = new Date().setHours(0, 0, 0, 0)
                    return caducidad < hoy
                }

                const generaRazones = (razones) => {
                    let ul = "<ul style='padding: 0; margin: 0 15px; text-align: left;'>"
                    razones.forEach((item) => {
                        ul += "<li>" + item.descripcion + "</li>"
                    })

                    ul += "</ul>"
                    return ul
                }

                const actualizaModal = (datos = {}) => {
                    $("#noCliente").val(datos.CLIENTE ?? "")
                    $("#rfc").val(datos.RFC ?? "")
                    $("#fecha").val(datos.NACIMIENTO ?? "")
                    $("#nombre1").val(datos.NOMBRE1 ?? "")
                    $("#nombre2").val(datos.NOMBRE2 ?? "")
                    $("#apellido1").val(datos.PRIMAPE ?? "")
                    $("#apellido2").val(datos.SEGAPE ?? "")
                    $("#calle").val(datos.CALLE ?? "")
                    $("#colonia").val(datos.COLONIA ?? "")
                    $("#municipio").val(datos.MUNICIPIO ?? "")
                    $("#ciudad").val(datos.CIUDAD ?? "")
                    $("#estadoNombre").val(datos.ESTADO_NOMBRE ?? "")
                    $("#estado").val(datos.ESTADO ?? "")
                    $("#cp").val(datos.CP ?? "")

                    $("#autorizacion").val("")
                    $("#ine").val("")
                }

                const capturaDatosConsulta = (id) => {
                    const fila = JSON.parse($("#" + id).val())
                    actualizaModal(fila)
                    $("#modalCDC").modal("show")
                }

                const consultaCDC = () => {
                    const aut = $("#autorizacion")[0].files[0]
                    const ine = $("#ine")[0].files[0]

                    if (validaArchivo(aut) !== true) return
                    if (validaArchivo(ine) !== true) return

                    confirmarMovimiento("Circulo de crédito", "¿Seguro desea consultar la información de este cliente?")
                    .then((continuar) => {
                        if (!continuar) return
                        
                        const parametros = getParametrosConsulta()
                        consultaServidor("/CDC/GeneraConsultaCDC_POST", parametros, (res) => {  
                            if (!res.success) return showError(res.mensaje)
                            showSuccess(res.mensaje)

                            $("#modalCDC").modal("hide")
                            buscarConsultas()
                        }, "POST", "JSON", false, false)
                    })

                }

                const validaArchivo = (archivo) => {
                    if (!archivo) return showError("Debe seleccionar los archivos de soporte.")
                    if (archivo.size > 2097152) return showError("El archivo no debe superar los 2MB.")
                    if (archivo.type !== "application/pdf") return showError("El archivo debe ser de tipo PDF.")

                    return true
                }

                const verPDF = (folio, documento) => {
                    const cliente = $("#" + idTabla + " tbody tr").eq(0).find("td").eq(0).text()
                    let metodo = "GetDocumento"
                    if (documento === "reporte") metodo = "GetReporteCDC"
                    $("#PDF").append($("<embed>", {
                        src: "http://192.168.1.2:7002/CDC/" + metodo + "/?cliente=" + cliente + "&folio=" + folio + "&documento=" + documento,
                        // type: "application/pdf",
                        style: "width: 100%; height: 100%; z-index: 1000; position: relative;",
                        id: "PDF_viewer"
                    }))

                    document.querySelector("#muestraPDF").show()
                }

                const cerrarPDF = () => {
                    $("#PDF_viewer").remove()
                    document.querySelector("#muestraPDF").close()
                }

                $(document).ready(() => {
                    configuraTabla(idTabla)
                    $("#cliente").keypress((e) => { if (e.which === 13) buscarConsultas() })
                    $("#buscar").click(buscarConsultas)
                    $("#consultaCDC").click(consultaCDC)
                })
            </script>
        HTML;

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Reporte de Referencias")));
        View::set('footer', $this->_contenedor->footer($js));
        View::render('cdc_consulta_admin');
    }

    public function Consulta()
    {
        $js = <<<HTML
            <script>
                {$this->mensajes}
                {$this->configuraTabla}
                {$this->actualizaDatosTabla}
                {$this->consultaServidor}
                {$this->respuestaError}
                {$this->muestraPDF}

                const idTabla = "tablaPrincipal"

                const getParametrosBusqueda = () => {
                    if (!$("#cliente").val()) return showError("Debe proporcionar un número de cliente.")
                    
                    const p = {}

                    p.cliente = $("#cliente").val()

                    return p
                }

                const getParametrosConsulta = () => {
                    if (!$("#autorizacion")[0].files[0]) return showError("Debe seleccionar el archivo de autorización.")
                    if (!$("#ine")[0].files[0]) return showError("Debe seleccionar el archivo de INE.")

                    const datos = new FormData();

                    datos.append("cliente", $("#noCliente").val())
                    datos.append("nombre1", $("#nombre1").val())
                    datos.append("nombre2", $("#nombre2").val() ?? null)
                    datos.append("apellido1", $("#apellido1").val())
                    datos.append("apellido2", $("#apellido2").val())
                    datos.append("fecha", $("#fechaN").val())
                    datos.append("rfc", $("#rfc").val())
                    datos.append("calle", $("#calle").val())
                    datos.append("colonia", $("#colonia").val())
                    datos.append("municipio", $("#municipio").val())
                    datos.append("ciudad", $("#ciudad").val())
                    datos.append("estado", $("#estado").val())
                    datos.append("cp", $("#cp").val())

                    datos.append("autorizacion", $("#autorizacion")[0].files[0])
                    datos.append("ine", $("#ine")[0].files[0])

                    return datos
                }

                const getParametrosActualizacion = () => {
                    if (!$("#autorizacion")[0].files[0]) return showError("Debe seleccionar el archivo de autorización.")
                    if (!$("#ine")[0].files[0]) return showError("Debe seleccionar el archivo del INE.")

                    const datos = new FormData();

                    datos.append("cliente", $("#noCliente").val())
                    datos.append("folio", $("#" + idTabla + " tbody tr").eq(0).find("td").eq(2).text())

                    datos.append("autorizacion", $("#autorizacion")[0].files[0])
                    datos.append("ine", $("#ine")[0].files[0])

                    return datos
                }

                const buscarConsultas = () => {
                    const parametros = getParametrosBusqueda()

                    consultaServidor("/CDC/GetResultadosCDC", parametros, (res) => {
                        if (!res.success) return respuestaError(idTabla, res.mensaje)
                        if (res.datos.length === 0) return respuestaError(idTabla, "No se encontró información para el número de cliente proporcionado.")
                        
                        const datos = res.datos.map((item, fila) => {
                            const caducidad = new Date(item.CADUCIDAD)
                            let boton = ""

                            if (item.FOLIO === null) {
                                boton += "<button type='button' class='btn btn-primary' onclick='capturaDatosConsulta(" + fila + ", true)'>Consultar</button>"
                                boton += "<input type='hidden' value='" + JSON.stringify(item) + "' id='" + fila + "'/>"
                            } else if (item.AUTORIZACION == 0 || item.IDENTIFICACION == 0) {
                                boton += "<button type='button' class='btn btn-primary' onclick='capturaDatosConsulta(" + fila + ", false)'><i class='glyphicon glyphicon-cloud-upload'>&nbsp;</i>Subir Documentos</button>"
                                boton += "<input type='hidden' value='" + JSON.stringify(item) + "' id='" + fila + "'/>"
                            } else {
                                if (fila === 0 && validaCaducidad(item.CADUCIDAD)) {
                                    boton += "<button type='button' class='btn btn-danger' onclick='capturaDatosConsulta(" + fila + ", true)'><i class='glyphicon glyphicon-refresh'>&nbsp;</i>Actualiza consulta</button>"
                                    boton += "<input type='hidden' value='" + JSON.stringify(item) + "' id='" + fila + "'/>"
                                } else boton += "<button type='button' class='btn btn-success' onclick=verPDF()><i class='glyphicon glyphicon-eye-open'>&nbsp;</i>Reporte</button>"
                            }
                            
                            return [
                                item.CLIENTE,
                                item.NOMBRE,
                                item.FOLIO,
                                item.FECHA,
                                item.CADUCIDAD,
                                boton
                            ]
                        })

                        actualizaDatosTabla(idTabla, datos)
                        $(".resultado").toggleClass("conDatos", true)
                    })
                }

                const validaCaducidad = (fecha) => {
                    const partes = fecha.split("/")
                    const caducidad = new Date(
                        parseInt(partes[2], 10),
                        parseInt(partes[1], 10) - 1,
                        parseInt(partes[0], 10)
                    )
                    const hoy = new Date().setHours(0, 0, 0, 0)
                    return caducidad < hoy
                }

                const actualizaModal = (datos = {}) => {
                    $("#noCliente").val(datos.CLIENTE ?? "")
                    $("#rfc").val(datos.RFC ?? "")
                    $("#fecha").val(datos.NACIMIENTO ?? "")
                    $("#nombre1").val(datos.NOMBRE1 ?? "")
                    $("#nombre2").val(datos.NOMBRE2 ?? "")
                    $("#apellido1").val(datos.PRIMAPE ?? "")
                    $("#apellido2").val(datos.SEGAPE ?? "")
                    $("#calle").val(datos.CALLE ?? "")
                    $("#colonia").val(datos.COLONIA ?? "")
                    $("#municipio").val(datos.MUNICIPIO ?? "")
                    $("#ciudad").val(datos.CIUDAD ?? "")
                    $("#estadoNombre").val(datos.ESTADO_NOMBRE ?? "")
                    $("#estado").val(datos.ESTADO ?? "")
                    $("#cp").val(datos.CP ?? "")

                    $("#autorizacion").val("")
                    $("#ine").val("")
                }

                const capturaDatosConsulta = (id, consulta) => {
                    const fila = JSON.parse($("#" + id).val())
                    actualizaModal(fila)

                    $("#consultaCDC").text(consulta ? "Consultar": "Subir Documentos")
                    $("#modalCDC").modal("show")
                }

                const consultaCDC = () => {
                    const aut = $("#autorizacion")[0].files[0]
                    const ine = $("#ine")[0].files[0]

                    if (validaArchivo(aut) !== true) return
                    if (validaArchivo(ine) !== true) return

                    let msjConf = "", metodo = "", parametros = {}
                    if ($("#consultaCDC").text() === "Consultar") {
                        msjConf = "¿Seguro desea consultar la información de este cliente?"
                        metodo = "GeneraConsultaCDC_POST"
                        parametros = getParametrosConsulta()
                    } else {
                        msjConf = "¿Seguro desea subir estos archivos?"
                        metodo = "SubeDocumentosCDC"
                        parametros = getParametrosActualizacion()
                    }

                    confirmarMovimiento("Circulo de crédito", msjConf)
                    .then((continuar) => {
                        if (!continuar) return
                        
                        const parametros = getParametrosActualizacion()
                        consultaServidor("/CDC/" + metodo, parametros, (res) => {  
                            if (!res.success) return showError(res.mensaje)
                            showSuccess(res.mensaje)

                            $("#modalCDC").modal("hide")
                            buscarConsultas()
                        }, "POST", "JSON", false, false)
                    })
                }

                const validaArchivo = (archivo) => {
                    if (!archivo) return showError("Debe seleccionar los archivos de soporte.")
                    if (archivo.size > 2097152) return showError("El archivo no debe superar los 2MB.")
                    if (archivo.type !== "application/pdf") return showError("El archivo debe ser de tipo PDF.")

                    return true
                }

                const verPDF = () => {
                    const cliente = $("#" + idTabla + " tbody tr").eq(0).find("td").eq(0).text()
                    $("#PDF").append($("<embed>", {
                        src: "http://192.168.1.2:7002/CDC/GetReporteCDC/?cliente=" + cliente,
                        style: "width: 100%; height: 100%; z-index: 1000; position: relative;",
                        id: "PDF_viewer"
                    }))

                    document.querySelector("#muestraPDF").show()
                }

                const cerrarPDF = () => {
                    $("#PDF_viewer").remove()
                    document.querySelector("#muestraPDF").close()
                }

                $(document).ready(() => {
                    configuraTabla(idTabla)
                    $("#cliente").keypress((e) => { if (e.which === 13) buscarConsultas() })
                    $("#buscar").click(buscarConsultas)
                    $("#consultaCDC").click(consultaCDC)
                })
            </script>
        HTML;

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Reporte de Referencias")));
        View::set('footer', $this->_contenedor->footer($js));
        View::render('cdc_consulta');
    }

    public function ConsultaCDC($cuerpo, $ep, $config = [])
    {
        $cnfg = [
            'CERT_CDC' => $this->cnfg['CERT_CDC'],
            'CERT_CULTIVA' => $this->cnfg['CERT_CULTIVA'],
            'PASS_CERT' => $this->cnfg['PASS_CERT'],
            'URL' => $config['URL'] ?? $this->cnfg['URL_CDC'],
            'API_KEY' => $config['API_KEY'] ?? $this->cnfg['API_KEY'],
            'valida' => $config['valida'] ?? true
        ];
        $firmas = new \SignatureService($cnfg['CERT_CDC'], $cnfg['CERT_CULTIVA'], $cnfg['PASS_CERT']);

        try {
            $firma = $firmas->generateDigitalSignature($cuerpo);
            $ci = curl_init();
            $hdrs = [];
            curl_setopt_array($ci, [
                CURLOPT_URL => $cnfg['URL'] . $ep,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'x-api-key: ' . $cnfg['API_KEY'],
                    'x-signature: ' . $firma
                ],
                CURLOPT_POSTFIELDS => $cuerpo,
                CURLOPT_HEADERFUNCTION => function ($curl, $hdr) use (&$hdrs) {
                    $h = explode(":", $hdr, 2);
                    if (count($h) == 2) $hdrs[trim($h[0])] = trim($h[1]);
                    return strlen($hdr);
                }
            ]);

            $resultado = curl_exec($ci);
            if (curl_errno($ci)) return self::GetRespuesta(false, 'No se logro consultar el servicio de CDC.', null, curl_error($ci));
            if (curl_getinfo($ci, CURLINFO_HTTP_CODE) !== 200) return self::GetRespuesta(false, 'Error al consultar los servicios de CDC.', null, json_decode($resultado, true));
            if ($cnfg['valida'] && !$firmas->isDigitalSigantureValid($resultado, $hdrs['x-signature'])) return self::GetRespuesta(false, 'Error en la respuesta de CDC.', null, 'La firma de respuesta no es válida.');

            $resJSON = json_decode($resultado, true) ?? $resultado;
            return self::GetRespuesta(true, 'Consulta exitosa', $resJSON);
        } catch (\Exception $e) {
            return self::GetRespuesta(false, 'Error al consultar la información del cliente en CDC.', null, $e->getMessage());
        } finally {
            if ($ci) curl_close($ci);
        }
    }

    public function SetResultadoCDC($infoCliente, $consultaCDC)
    {
        $guardar = [
            'cliente' => $infoCliente['cliente'],
            'folio' => $consultaCDC['folioConsulta'],
            'resultado' => json_encode($consultaCDC),
            'usuario' => $_SESSION['usuario'] ?? null
        ];

        if ($infoCliente['autorizacion']) $guardar['autorizacion'] = $infoCliente['autorizacion'];
        if ($infoCliente['ine']) $guardar['ine'] = $infoCliente['ine'];
        return CDCDao::SetResultadoCDC($guardar);
    }

    public function GetResultadosCDC()
    {
        $resultado = CDCDao::GetResultadosCDC($_POST);
        if ($resultado['success'] && count($resultado['datos']) !== 0) {
            foreach ($resultado['datos'] as $key => &$value) {
                $value['RESULTADO'] = null;
                // if (is_resource($value['RESULTADO'])) {
                //     $contenido = stream_get_contents($resultado['datos'][$key]['RESULTADO']);
                //     fclose($resultado['datos'][$key]['RESULTADO']);
                //     $value['RESULTADO'] = $contenido;
                // }
            }
        }

        self::RespondeJSON($resultado);
    }

    public function GetDocumento()
    {
        $archivo = CDCDao::GetDocumento($_GET);

        if (!$archivo['success']) {
            echo self::ErrorPDF($archivo['mensaje']);
            return;
        }

        if ($archivo['datos']['PDF'] === null) {
            echo self::ErrorPDF('El documento solicitado no ha sido registrado aun.');
            return;
        }

        $contenido = $archivo['datos']['PDF'];
        $archivo = is_resource($contenido) ? stream_get_contents($contenido) : $contenido;

        header("Content-Type: application/pdf");
        header('Content-Disposition: inline; filename="' . $_GET['documento'] . '.pdf"');
        header('Content-Transfer-Encoding: binary');
        header("Content-Length: " . strlen($archivo));
        echo $archivo;

        ob_clean();
        flush();
    }

    public function GeneraConsultaCDC_POST()
    {
        $cdc = self::ReporteConsolidado($_POST);

        if (!$cdc['success']) return self::RespondeJSON($cdc);
        else {
            try {
                if (!isset($_FILES['autorizacion']) && $_FILES['autorizacion']['error'] !== UPLOAD_ERR_OK) return self::Responde(false, 'No se incluyó el archivo de autorización.');
                if (!isset($_FILES['ine']) && $_FILES['ine']['error'] !== UPLOAD_ERR_OK) return self::Responde(false, 'No se incluyó el archivo de INE.');

                $_POST['autorizacion'] = fopen($_FILES['autorizacion']['tmp_name'], 'rb');
                $_POST['ine'] = fopen($_FILES['ine']['tmp_name'], 'rb');
                self::RespondeJSON(self::SetResultadoCDC($_POST, $cdc['datos']));
            } catch (\Exception $e) {
                return self::Responde(false, 'Error al guardar los datos en la base.', null, $e->getMessage());
            } finally {
                if (isset($_POST['autorizacion'])) fclose($_POST['autorizacion']);
                if (isset($_POST['ine'])) fclose($_POST['ine']);
            }
        }
    }

    public function GeneraConsultaCDC_API()
    {
        $resultado = CDCDao::GetResultadosCDC($_GET);

        if (!$resultado['success']) return self::Responde(false, 'Ocurrió un error al consultar la información del cliente en la base de datos, favor de intentarlo nuevamente en unos minutos.\n Si el problema persiste contacte a soporte técnico.', null, $resultado['error']);

        $resultado = $resultado['datos'];
        if (count($resultado) === 0) return self::Responde(false, 'No se encontró información para el numero de cliente ' . $_GET['cliente'] . ' en la base de datos.');

        $ultimaConsulta = $resultado[0];
        if ($ultimaConsulta['FOLIO'] !== null) {
            $caducidad = date_create_from_format('d/m/Y', $ultimaConsulta['CADUCIDAD']);
            $actual = new \DateTime();
            if ($caducidad > $actual) return self::Responde(true, 'El cliente cuenta con una consulta en circulo de crédito vigente, puede consultar la información en el sistema de reportería (CULTIVA).');
        }

        $infoCliente = [
            'cliente' => $ultimaConsulta['CLIENTE'],
            'nombre1' => $ultimaConsulta['NOMBRE1'],
            'nombre2' => $ultimaConsulta['NOMBRE2'],
            'apellido1' => $ultimaConsulta['PRIMAPE'],
            'apellido2' => $ultimaConsulta['SEGAPE'],
            'fecha' => $ultimaConsulta['NACIMIENTO'],
            'rfc' => $ultimaConsulta['RFC'],
            'calle' => $ultimaConsulta['CALLE'],
            'colonia' => $ultimaConsulta['COLONIA'],
            'municipio' => $ultimaConsulta['MUNICIPIO'],
            'ciudad' => $ultimaConsulta['CIUDAD'],
            'estado' => $ultimaConsulta['ESTADO'],
            'cp' => $ultimaConsulta['CP']
        ];

        $cdc = self::ReporteConsolidado($infoCliente);
        if (!$cdc['success']) self::RespondeJSON($cdc);
        else self::RespondeJSON(self::SetResultadoCDC($infoCliente, $cdc['datos']));
    }

    public function SubeDocumentosCDC()
    {
        try {
            if (!isset($_FILES['autorizacion']) && $_FILES['autorizacion']['error'] !== UPLOAD_ERR_OK) return self::Responde(false, 'No se incluyó el archivo de autorización.');
            if (!isset($_FILES['ine']) && $_FILES['ine']['error'] !== UPLOAD_ERR_OK) return self::Responde(false, 'No se incluyó el archivo de INE.');

            $_POST['autorizacion'] = fopen($_FILES['autorizacion']['tmp_name'], 'rb');
            $_POST['ine'] = fopen($_FILES['ine']['tmp_name'], 'rb');
            self::RespondeJSON(CDCDao::SetDocumentosCDC($_POST));
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al guardar los documentos en la base.', null, $e->getMessage());
        } finally {
            if (isset($_POST['autorizacion'])) fclose($_POST['autorizacion']);
            if (isset($_POST['ine'])) fclose($_POST['ine']);
        }
    }

    public function GetReporteCDC()
    {
        $resultado = CDCDao::GetResultadosCDC($_GET);
        if (!$resultado['success'] || count($resultado['datos']) === 0 || $resultado['datos'][0]['RESULTADO'] === null) {
            echo self::ErrorPDF('No se encontró información para el número de cliente proporcionado.');
            return;
        }

        $consulta = json_decode(stream_get_contents($resultado['datos'][0]['RESULTADO']), true);

        $nombreArchivo = 'Reporte CDC ' . $resultado['datos'][0]['CLIENTE'];
        $mpdf = new \Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'default_font_size' => 6,
            'default_font' => 'Arial',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_header' => 0,
            'margin_footer' => 5,
        ]);
        $mpdf->SetTitle($nombreArchivo);

        $fi = date('d/m/Y H:i:s');
        $pie = <<< HTML
            <table style="width: 100%; font-size: 10px">
                <tr>
                    <td style="text-align: left; width: 50%;">
                        Fecha de impresión  {$fi}
                    </td>
                    <td style="text-align: right; width: 50%;">
                        Página {PAGENO} de {nb}
                    </td>
                </tr>
            </table>
        HTML;

        $mpdf->SetHTMLFooter($pie);

        // Estilos generales
        $estilo = <<<HTML
            <style>
                body {
                    margin: 0;
                    padding: 0;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                }
                .tituloTablas {
                    text-align: left;
                    font-weight: bold;
                    font-size: 15px;
                }
            </style>
        HTML;
        $mpdf->WriteHTML($estilo, 1);

        $cuerpo = self::generarTablaHtml('Datos generales', [$consulta['persona']], [
            ['titulo' => 'Nombre (s)', 'campo' => 'nombres', 'formato' => 'centrado'],
            ['titulo' => 'Primer apellido', 'campo' => 'apellidoPaterno', 'formato' => 'centrado'],
            ['titulo' => 'Segundo apellido', 'campo' => 'apellidoMaterno', 'formato' => 'centrado'],
            ['titulo' => 'Fecha de nacimiento', 'campo' => 'fechaNacimiento', 'formato' => 'fecha'],
            ['titulo' => 'RFC', 'campo' => 'RFC', 'formato' => 'centrado'],
        ]);

        // DOMICILIOS
        $cuerpo .= self::generarTablaHtml('Domicilios', $consulta['domicilios'], [
            ['titulo' => 'Calle y número', 'campo' => 'direccion'],
            ['titulo' => 'Colonia', 'campo' => 'coloniaPoblacion'],
            ['titulo' => 'Del/Mpio', 'campo' => 'delegacionMunicipio'],
            ['titulo' => 'Ciudad', 'campo' => 'ciudad'],
            ['titulo' => 'Estado', 'campo' => 'estado', 'formato' => 'centrado'],
            ['titulo' => 'CP', 'campo' => 'CP', 'formato' => 'centrado'],
            ['titulo' => 'Teléfono', 'campo' => 'numeroTelefono', 'formato' => 'centrado'],
            ['titulo' => 'Fecha de', 'campo' => 'fechaResidencia', 'formato' => 'fecha'],
        ], true);

        // CREDITOS
        $cuerpo .= self::generarTablaHtml('Créditos otorgados', $consulta['creditos'], [
            ['titulo' => 'Otorgante', 'campo' => 'NombreOtorgante'],
            ['titulo' => 'Apertura', 'campo' => 'FechaAperturaCuenta', 'formato' => 'fecha'],
            ['titulo' => 'Ultimo pago', 'campo' => 'FechaUltimoPago', 'formato' => 'fecha'],
            ['titulo' => 'Limite de crédito', 'campo' => 'LimiteCredito', 'formato' => 'moneda'],
            ['titulo' => 'Saldo actual', 'campo' => 'SaldoActual', 'formato' => 'moneda'],
            ['titulo' => 'Saldo vencido', 'campo' => 'SaldoVencido', 'formato' => 'moneda'],
        ]);

        // EMPLEOS
        $cuerpo .= self::generarTablaHtml('Empleos Registrados', $consulta['empleos'], [
            ['titulo' => 'Empresa', 'campo' => 'nombreEmpresa'],
            ['titulo' => 'Puesto', 'campo' => 'puesto'],
            ['titulo' => 'Contratación', 'campo' => 'fechaContratacion', 'formato' => 'fecha'],
            ['titulo' => 'Salario', 'campo' => 'salarioMensual', 'formato' => 'moneda'],
        ]);

        // CONSULTAS
        $cuerpo .= self::generarTablaHtml('Consultas realizadas', $consulta['consultas'], [
            ['titulo' => 'Fecha de Consulta', 'campo' => 'fechaConsulta', 'formato' => 'fecha'],
            ['titulo' => 'Otorgante', 'campo' => 'nombreOtorgante'],
            ['titulo' => 'Tipo de crédito', 'campo' => 'tipoCredito', 'formato' => 'centrado'],
            ['titulo' => 'Monto', 'campo' => 'importeCredito', 'formato' => 'moneda'],
            ['titulo' => 'Moneda', 'campo' => 'claveUnidadMonetaria', 'formato' => 'centrado'],
        ]);

        $mpdf->WriteHTML($cuerpo, 2);
        $mpdf->Output($nombreArchivo . '.pdf', 'I');
    }

    private function generarTablaHtml($titulo, $datos, $columnas, $indice = false)
    {
        $noCol = count($columnas);
        if ($indice) $noCol++;

        $html = <<<HTML
            <table>
                <thead>
                    <tr>
                        <td colspan="{$noCol}" class="tituloTablas">
                            $titulo
                        </td>
                    </tr>
                    <tr>
        HTML;

        if ($indice) $html .= "<th style='border: 1px solid #006699;'>#</th>";

        foreach ($columnas as $columna) {
            $html .= "<th style='border: 1px solid #006699;'>{$columna['titulo']}</th>";
        }

        $html .= "</tr></thead><tbody>";

        // Añadir las filas de datos
        $i = 0;
        foreach ($datos as $dato) {
            $html .= "<tr>";
            $i++;
            if ($indice) $html .= "<td style='border: 1px solid #006699; text-align: center;'>{$i}</td>";
            foreach ($columnas as $columna) {
                [$valor, $estilo] = self::AplicaEstilos($dato[$columna['campo']], $columna['formato']);
                $html .= "<td style='border: 1px solid #006699; {$estilo}'>{$valor}</td>";
            }
            $html .= "</tr>";
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function AplicaEstilos($valor, $formato = null)
    {
        if ($formato === 'moneda') return ['$' . number_format($valor, 2), 'text-align: right;'];
        if ($formato === 'fecha') return [self::formatearFecha($valor), 'text-align: center;'];
        if ($formato === 'centrado') return [$valor, 'text-align: center;'];
        return [$valor, ''];
    }

    private function formatearFecha($fecha)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $fecha);
        $meses = [
            'Jan' => 'ENE',
            'Feb' => 'FEB',
            'Mar' => 'MAR',
            'Apr' => 'ABR',
            'May' => 'MAY',
            'Jun' => 'JUN',
            'Jul' => 'JUL',
            'Aug' => 'AGO',
            'Sep' => 'SEP',
            'Oct' => 'OCT',
            'Nov' => 'NOV',
            'Dec' => 'DIC'
        ];
        $mes = $meses[$date->format('M')];
        return $date->format('d') . '/' . $mes . '/' . $date->format('y');
    }

    private function AplicaCatalogos($datos, $catalogo)
    {
        $estados = [
            'AGS' => 'AGUASCALIENTES',
            'BC' => 'BAJA CALIFORNIA',
            'BCS' => 'BAJA CALIFORNIA SUR',
            'CAMP' => 'CAMPECHE',
            'CHIS' => 'CHIAPAS',
            'CHIH' => 'CHIHUAHUA',
            'CDMX' => 'CIUDAD DE MÉXICO',
            'COAH' => 'COAHUILA',
            'COL' => 'COLIMA',
            'DGO' => 'DURANGO',
            'GTO' => 'GUANAJUATO',
            'GRO' => 'GUERRERO',
            'HGO' => 'HIDALGO',
            'JAL' => 'JALISCO',
            'MEX' => 'MÉXICO',
            'MICH' => 'MICHOACÁN',
            'MOR' => 'MORELOS',
            'NAY' => 'NAYARIT',
            'NL' => 'NUEVO LEÓN',
            'OAX' => 'OAXACA',
            'PUE' => 'PUEBLA',
            'QRO' => 'QUERÉTARO',
            'QROO' => 'QUINTANA ROO',
            'SLP' => 'SAN LUIS POTOSÍ',
            'SIN' => 'SINALOA',
            'SON' => 'SONORA',
            'TAB' => 'TABASCO',
            'TAMPS' => 'TAMAULIPAS',
            'TLAX' => 'TLAXCALA',
            'VER' => 'VERACRUZ',
            'YUC' => 'YUCATÁN',
            'ZAC' => 'ZACATECAS'
        ];

        $tiposCredito = [
            'AA' => 'Arrendamiento Automotriz',
            'AB' => 'Automotriz Bancario',
            'AE' => 'Física Actividad Empresarial',
            'AM' => 'Aparatos/Muebles',
            'AR' => 'Arrendamiento',
            'AV' => 'Aviación',
            'BC' => 'Banca Comunal',
            'BL' => 'Bote/Lancha',
            'BR' => 'Bienes Raíces',
            'CA' => 'Compra De Automóvil',
            'CC' => 'Crédito Al Consumo',
            'CF' => 'Crédito Fiscal',
            'CO' => 'Consolidación',
            'CP' => 'Crédito Personal Al Consumo',
            'ED' => 'Editorial',
            'EQ' => 'Equipo',
            'FF' => 'Fondeo Fira',
            'FI' => 'Fianza',
            'FT' => 'Factoraje',
            'GS' => 'Grupo Solidario',
            'HB' => 'Hipotecario Bancario',
            'HE' => 'Préstamo Tipo Home Equity',
            'HV' => 'Hipotecario o Vivienda',
            'LC' => 'Línea de Crédito',
            'MC' => 'Mejoras a la Casa',
            'NG' => 'Préstamo No Garantizado',
            'PB' => 'Préstamo Personal Bancario',
            'PC' => 'Procampo',
            'PE' => 'Préstamo Para Estudiante',
            'PG' => 'Préstamo Garantizado',
            'PQ' => 'Préstamo Quirografario',
            'PM' => 'Préstamo Empresarial',
            'PN' => 'Préstamo de Nómina',
            'PP' => 'Préstamo Personal',
            'SH' => 'Segunda Hipoteca',
            'TC' => 'Tarjeta De Crédito',
            'TD' => 'Tarjeta Departamental',
            'TG' => 'Tarjeta Garantizada',
            'TS' => 'Tarjeta De Servicios',
            'VR' => 'Vehículo Recreativo',
            'OT' => 'Otros',
            'NC' => 'Desconocido'
        ];

        $cat = null;
        if ($catalogo === 'estados') $cat = $estados;
        if ($catalogo === 'tiposCredito') $cat = $tiposCredito;
        if ($cat === null) return $datos;

        foreach ($datos as &$dato) {
            foreach ($dato as $campo => &$valor) {
                $valor = $cat[$valor] ?? $valor;
            }
        }

        return $datos;
    }

    public function SecurityTest()
    {
        $ep = 'v1/securitytest';
        $resultado = $this->ConsultaCDC("{'mensaje': 'Prueba conectividad a producción CDC'}", $ep);
        $this->RespondeJSON($resultado);
    }

    public function ReporteConsolidado($datos)
    {
        $ep = 'v2/rcc';

        $campos = [
            'primerNombre' => 'JUAN' ?? $datos['nombre1'],
            'segundoNombre' => null, // ?? $datos['nombre2'],
            'apellidoPaterno' => 'SESENTA' ?? $datos['apellido1'],
            'apellidoMaterno' => 'PRUEBA' ?? $datos['apellido2'],
            'fechaNacimiento' => '1944-01-04' ?? $datos['fecha'],
            'RFC' => 'SEPJ440104K91' ?? $datos['rfc'],
            'nacionalidad' => 'MX' ?? $datos['nacionalidad'],
            'domicilio' => [
                'direccion' => 'PASADISO ENCONTRADO 772' ?? $datos['calle'],
                'coloniaPoblacion' => 'JOSÉ VASCONCELOS CALDERÓN' ?? $datos['colonia'],
                'delegacionMunicipio' => 'AGUASCALIENTES' ?? $datos['municipio'],
                'ciudad' => 'AGUASCALIENTES' ?? $datos['ciudad'],
                'estado' => 'AGS' ?? $datos['estado'],
                'CP' => '20200' ?? $datos['cp']
            ]
        ];
        $campos = json_encode($campos);

        // Esta configuración debe comentarse al pasar a producción
        $TST_cnfg = $this->GetTstCnfg();

        return $this->ConsultaCDC($campos, $ep, $TST_cnfg);
    }

    private function GetTstCnfg()
    {
        return [
            'URL' => $this->cnfg['URL_CDC_TST'],
            'API_KEY' => $this->cnfg['API_KEY_TST'],
            'valida' => false
        ];
    }
}
