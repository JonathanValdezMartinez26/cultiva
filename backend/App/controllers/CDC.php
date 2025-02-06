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

    function __construct()
    {
        parent::__construct();
        $this->_contenedor = new Contenedor;
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
                    datos.append("nombre2", $("#nombre2").val())
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

                    consultaServidor("/CDC/GetResultadoCDC", parametros, (res) => {
                        if (!res.success) return respuestaError(idTabla, res.mensaje)
                        if (res.datos.length === 0) return respuestaError(idTabla, "No se encontró información para el número de cliente proporcionado.")

                        
                        const datos = res.datos.map((item, fila) => {
                            let boton = null, calificacion = null, razones = null, nivel = null

                            if (item.FOLIO === null) {
                                boton = "<button type='button' class='btn btn-primary' onclick=capturaDatosConsulta(" + fila + ")>Consultar</button>"
                                boton += "<input type='hidden' value='" + JSON.stringify(item) + "' id='" + fila + "'/>"
                            } else {
                                boton = "<button type='button' class='btn btn-primary' onclick='verPDF(" + item.FOLIO + ", \"autorizacion\")'><i class='glyphicon glyphicon-file'></i></button>"
                                boton += "<button type='button' class='btn btn-primary' onclick='verPDF(" + item.FOLIO + ",  \"identificacion\")'><i class='glyphicon glyphicon-user'></i></button>"
                                const resultado = JSON.parse(item.RESULTADO)
                                const score = resultado.scores[0]
                                calificacion = score.score
                                razones = generaRazones(score.razones)

                                if (calificacion <= 470) nivel = "<span style='color: red; font-weight: bold; font-size: medium;'>Bajo</span>"
                                else if (calificacion <= 550) nivel = "<span style='color: orange; font-weight: bold; font-size: medium;'>Moderado</span>"
                                else if (calificacion <= 700) nivel = "<span style='color: green; font-weight: bold; font-size: medium;'>Bueno</span>"
                                else nivel = "<span style='color: blue; font-weight: bold; font-size: medium;'>Excelente</span>"
                            }
                            
                            return [
                                item.CLIENTE,
                                item.NOMBRE,
                                item.FECHA,
                                calificacion,
                                nivel,
                                razones,
                                item.CADUCIDAD,
                                boton
                            ]
                        })


                        actualizaDatosTabla(idTabla, datos)
                        $(".resultado").toggleClass("conDatos", true)
                    })
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
                        consultaServidor("/CDC/ConsultaCDC", parametros, (res) => {  
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
                    $("#PDF").append($("<embed>", {
                        src: "http://192.168.1.2:7002/CDC/GetDocumento/?cliente=" + cliente + "&folio=" + folio + "&documento=" + documento,
                        type: "application/pdf",
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

    public function ConsultaCDC()
    {
        // $datos = [
        //     'primerNombre' => $_POST['nombre1'],
        //     'segundoNombre' => $_POST['nombre2'],
        //     'apellidoPaterno' => $_POST['apellido1'],
        //     'apellidoMaterno' => $_POST['apellido2'],
        //     'fechaNacimiento' => $_POST['fecha'],
        //     'rfc' => $_POST['rfc'],
        //     'domicilio' => [
        //         'direccion' => $_POST['calle'],
        //         'colonia' => $_POST['colonia'],
        //         'municipio' => $_POST['municipio'],
        //         'ciudad' => $_POST['ciudad'],
        //         'estado' => $_POST['estado'],
        //         'codigoPostal' => $_POST['cp']
        //     ]
        // ];

        // Se crea el JSON con los datos temporales de prueba
        $datos = [
            'primerNombre' => 'ROBERTO',
            'segundoNombre' => '',
            'apellidoPaterno' => 'SAHAGUN',
            'apellidoMaterno' => 'ZARAGOZA',
            'fechaNacimiento' => '2001-01-01',
            'rfc' => 'SAZR010101',
            'domicilio' => [
                'direccion' => 'HIDALGO 32',
                'colonia' => 'CENTRO',
                'municipio' => 'LA BARCA',
                'ciudad' => 'BENITO JUAREZ',
                'estado' => 'JAL',
                'codigoPostal' => '47917'
            ]
        ];

        $url = App::getConfig()['URL_CDC'];

        $ci = curl_init();
        curl_setopt_array($ci, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: jqAhoWslVLi7BiAYbvXk7AoLt2Sm3fiz'
            ],
            CURLOPT_POSTFIELDS => json_encode($datos)
        ]);

        $res = curl_exec($ci);

        if (curl_errno($ci))
            echo json_encode(['success' => false, 'mensaje' => 'Error al consultar la información del cliente en CDC.', 'error' => curl_error($ci)]);
        else {
            self::SetResultadoCDC($res);
        }

        curl_close($ci);
    }

    public function SetResultadoCDC($res)
    {
        $resultado = json_decode($res, true);

        $guardar = [
            'cliente' => $_POST['cliente'],
            'folio' => $resultado['folioConsulta'],
            'resultado' => json_encode($resultado),
            'usuario' => $_SESSION['usuario']
        ];

        if (isset($_FILES['autorizacion']) && $_FILES['autorizacion']['error'] === UPLOAD_ERR_OK)
            $guardar['autorizacion'] = fopen($_FILES['autorizacion']['tmp_name'], 'rb');

        if (isset($_FILES['ine']) && $_FILES['ine']['error'] === UPLOAD_ERR_OK)
            $guardar['ine'] = fopen($_FILES['ine']['tmp_name'], 'rb');

        echo json_encode(CDCDao::SetResultadoCDC($guardar));

        if (isset($guardar['autorizacion'])) fclose($guardar['autorizacion']);
        if (isset($guardar['ine'])) fclose($guardar['ine']);
    }

    public function GetResultadoCDC()
    {
        echo json_encode(CDCDao::GetResultadoCDC($_POST));
    }

    public function GetDocumento()
    {
        $archivo = CDCDao::GetDocumento($_GET);

        if (!$archivo['success']) {
            echo "No se encontró el archivo solicitado.";
            return;
        }

        $archivo = $archivo['datos']['PDF'];
        $contenido = is_resource($archivo) ? stream_get_contents($archivo) : $archivo;

        header("Content-Type: application/pdf");
        header('Content-Disposition: inline; filename="' . $_GET['documento'] . '.pdf"');
        header('Content-Transfer-Encoding: binary');
        header("Content-Length: " . strlen($contenido));
        echo $contenido;

        ob_clean();
        flush();
    }
}
