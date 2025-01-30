<?php

namespace App\controllers;

defined("APPPATH") or die("Access denied");

use \Core\View;
use \Core\Controller;
use \App\models\Operaciones as OperacionesDao;

class Operaciones extends Controller
{
    private $_contenedor;

    function __construct()
    {
        parent::__construct();
        $this->_contenedor = new Contenedor;
        View::set('header', $this->_contenedor->header());
        View::set('footer', $this->_contenedor->footer());
    }
    public function getUsuario()
    {
        return $this->__usuario;
    }

    public function ReportePLDPagos()
    {
        $extraFooter = <<<HTML
        <script>
            function getParameterByName(name) {
                name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]")
                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                    results = regex.exec(location.search)
                return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "))
            }

            $(document).ready(function () {
                $("#muestra-cupones").tablesorter()
                var oTable = $("#muestra-cupones").DataTable({
                    lengthMenu: [
                        [13, 50, -1],
                        [132, 50, "Todos"]
                    ],
                    columnDefs: [
                        {
                            orderable: false,
                            targets: 0
                        }
                    ],
                    order: false
                })
                // Remove accented character from search input as well
                $("#muestra-cupones input[type=search]").keyup(function () {
                    var table = $("#example").DataTable()
                    table.search(jQuery.fn.DataTable.ext.type.search.html(this.value)).draw()
                })
                var checkAll = 0

                fecha1 = getParameterByName("Inicial")
                fecha2 = getParameterByName("Final")

                $("#export_excel_consulta").click(function () {
                    $("#all").attr(
                        "action",
                        "/Operaciones/generarExcelPagos/?Inicial=" + fecha1 + "&Final=" + fecha2
                    )
                    $("#all").attr("target", "_blank")
                    $("#all").submit()
                })
            })

            function Validar() {
                fecha1 = moment((document.getElementById("Inicial").innerHTML = inputValue))
                fecha2 = moment((document.getElementById("Final").innerHTML = inputValue))

                dias = fecha2.diff(fecha1, "days")
                alert(dias)

                if (dias == 1) {
                    alert("si es")
                    return false
                }
                return false
            }

            Inicial.max = new Date().toISOString().split("T")[0]
            Final.max = new Date().toISOString().split("T")[0]

            function InfoAdmin() {
                swal("Info", "Este registro fue capturado por una administradora en caja", "info")
            }

            function InfoPhone() {
                swal(
                    "Info",
                    "Este registro fue capturado por un ejecutivo en campo y procesado por una administradora",
                    "info"
                )
            }
        </script>
        HTML;

        $Inicial = $_GET['Inicial'];
        $Final = $_GET['Final'];
        $vista = "";
        $tabla = "";

        if ($Inicial != '' || $Final != '') {
            $Consulta = OperacionesDao::ConsultarPagos($Inicial, $Final);

            foreach ($Consulta as $key => $value) {
                $tabla .= <<<HTML
                <tr style="padding: 0px !important;">
                    <td style="padding: 0px !important;">{$value['LOCALIDAD']}</td>
                    <td style="padding: 0px !important;">{$value['SUCURSAL']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_OPERACION']}</td>
                    <td style="padding: 0px !important;">{$value['ID_CLIENTE']}</td>
                    <td style="padding: 0px !important;">{$value['NUM_CUENTA']}</td>
                    <td style="padding: 0px !important;">{$value['INSTRUMENTO_MONETARIO']}</td>
                    <td style="padding: 0px !important;">{$value['MONEDA']}</td>
                    <td style="padding: 0px !important;">$ {$value['MONTO']}</td>
                    <td style="padding: 0px !important;">{$value['FECHA_OPERACION']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_RECEPTOR']}</td>
                    <td style="padding: 0px !important;">{$value['CLAVE_RECEPTOR']}</td>
                    <td style="padding: 0px !important;">{$value['NUM_CAJA']}</td>
                    <td style="padding: 0px !important;">{$value['ID_CAJERO']}</td>
                    <td style="padding: 0px !important;">{$value['FECHA_HORA']}</td>
                    <td style="padding: 0px !important;">{$value['NOTARJETA_CTA']}</td>
                    <td style="padding: 0px !important;">{$value['TIPOTARJETA']}</td>
                    <td style="padding: 0px !important;">{$value['COD_AUTORIZACION']}</td>
                    <td style="padding: 0px !important;">{$value['ATRASO']}</td>
                    <td style="padding: 0px !important;">{$value['OFICINA_CLIENTE']}</td>
                </tr>
                HTML;
            }

            if ($Consulta[0] == '') {
                View::set('fechaActual', date('Y-m-d'));
                $vista = "pagos_cobrados_consulta_cultiva_busqueda_message";
            } else {
                View::set('tabla', $tabla);
                View::set('Inicial', $Inicial);
                View::set('Final', $Final);
                $vista = "pagos_cobrados_consulta_cultiva_busqueda";
            }
        } else {
            View::set('fechaActual', date('Y-m-d'));
            $vista = "pagos_cobrados_consulta_cultiva_all";
        }

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Consulta de Pagos Cultiva")));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render($vista);
    }

    public function ReportePLDPagosNacimiento()
    {
        $extraFooter = <<<HTML
        <script>
            {$this->descargaExcel}

            function getParameterByName(name) {
                name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]")
                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                    results = regex.exec(location.search)
                return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "))
            }

            $(document).ready(function () {
                $("#muestra-cupones").tablesorter()
                var oTable = $("#muestra-cupones").DataTable({
                    lengthMenu: [
                        [13, 50, -1],
                        [132, 50, "Todos"]
                    ],
                    columnDefs: [
                        {
                            orderable: false,
                            targets: 0
                        }
                    ],
                    order: false
                })
                // Remove accented character from search input as well
                $("#muestra-cupones input[type=search]").keyup(function () {
                    var table = $("#example").DataTable()
                    table.search(jQuery.fn.DataTable.ext.type.search.html(this.value)).draw()
                })
                var checkAll = 0

                fecha1 = getParameterByName("Inicial")
                fecha2 = getParameterByName("Final")

                $("#export_excel_consulta").click(function () {
                    descargaExcel("/Operaciones/generarExcelPagosF/?Inicial=" + fecha1 + "&Final=" + fecha2)
                })
            })

            function Validar() {
                fecha1 = moment((document.getElementById("Inicial").innerHTML = inputValue))
                fecha2 = moment((document.getElementById("Final").innerHTML = inputValue))

                dias = fecha2.diff(fecha1, "days")
                alert(dias)

                if (dias == 1) {
                    alert("si es")
                    return false
                }
                return false
            }

            Inicial.max = new Date().toISOString().split("T")[0]
            Final.max = new Date().toISOString().split("T")[0]

            function InfoAdmin() {
                swal("Info", "Este registro fue capturado por una administradora en caja", "info")
            }

            function InfoPhone() {
                swal(
                    "Info",
                    "Este registro fue capturado por un ejecutivo en campo y procesado por una administradora",
                    "info"
                )
            }
        </script>
        HTML;

        $Inicial = $_GET['Inicial'];
        $Final = $_GET['Final'];
        $vista = "";
        $tabla = "";

        if ($Inicial != '' || $Final != '') {
            $Consulta = OperacionesDao::ConsultarPagosNacimiento($Inicial, $Final);

            foreach ($Consulta as $key => $value) {
                $tabla .= <<<HTML
                <tr style="padding: 0px !important;">
                    <td style="padding: 0px !important;">{$value['LOCALIDAD']}</td>
                    <td style="padding: 0px !important;">{$value['SUCURSAL']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_OPERACION']}</td>
                    <td style="padding: 0px !important;">{$value['ID_CLIENTE']}</td>
                    <td style="padding: 0px !important;">{$value['NUM_CUENTA']}</td>
                    <td style="padding: 0px !important;">{$value['INSTRUMENTO_MONETARIO']}</td>
                    <td style="padding: 0px !important;">{$value['MONEDA']}</td>
                    <td style="padding: 0px !important;">$ {$value['MONTO']}</td>
                    <td style="padding: 0px !important;">{$value['FECHA_OPERACION']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_RECEPTOR']}</td>
                    <td style="padding: 0px !important;">{$value['CLAVE_RECEPTOR']}</td>
                    <td style="padding: 0px !important;">{$value['NUM_CAJA']}</td>
                    <td style="padding: 0px !important;">{$value['ID_CAJERO']}</td>
                    <td style="padding: 0px !important;">{$value['FECHA_HORA']}</td>
                    <td style="padding: 0px !important;">{$value['NOTARJETA_CTA']}</td>
                    <td style="padding: 0px !important;">{$value['TIPOTARJETA']}</td>
                    <td style="padding: 0px !important;">{$value['COD_AUTORIZACION']}</td>
                    <td style="padding: 0px !important;">{$value['ATRASO']}</td>
                    <td style="padding: 0px !important;">{$value['OFICINA_CLIENTE']}</td>
                    <td style="padding: 0px !important;">{$value['FEC_NAC']}</td>
                    <td style="padding: 0px !important;">{$value['EDAD']}</td>
                    <td style="padding: 0px !important;">{$value['CICLO']}</td>
                </tr>
                HTML;
            }

            if ($Consulta[0] == '') {
                View::set('fechaActual', date('Y-m-d'));
                $vista = "pagos_cobrados_consulta_cultiva_busqueda_message_F";
            } else {
                View::set('tabla', $tabla);
                View::set('Inicial', $Inicial);
                View::set('Final', $Final);
                $vista = "pagos_cobrados_consulta_cultiva_busqueda_F";
            }
        } else {
            View::set('fechaActual', date('Y-m-d'));
            $vista = "pagos_cobrados_consulta_cultiva_all_F";
        }

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Consulta de Desembolsos Cultiva con Fecha Nac")));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render($vista);
    }

    public function ReportePLDDesembolsos()
    {
        $extraFooter = <<<HTML
            <script>
                function getParameterByName(name) {
                    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]")
                    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                        results = regex.exec(location.search)
                    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "))
                }

                $(document).ready(function () {
                    $("#muestra-cupones").tablesorter()
                    var oTable = $("#muestra-cupones").DataTable({
                        lengthMenu: [
                            [13, 50, -1],
                            [132, 50, "Todos"]
                        ],
                        columnDefs: [
                            {
                                orderable: false,
                                targets: 0
                            }
                        ],
                        order: false
                    })
                    // Remove accented character from search input as well
                    $("#muestra-cupones input[type=search]").keyup(function () {
                        var table = $("#example").DataTable()
                        table.search(jQuery.fn.DataTable.ext.type.search.html(this.value)).draw()
                    })
                    var checkAll = 0

                    fecha1 = getParameterByName("Inicial")
                    fecha2 = getParameterByName("Final")

                    $("#export_excel_consulta").click(function () {
                        $("#all").attr(
                            "action",
                            "/Operaciones/generarExcel/?Inicial=" + fecha1 + "&Final=" + fecha2
                        )
                        $("#all").attr("target", "_blank")
                        $("#all").submit()
                    })
                })

                function Validar() {
                    fecha1 = moment((document.getElementById("Inicial").innerHTML = inputValue))
                    fecha2 = moment((document.getElementById("Final").innerHTML = inputValue))

                    dias = fecha2.diff(fecha1, "days")
                    alert(dias)

                    if (dias == 1) {
                        alert("si es")
                        return false
                    }
                    return false
                }

                Inicial.max = new Date().toISOString().split("T")[0]
                Final.max = new Date().toISOString().split("T")[0]

                function InfoAdmin() {
                    swal("Info", "Este registro fue capturado por una administradora en caja", "info")
                }

                function InfoPhone() {
                    swal(
                        "Info",
                        "Este registro fue capturado por un ejecutivo en campo y procesado por una administradora",
                        "info"
                    )
                }
            </script>
        HTML;

        $Inicial = $_GET['Inicial'];
        $Final = $_GET['Final'];
        $vista = '';
        $tabla = '';

        if ($Inicial != '' || $Final != '') {
            $Consulta = OperacionesDao::ConsultarDesembolsos($Inicial, $Final);

            foreach ($Consulta as $key => $value) {
                $tabla .= <<<HTML
                <tr style="padding: 0px !important;">
                    <td style="padding: 0px !important;">{$value['LOCALIDAD']}</td>
                    <td style="padding: 0px !important;">{$value['SUCURSAL']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_OPERACION']}</td>
                    <td style="padding: 0px !important;">{$value['ID_CLIENTE']}</td>
                    <td style="padding: 0px !important;">{$value['NUM_CUENTA']}</td>
                    <td style="padding: 0px !important;">{$value['INSTRUMENTO_MONETARIO']}</td>
                    <td style="padding: 0px !important;">{$value['MONEDA']}</td>
                    <td style="padding: 0px !important;">$ {$value['MONTO']}</td>
                    <td style="padding: 0px !important;">{$value['FECHA_OPERACION']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_RECEPTOR']}</td>
                    <td style="padding: 0px !important;">{$value['CLAVE_RECEPTOR']}</td>
                    <td style="padding: 0px !important;">{$value['NUM_CAJA']}</td>
                    <td style="padding: 0px !important;">{$value['ID_CAJERO']}</td>
                    <td style="padding: 0px !important;">{$value['FECHA_HORA']}</td>
                    <td style="padding: 0px !important;">{$value['NOTARJETA_CTA']}</td>
                    <td style="padding: 0px !important;">{$value['TIPOTARJETA']}</td>
                    <td style="padding: 0px !important;">{$value['COD_AUTORIZACION']}</td>
                    <td style="padding: 0px !important;">{$value['ATRASO']}</td>
                    <td style="padding: 0px !important;">{$value['OFICINA_CLIENTE']}</td>
                </tr>
                HTML;
            }

            if ($Consulta[0] == '') {
                View::set('fechaActual', date('Y-m-d'));
                $vista = "pagos_consulta_cultiva_busqueda_message";
            } else {
                View::set('tabla', $tabla);
                View::set('Inicial', $Inicial);
                View::set('Final', $Final);
                $vista = "pagos_consulta_cultiva_busqueda";
            }
        } else {
            View::set('fechaActual', date('Y-m-d'));
            $vista = "pagos_consulta_cultiva_all";
        }

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Consulta de Desembolsos Cultiva con Fecha Nac")));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render($vista);
    }

    public function IdentificacionClientes()
    {
        $extraFooter = <<<HTML
        <script>
            function getParameterByName(name) {
                name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]")
                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                    results = regex.exec(location.search)
                return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "))
            }

            $(document).ready(function () {
                $("#muestra-cupones").tablesorter()
                var oTable = $("#muestra-cupones").DataTable({
                    lengthMenu: [
                        [13, 50, -1],
                        [132, 50, "Todos"]
                    ],
                    columnDefs: [
                        {
                            orderable: false,
                            targets: 0
                        }
                    ],
                    order: false
                })
                // Remove accented character from search input as well
                $("#muestra-cupones input[type=search]").keyup(function () {
                    var table = $("#example").DataTable()
                    table.search(jQuery.fn.DataTable.ext.type.search.html(this.value)).draw()
                })
                var checkAll = 0

                fecha1 = getParameterByName("Inicial")
                fecha2 = getParameterByName("Final")

                $("#export_excel_consulta").click(function () {
                    $("#all").attr(
                        "action",
                        "/Operaciones/generarExcelPagosIC/?Inicial=" + fecha1 + "&Final=" + fecha2
                    )
                    $("#all").attr("target", "_blank")
                    $("#all").submit()
                })
            })

            function Validar() {
                fecha1 = moment((document.getElementById("Inicial").innerHTML = inputValue))
                fecha2 = moment((document.getElementById("Final").innerHTML = inputValue))

                dias = fecha2.diff(fecha1, "days")
                alert(dias)

                if (dias == 1) {
                    alert("si es")
                    return false
                }
                return false
            }

            Inicial.max = new Date().toISOString().split("T")[0]
            Final.max = new Date().toISOString().split("T")[0]
        </script>
        HTML;

        $fechaActual = date('Y-m-d');
        $Inicial = $_GET['Inicial'];
        $Final = $_GET['Final'];
        $vista = "";
        $tabla = "";

        if ($Inicial != '' || $Final != '') {
            $Consulta = OperacionesDao::ConsultarClientes($Inicial, $Final);

            foreach ($Consulta as $key => $value) {
                $tabla .= <<<HTML
                <tr style="padding: 0px !important;">
                    <td style="padding: 0px !important;">{$value['CDGCL']}</td>
                    <td style="padding: 0px !important;">{$value['GRUPO']}</td>
                    <td style="padding: 0px !important;">{$value['ORIGEN']}</td>
                    <td style="padding: 0px !important;">{$value['NOMBRE']}</td>
                    <td style="padding: 0px !important;">{$value['ADICIONAL']}</td>
                    <td style="padding: 0px !important;">{$value['A_PATERNO']}</td>
                    <td style="padding: 0px !important;">{$value['A_MATERNO']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_PERSONA']}</td>
                    <td style="padding: 0px !important;">{$value['RFC']}</td>
                    <td style="padding: 0px !important;">{$value['CURP']}</td>
                    <td style="padding: 0px !important;">{$value['RAZON_SOCIAL']}</td>
                    <td style="padding: 0px !important;">{$value['FECHA_NAC']}</td>
                    <td style="padding: 0px !important;">{$value['NACIONALIDAD']}</td>
                    <td style="padding: 0px !important;">{$value['DOMICILIO']}</td>
                    <td style="padding: 0px !important;">{$value['COLONIA']}</td>
                    <td style="padding: 0px !important;">{$value['CIUDAD']}</td>
                    <td style="padding: 0px !important;">{$value['PAIS']}</td>
                    <td style="padding: 0px !important;">{$value['SUC_ID_ESTADO']}</td>
                    <td style="padding: 0px !important;">{$value['TELEFONO']}</td>
                    <td style="padding: 0px !important;">{$value['ID_ACTIVIDAD_ECONO']}</td>
                    <td style="padding: 0px !important;">{$value['CALIFICACION']}</td>
                    <td style="padding: 0px !important;">{$value['ALTA']}</td>
                    <td style="padding: 0px !important;">{$value['ID_SUCURSAL_SISTEMA']}</td>
                    <td style="padding: 0px !important;">{$value['GENERO']}</td>
                    <td style="padding: 0px !important;">{$value['CORREO_ELECTRONICO']}</td>
                    <td style="padding: 0px !important;">{$value['FIRMA_ELECT']}</td>
                    <td style="padding: 0px !important;">{$value['PROFESION']}</td>
                    <td style="padding: 0px !important;">{$value['OCUPACION']}</td>
                    <td style="padding: 0px !important;">{$value['PAIS_NAC']}</td>
                    <td style="padding: 0px !important;">{$value['EDO_NAC']}</td>
                    <td style="padding: 0px !important;">{$value['LUGAR_NAC']}</td>
                    <td style="padding: 0px !important;">{$value['NUMERO_DOCUMENTO']}</td>
                    <td style="padding: 0px !important;">{$value['CONOCIMIENTO']}</td>
                    <td style="padding: 0px !important;">{$value['INMIGRACION']}</td>
                    <td style="padding: 0px !important;">{$value['CUENTA_ORIGINAL']}</td>
                    <td style="padding: 0px !important;">{$value['SITUACION_CREDITO']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_DOCUMENTO']}</td>
                    <td style="padding: 0px !important;">{$value['INDICADOR_EMPLEO']}</td>
                    <td style="padding: 0px !important;">{$value['EMPRESAS']}</td>
                    <td style="padding: 0px !important;">{$value['INDICADOR_GOBIERNO']}</td>
                    <td style="padding: 0px !important;">{$value['PUESTO']}</td>
                    <td style="padding: 0px !important;">{$value['FECHA_INICIO']}</td>
                    <td style="padding: 0px !important;">{$value['FEH_FIN']}</td>
                    <td style="padding: 0px !important;">{$value['CP']}</td>
                </tr>
                HTML;
            }
            if ($Consulta[0] == '') {
                View::set('fechaActual', $fechaActual);
                $vista = "clientes_consulta_cultiva_busqueda_message";
            } else {
                View::set('tabla', $tabla);
                View::set('Inicial', $Inicial);
                View::set('Final', $Final);
                $vista = "clientes_consulta_cultiva_busqueda";
            }
        } else {
            View::set('fechaActual', $fechaActual);
            $vista = "clientes_consulta_cultiva_all";
        }

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Identificación de clientes Cultiva")));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render($vista);
    }

    public function CuentasRelacionadas()
    {
        $extraFooter = <<<HTML
        <script>
            function getParameterByName(name) {
                name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]")
                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                    results = regex.exec(location.search)
                return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "))
            }

            $(document).ready(function () {
                $("#muestra-cupones").tablesorter()
                var oTable = $("#muestra-cupones").DataTable({
                    lengthMenu: [
                        [13, 50, -1],
                        [132, 50, "Todos"]
                    ],
                    columnDefs: [
                        {
                            orderable: false,
                            targets: 0
                        }
                    ],
                    order: false
                })
                // Remove accented character from search input as well
                $("#muestra-cupones input[type=search]").keyup(function () {
                    var table = $("#example").DataTable()
                    table.search(jQuery.fn.DataTable.ext.type.search.html(this.value)).draw()
                })
                var checkAll = 0

                fecha1 = getParameterByName("Inicial")
                fecha2 = getParameterByName("Final")

                $("#export_excel_consulta").click(function () {
                    $("#all").attr(
                        "action",
                        "/Operaciones/generarExcelClientesCR/?Inicial=" + fecha1 + "&Final=" + fecha2
                    )
                    $("#all").attr("target", "_blank")
                    $("#all").submit()
                })
            })

            function Validar() {
                fecha1 = moment((document.getElementById("Inicial").innerHTML = inputValue))
                fecha2 = moment((document.getElementById("Final").innerHTML = inputValue))

                dias = fecha2.diff(fecha1, "days")
                alert(dias)

                if (dias == 1) {
                    alert("si es")
                    return false
                }
                return false
            }

            Inicial.max = new Date().toISOString().split("T")[0]
            Final.max = new Date().toISOString().split("T")[0]
        </script>
        HTML;

        $Inicial = $_GET['Inicial'];
        $Final = $_GET['Final'];
        $vista = "";
        $tabla = "";

        if ($Inicial != '' || $Final != '') {
            $Consulta = OperacionesDao::CuentasRelacionadas($Inicial, $Final);
            foreach ($Consulta as $key => $value) {
                $tabla .= <<<HTML
                <tr style="padding: 0px !important;">
                    <td style="padding: 0px !important;">{$value['CLIENTE']}</td>
                    <td style="padding: 0px !important;">{$value['GRUPO']}</td>
                    <td style="padding: 0px !important;">{$value['CUENTA_RELACION']}</td>
                    <td style="padding: 0px !important;">{$value['NOMBRE']}</td>
                    <td style="padding: 0px !important;">{$value['ADICIONAL']}</td>
                    <td style="padding: 0px !important;">{$value['A_PATERNO']}</td>
                    <td style="padding: 0px !important;">{$value['A_MATERNO']}</td>
                    <td style="padding: 0px !important;">{$value['DESCRIPCION_OPERACION']}</td>
                    <td style="padding: 0px !important;">{$value['IDENTIFICA_CUENTA']}</td>
                    <td style="padding: 0px !important;">{$value['CONSERVA']}</td>
                    <td style="padding: 0px !important;">{$value['OFICINA_CLIENTE']}</td>
                    <td style="padding: 0px !important;">{$value['FECHA_INICIO_OPERACION']}</td>
                </tr>
                HTML;
            }
            if ($Consulta[0] == '') {
                View::set('fechaActual', date('Y-m-d'));
                $vista = "cuentas_relacionadas_cultiva_busqueda_message";
            } else {
                View::set('tabla', $tabla);
                View::set('Inicial', $Inicial);
                View::set('Final', $Final);
                $vista = "cuentas_relacionadas_cultiva_busqueda";
            }
        } else {
            View::set('fechaActual', date('Y-m-d'));
            $vista = "cuentas_relacionadas_consulta_all";
        }

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Cuentas Relacionadas de clientes Cultiva")));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render($vista);
    }

    public function PerfilTransaccional()
    {
        $extraFooter = <<<HTML
        <script>
            function getParameterByName(name) {
                name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]")
                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                    results = regex.exec(location.search)
                return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "))
            }

            $(document).ready(function () {
                $("#muestra-cupones").tablesorter()
                var oTable = $("#muestra-cupones").DataTable({
                    lengthMenu: [
                        [13, 50, -1],
                        [132, 50, "Todos"]
                    ],
                    columnDefs: [
                        {
                            orderable: false,
                            targets: 0
                        }
                    ],
                    order: false
                })
                // Remove accented character from search input as well
                $("#muestra-cupones input[type=search]").keyup(function () {
                    var table = $("#example").DataTable()
                    table.search(jQuery.fn.DataTable.ext.type.search.html(this.value)).draw()
                })
                var checkAll = 0

                fecha1 = getParameterByName("Inicial")
                fecha2 = getParameterByName("Final")

                $("#export_excel_consulta").click(function () {
                    $("#all").attr(
                        "action",
                        "/Operaciones/generarExcelClientesPT/?Inicial=" + fecha1 + "&Final=" + fecha2
                    )
                    $("#all").attr("target", "_blank")
                    $("#all").submit()
                })
            })

            function Validar() {
                fecha1 = moment((document.getElementById("Inicial").innerHTML = inputValue))
                fecha2 = moment((document.getElementById("Final").innerHTML = inputValue))

                dias = fecha2.diff(fecha1, "days")
                alert(dias)

                if (dias == 1) {
                    alert("si es")
                    return false
                }
                return false
            }

            Inicial.max = new Date().toISOString().split("T")[0]
            Final.max = new Date().toISOString().split("T")[0]
        </script>
        HTML;

        $Inicial = $_GET['Inicial'];
        $Final = $_GET['Final'];
        $vista = "";
        $tabla = "";

        if ($Inicial != '' || $Final != '') {
            $Consulta = OperacionesDao::ConsultarPerfilTransaccional($Inicial, $Final);
            foreach ($Consulta as $key => $value) {
                $tabla .= <<<HTML
                <tr style="padding: 0px !important;">
                    <td style="padding: 0px !important;">{$value['CDGCL']}</td>
                    <td style="padding: 0px !important;">{$value['GRUPO']}</td>
                    <td style="padding: 0px !important;">{$value['NOMBRE']}</td>
                    <td style="padding: 0px !important;">{$value['INSTRUMENTO']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_MONEDA']}</td>
                    <td style="padding: 0px !important;">{$value['T_CAMBIO']}</td>
                    <td style="padding: 0px !important;">{$value['MONTO_PRESTAMO']}</td>
                    <td style="padding: 0px !important;">{$value['PLAZO']}</td>
                    <td style="padding: 0px !important;">{$value['FRECUENCIA']}</td>
                    <td style="padding: 0px !important;">{$value['TOTAL_PAGOS']}</td>
                    <td style="padding: 0px !important;">{$value['MONTO_FIN_PAGO']}</td>
                    <td style="padding: 0px !important;">{$value['ADELANTAR_PAGO']}</td>
                    <td style="padding: 0px !important;">{$value['NUMERO_APORTACIONES']}</td>
                    <td style="padding: 0px !important;">{$value['MONTO_APORTACIONES']}</td>
                    <td style="padding: 0px !important;">{$value['CUOTA_PAGO']}</td>
                    <td style="padding: 0px !important;">{$value['SALDO']}</td>
                    <td style="padding: 0px !important;">{$value['ID_SUCURSAL_SISTEMA']}</td>
                    <td style="padding: 0px !important;">{$value['ORIGEN_RECURSO']}</td>
                    <td style="padding: 0px !important;">{$value['DESTINO_RECURSOS']}</td>
                    <td style="padding: 0px !important;">{$value['FECHA_INICIO_CREDITO']}</td>
                    <td style="padding: 0px !important;">{$value['FECHA_FIN']}</td>
                    <td style="padding: 0px !important;">{$value['DESTINO']}</td>
                    <td style="padding: 0px !important;">{$value['ORIGEN']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_OPERACION']}</td>
                    <td style="padding: 0px !important;">{$value['INST_MONETARIO']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_CREDITO']}</td>
                    <td style="padding: 0px !important;">{$value['PRODUCTO']}</td>
                    <td style="padding: 0px !important;">{$value['PAIS_ORIGEN']}</td>
                    <td style="padding: 0px !important;">{$value['PAIS_DESTINO']}</td>
                    <td style="padding: 0px !important;">{$value['ALTA_CONTRATO']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_CONTRATO']}</td>
                    <td style="padding: 0px !important;">{$value['TIPO_DOC']}</td>
                    <td style="padding: 0px !important;">{$value['LATLON']}</td>
                    <td style="padding: 0px !important;">{$value['LOCALIZACION']}</td>
                    <td style="padding: 0px !important;">{$value['CP']}</td>
                </tr>
                HTML;
            }
            if ($Consulta[0] == '') {
                View::set('fechaActual', date('Y-m-d'));
                $vista = "perfil_transaccional_cultiva_busqueda_message";
            } else {
                View::set('tabla', $tabla);
                View::set('Inicial', $Inicial);
                View::set('Final', $Final);
                $vista = "perfil_transaccional_cultiva_busqueda";
            }
        } else {
            View::set('fechaActual', date('Y-m-d'));
            $vista = "perfil_transaccional_consulta_cultiva_all";
        }

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Perfil transaccional Cultiva")));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render($vista);
    }

    public function generarExcel()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();
        $soloCentrado = [
            'estilo' => $estilos['centrado']
        ];

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('LOCALIDAD', 'LOCALIDAD'),
            \PHPSpreadsheet::ColumnaExcel('SUCURSAL', 'SUCURSAL', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_OPERACION', 'TIPO DE OPERACIÓN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ID_CLIENTE', 'ID CLIENTE', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('NUM_CUENTA', 'NUMERO DE CTA, CONTRATO, OPERACIÓN, PÓLIZA O NUMERO DE SEGURIDAD SOCIAL', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('INSTRUMENTO_MONETARIO', 'INSTRUMENTO MONETARIO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('MONEDA', 'MONEDA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('MONTO', 'MONTO', ['estilo' => $estilos['moneda'], 'total' => true]),
            \PHPSpreadsheet::ColumnaExcel('FECHA_OPERACION', 'FECHA DE LA OPERACIÓN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_RECEPTOR', 'TIPO RECEPTOR', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CLAVE_RECEPTOR', 'CLAVE DE RECEPTOR'),
            \PHPSpreadsheet::ColumnaExcel('NUM_CAJA', 'CAJA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ID_CAJERO', 'CAJERO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('FECHA_HORA', 'FECHA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('NOTARJETA_CTA', 'NO. TARJETA O CTA DEPOSITO'),
            \PHPSpreadsheet::ColumnaExcel('TIPOTARJETA', 'TIPO DE TARJETA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('COD_AUTORIZACION', 'COD AUTORIZACION', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ATRASO', 'ATRASO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('OFICINA_CLIENTE', 'OFICINA CLIENTE', $soloCentrado)
        ];

        $filas = OperacionesDao::ConsultarDesembolsos($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::DescargaExcel('Consulta de Desembolsos Cultiva', 'Reporte', 'Consulta de Desembolsos', $columnas, $filas);
    }

    public function generarExcelPagos()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();
        $soloCentrado = [
            'estilo' => $estilos['centrado']
        ];

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('LOCALIDAD', 'LOCALIDAD'),
            \PHPSpreadsheet::ColumnaExcel('SUCURSAL', 'SUCURSAL', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_OPERACION', 'TIPO DE OPERACIÓN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ID_CLIENTE', 'ID CLIENTE', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('NUM_CUENTA', 'NUMERO DE CTA, CONTRATO, OPERACIÓN, PÓLIZA O NUMERO DE SEGURIDAD SOCIAL', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('INSTRUMENTO_MONETARIO', 'INSTRUMENTO MONETARIO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('MONEDA', 'MONEDA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('MONTO', 'MONTO', ['estilo' => $estilos['moneda'], 'total' => true]),
            \PHPSpreadsheet::ColumnaExcel('FECHA_OPERACION', 'FECHA DE LA OPERACIÓN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_RECEPTOR', 'TIPO RECEPTOR', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CLAVE_RECEPTOR', 'CLAVE DE RECEPTOR'),
            \PHPSpreadsheet::ColumnaExcel('NUM_CAJA', 'CAJA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ID_CAJERO', 'CAJERO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('FECHA_HORA', 'FECHA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('NOTARJETA_CTA', 'NO. TARJETA O CTA DEPOSITO'),
            \PHPSpreadsheet::ColumnaExcel('TIPOTARJETA', 'TIPO DE TARJETA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('COD_AUTORIZACION', 'COD AUTORIZACION', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ATRASO', 'ATRASO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('OFICINA_CLIENTE', 'OFICINA CLIENTE', $soloCentrado)
        ];

        $filas = OperacionesDao::ConsultarPagos($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::DescargaExcel('Consulta de Pagos Cultiva', 'Reporte', 'Consulta de Pagos', $columnas, $filas);
    }

    public function generarExcelPagosF()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();
        $soloCentrado = [
            'estilo' => $estilos['centrado']
        ];

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('LOCALIDAD', 'LOCALIDAD'),
            \PHPSpreadsheet::ColumnaExcel('SUCURSAL', 'SUCURSAL', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_OPERACION', 'TIPO DE OPERACIÓN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ID_CLIENTE', 'ID CLIENTE', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('NUM_CUENTA', 'NUMERO DE CTA, CONTRATO, OPERACIÓN, PÓLIZA O NUMERO DE SEGURIDAD SOCIAL', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('INSTRUMENTO_MONETARIO', 'INSTRUMENTO MONETARIO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('MONEDA', 'MONEDA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('MONTO', 'MONTO', ['estilo' => $estilos['moneda'], 'total' => true]),
            \PHPSpreadsheet::ColumnaExcel('FECHA_OPERACION', 'FECHA DE LA OPERACIÓN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_RECEPTOR', 'TIPO RECEPTOR', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CLAVE_RECEPTOR', 'CLAVE DE RECEPTOR'),
            \PHPSpreadsheet::ColumnaExcel('NUM_CAJA', 'CAJA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ID_CAJERO', 'CAJERO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('FECHA_HORA', 'FECHA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('NOTARJETA_CTA', 'NO. TARJETA O CTA DEPOSITO'),
            \PHPSpreadsheet::ColumnaExcel('TIPOTARJETA', 'TIPO DE TARJETA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('COD_AUTORIZACION', 'COD AUTORIZACION', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ATRASO', 'ATRASO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('OFICINA_CLIENTE', 'OFICINA CLIENTE', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('FEC_NAC', 'FECHA NACIMIENTO', ['estilo' => $estilos['fecha']]),
            \PHPSpreadsheet::ColumnaExcel('EDAD', 'EDAD', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CICLO', 'CICLO', $soloCentrado)
        ];

        $filas = OperacionesDao::ConsultarPagosNacimiento($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::DescargaExcel('Consulta de Pagos X Fecha Nacimiento Cultiva', 'Reporte', 'Consulta de Pagos por Fecha de Nacimiento', $columnas, $filas);
    }

    public function generarExcelPagosIC()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();
        $soloCentrado = [
            'estilo' => $estilos['centrado']
        ];

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('CDGCL', 'ID CLIENTE', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('GRUPO', 'CUENTA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ORIGEN', 'Origen', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('NOMBRE', 'NOMBRE'),
            \PHPSpreadsheet::ColumnaExcel('ADICIONAL', 'ADICIONAL'),
            \PHPSpreadsheet::ColumnaExcel('A_PATERNO', 'APELLIDO PATERNO'),
            \PHPSpreadsheet::ColumnaExcel('A_MATERNO', 'APELLIDO MATERNO'),
            \PHPSpreadsheet::ColumnaExcel('TIPO_PERSONA', 'TIPO DE PERSONA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('RFC', 'RFC'),
            \PHPSpreadsheet::ColumnaExcel('CURP', 'CURP'),
            \PHPSpreadsheet::ColumnaExcel('RAZON_SOCIAL', 'RAZÓN SOCIAL O DENOMINACIÓN'),
            \PHPSpreadsheet::ColumnaExcel('FECHA_NAC', 'FECHA DE NACIMIENTO O CONSTITUCIÓN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('NACIONALIDAD', 'NACIONALIDAD', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('DOMICILIO', 'DOMICILIO (calle, número exterior e interior (si aplica) y código postal)'),
            \PHPSpreadsheet::ColumnaExcel('COLONIA', 'COLONIA'),
            \PHPSpreadsheet::ColumnaExcel('CIUDAD', 'CIUDAD O POBLACIÓN'),
            \PHPSpreadsheet::ColumnaExcel('PAIS', 'PAIS', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('SUC_ID_ESTADO', 'ESTADO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TELEFONO', 'TELEFONO OFICINA/PARTICULAR', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ID_ACTIVIDAD_ECONO', 'ACTIVIDAD ECONOMICA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CALIFICACION', 'CALIFICACIÓN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ALTA', 'FECHA ALTA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ID_SUCURSAL_SISTEMA', 'SUCURSAL', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('GENERO', 'GENERO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CORREO_ELECTRONICO', 'CORREO ELECTRONICO'),
            \PHPSpreadsheet::ColumnaExcel('FIRMA_ELECT', 'FIRMA ELEC.'),
            \PHPSpreadsheet::ColumnaExcel('PROFESION', 'PROFESION', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('OCUPACION', 'OCUPACION', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('PAIS_NAC', 'PAIS NAC.', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('EDO_NAC', 'EDO. NAC.', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('LUGAR_NAC', 'LUGAR NAC.'),
            \PHPSpreadsheet::ColumnaExcel('NUMERO_DOCUMENTO', 'NUMERO DE DOCUMENTO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CONOCIMIENTO', 'CONOCIMIENTO CLIENTE', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('INMIGRACION', 'REGISTRO NACIONAL DE INMIGRACION', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CUENTA_ORIGINAL', 'CUENTA ORIGINAL', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('SITUACION_CREDITO', 'SITUACIÓN CLIENTE', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_DOCUMENTO', 'TIPO DOCUMENTO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('INDICADOR_EMPLEO', 'INDICADOR EMPLEO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('EMPRESAS', 'EMPRESA LABORA(Ó)'),
            \PHPSpreadsheet::ColumnaExcel('INDICADOR_GOBIERNO', 'INDICADOR GOBIERNO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('PUESTO', 'PUESTO'),
            \PHPSpreadsheet::ColumnaExcel('FECHA_INICIO', 'FECHA INICIO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('FEH_FIN', 'FEH FIN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CP', 'CP', $soloCentrado)
        ];

        $filas = OperacionesDao::ConsultarClientes($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::DescargaExcel('Identificación de Clientes Cultiva', 'Reporte', 'Identificación de Clientes', $columnas, $filas);
    }

    public function generarExcelClientesCR()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();
        $soloCentrado = [
            'estilo' => $estilos['centrado']
        ];

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('CLIENTE', 'CLIENTE', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('GRUPO', 'NUMERO DE CUENTA- CONTRATO-OPERACIÓN- PÓLIZA O NSS2', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CUENTA_RELACION', 'NO. CUENTA RELACIONADA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('DESCRIPCION_OPERACION', 'DESCRIPCION DE LA OPERACIÓN*', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('IDENTIFICA_CUENTA', 'IDENTIFICA CUENTA como interna', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CONSERVA', 'CONSERVA CUENTA ORIGINAL'),
            \PHPSpreadsheet::ColumnaExcel('OFICINA_CLIENTE', 'OFICINA CLIENTE', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('FECHA_INICIO_OPERACION', 'FECHA INICIO OPERACIÓN', $soloCentrado)
        ];

        $filas = OperacionesDao::CuentasRelacionadas($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::DescargaExcel('Cuentas Relacionadas Cultiva', 'Reporte', 'Cuentas Relacionadas PLD Cultiva', $columnas, $filas);
    }

    public function generarExcelClientesPT()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();
        $soloCentrado = [
            'estilo' => $estilos['centrado']
        ];

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('CDGCL', 'ID CLIENTE', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('GRUPO', 'Cuenta', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('INSTRUMENTO', 'INSTRUMENTO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_MONEDA', 'TIPO MONEDA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('T_CAMBIO', 'T/CAMBIO'),
            \PHPSpreadsheet::ColumnaExcel('MONT_PRESTAMO', 'MONTO Prest/INV.'),
            \PHPSpreadsheet::ColumnaExcel('PLAZO', 'PLAZO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('FRECUENCIA', 'FRECUENCIA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TOTAL_PAGOS', 'TOTAL PAGOS', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('MONTO_FIN_PAGO', 'Monto C/Pago'),
            \PHPSpreadsheet::ColumnaExcel('ADELANTAR_PAGO', 'AUT. ADELANTAR PAGO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('NUMERO_APORTACIONES', 'NO.APORTACIONES', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('MONTO_APORTACIONES', 'Monto APORTACIONES'),
            \PHPSpreadsheet::ColumnaExcel('ID_SUCURSAL_SISTEMA', 'SUCURSAL', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ORIGEN_RECURSO', 'ORIGEN RECURSOS'),
            \PHPSpreadsheet::ColumnaExcel('DESTINO_RECURSOS', 'DESTINO RECURSOS'),
            \PHPSpreadsheet::ColumnaExcel('FECHA_INICIO_CREDITO', 'FECHA INICIO CREDITO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('FECHA_FIN', 'FECHA FIN CREDITO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('DESTINO', 'Destino/nacionalidad', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ORIGEN', 'Origen/nacionalidad2', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_OPERACION', 'TIPO OPERACIÓN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('INST_MONETARIO', 'INSTR MONETARIOS', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_CREDITO', 'TIPO CRÉDITO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('PRODUCTO', 'CLAVE PRODUCTO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('PAIS_ORIGEN', 'PAIS ORIGEN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('PAIS_DESTINO', 'PAIS DESTINO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ALTA_CONTRATO', 'ALTA CONTRATO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_CONTRATO', 'TIPO DE CONTRATO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIP_DOC', 'TIPO DE DOCUMENTO/FOLIO'),
            \PHPSpreadsheet::ColumnaExcel('LATLON', 'LATITUD/LONGITUD'),
            \PHPSpreadsheet::ColumnaExcel('LOCALIZACION', 'LOCALIZACION'),
            \PHPSpreadsheet::ColumnaExcel('', 'PROPIETARIO REAL'),
            \PHPSpreadsheet::ColumnaExcel('', 'PROVEEDOR DE RECURSOS')
        ];

        $filas = OperacionesDao::ConsultarPerfilTransaccional($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::DescargaExcel('Perfil Transaccional Cultiva', 'Reporte', 'Perfil Transaccional Cultiva', $columnas, $filas);
    }

    public function ReporteAuditoria()
    {
        $extraFooter = <<<HTML
            <script>
                {$this->mensajes}
                {$this->configuraTabla}
                {$this->actualizaDatosTabla}
                {$this->consultaServidor}
                {$this->respuestaError}
                {$this->respuestaSuccess}
                {$this->descargaExcel}

                const idTabla = 'reporteAuditoria'

                const getParametros = (post = true) => {
                    const p = {
                        fechaI: $("#fechaI").val(),
                        fechaF: $("#fechaF").val()
                    }

                    if (post) return p
                    return Object.keys(p).map((key) => key + "=" + p[key]).join("&")
                }

                const generaReporte = () => {
                    consultaServidor('/operaciones/GetReporteAuditoria', getParametros(), (res) => {
                        if (!res.success) return respuestaError(idTabla, res.mensaje)
                        if (res.datos.length === 0) return respuestaError(idTabla, "No se encontraron registros para los parámetros solicitados.")

                        respuestaSuccess(idTabla, res.datos)
                    })
                }

                $(document).ready(() => {
                    configuraTabla(idTabla)

                    $("#buscar").click(generaReporte)
                    $("#exportar").click(() => descargaExcel("/Operaciones/ExportReporteAuditoria/?" + getParametros(false)))
                })
            </script>
        HTML;

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Reporte de auditoría")));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render('reporte_auditoria');
    }

    public function GetReporteAuditoria()
    {
        echo json_encode(OperacionesDao::ReporteAuditoria($_POST));
    }

    public function ExportReporteAuditoria()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();
        $soloCentrado = [
            'estilo' => $estilos['centrado']
        ];

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('LOCALIDAD', 'LOCALIDAD'),
            \PHPSpreadsheet::ColumnaExcel('SUCURSAL', 'SUCURSAL', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_OPERACION', 'TIPO DE OPERACIÓN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ID_CLIENTE', 'ID CLIENTE', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('NUM_CUENTA', 'NUMERO DE CTA, CONTRATO, OPERACIÓN, PÓLIZA O NUMERO DE SEGURIDAD SOCIAL', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('INSTRUMENTO_MONETARIO', 'INSTRUMENTO MONETARIO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('MONEDA', 'MONEDA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('MONTO', 'MONTO', ['estilo' => $estilos['moneda'], 'total' => true]),
            \PHPSpreadsheet::ColumnaExcel('FECHA_OPERACION', 'FECHA DE LA OPERACIÓN', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('TIPO_RECEPTOR', 'TIPO RECEPTOR', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('CLAVE_RECEPTOR', 'CLAVE DE RECEPTOR'),
            \PHPSpreadsheet::ColumnaExcel('NUM_CAJA', 'CAJA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ID_CAJERO', 'CAJERO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('FECHA_HORA', 'FECHA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('NOTARJETA_CTA', 'NO. TARJETA O CTA DEPOSITO'),
            \PHPSpreadsheet::ColumnaExcel('TIPOTARJETA', 'TIPO DE TARJETA', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('COD_AUTORIZACION', 'COD AUTORIZACION', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('ATRASO', 'ATRASO', $soloCentrado),
            \PHPSpreadsheet::ColumnaExcel('OFICINA_CLIENTE', 'OFICINA CLIENTE', $soloCentrado)
        ];

        $filas = OperacionesDao::ReporteAuditoria($_GET);
        $filas = $filas['success'] ? $filas['datos'] : [];

        \PHPSpreadsheet::DescargaExcel('Reporte de Auditoría', 'Reporte', 'Reporte de Auditoría', $columnas, $filas);
    }
}
