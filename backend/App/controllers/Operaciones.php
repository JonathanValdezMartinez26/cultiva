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
                        "/Operaciones/generarExcelPagosF/?Inicial=" + fecha1 + "&Final=" + fecha2
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
        $vista = "";
        $tabla = "";

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

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('A', 'LOCALIDAD', 'LOCALIDAD'),
            \PHPSpreadsheet::ColumnaExcel('B', 'SUCURSAL', 'SUCURSAL', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('C', 'TIPO_OPERACION', 'TIPO DE OPERACIÓN', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('D', 'ID_CLIENTE', 'ID CLIENTE', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('E', 'NUM_CUENTA', 'NUMERO DE CTA, CONTRATO, OPERACIÓN, PÓLIZA O NUMERO DE SEGURIDAD SOCIAL', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('F', 'INSTRUMENTO_MONETARIO', 'INSTRUMENTO MONETARIO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('G', 'MONEDA', 'MONEDA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('H', 'MONTO', 'MONTO', $estilos['moneda']),
            \PHPSpreadsheet::ColumnaExcel('I', 'FECHA_OPERACION', 'FECHA DE LA OPERACIÓN', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('J', 'TIPO_RECEPTOR', 'TIPO RECEPTOR', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('K', 'CLAVE_RECEPTOR', 'CLAVE DE RECEPTOR'),
            \PHPSpreadsheet::ColumnaExcel('L', 'NUM_CAJA', 'CAJA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('M', 'ID_CAJERO', 'CAJERO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('N', 'FECHA_HORA', 'FECHA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('O', 'NOTARJETA_CTA', 'NO. TARJETA O CTA DEPOSITO'),
            \PHPSpreadsheet::ColumnaExcel('P', 'TIPOTARJETA', 'TIPO DE TARJETA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('Q', 'COD_AUTORIZACION', 'COD AUTORIZACION', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('R', 'ATRASO', 'ATRASO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('S', 'OFICINA_CLIENTE', 'OFICINA CLIENTE', $estilos['centrado'])
        ];

        $filas = OperacionesDao::ConsultarDesembolsos($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::GeneraExcel('Consulta de Desembolsos Cultiva', 'Reporte', 'Consulta de Desembolsos', $columnas, $filas);
    }

    public function generarExcelPagos()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('A', 'LOCALIDAD', 'LOCALIDAD'),
            \PHPSpreadsheet::ColumnaExcel('B', 'SUCURSAL', 'SUCURSAL', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('C', 'TIPO_OPERACION', 'TIPO DE OPERACIÓN', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('D', 'ID_CLIENTE', 'ID CLIENTE', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('E', 'NUM_CUENTA', 'NUMERO DE CTA, CONTRATO, OPERACIÓN, PÓLIZA O NUMERO DE SEGURIDAD SOCIAL', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('F', 'INSTRUMENTO_MONETARIO', 'INSTRUMENTO MONETARIO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('G', 'MONEDA', 'MONEDA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('H', 'MONTO', 'MONTO', $estilos['moneda']),
            \PHPSpreadsheet::ColumnaExcel('I', 'FECHA_OPERACION', 'FECHA DE LA OPERACIÓN', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('J', 'TIPO_RECEPTOR', 'TIPO RECEPTOR', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('K', 'CLAVE_RECEPTOR', 'CLAVE DE RECEPTOR'),
            \PHPSpreadsheet::ColumnaExcel('L', 'NUM_CAJA', 'CAJA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('M', 'ID_CAJERO', 'CAJERO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('N', 'FECHA_HORA', 'FECHA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('O', 'NOTARJETA_CTA', 'NO. TARJETA O CTA DEPOSITO'),
            \PHPSpreadsheet::ColumnaExcel('P', 'TIPOTARJETA', 'TIPO DE TARJETA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('Q', 'COD_AUTORIZACION', 'COD AUTORIZACION', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('R', 'ATRASO', 'ATRASO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('S', 'OFICINA_CLIENTE', 'OFICINA CLIENTE', $estilos['centrado'])
        ];

        $filas = OperacionesDao::ConsultarPagos($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::GeneraExcel('Consulta de Pagos Cultiva', 'Reporte', 'Consulta de Pagos', $columnas, $filas);
    }

    public function generarExcelPagosF()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('A', 'LOCALIDAD', 'LOCALIDAD'),
            \PHPSpreadsheet::ColumnaExcel('B', 'SUCURSAL', 'SUCURSAL', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('C', 'TIPO_OPERACION', 'TIPO DE OPERACIÓN', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('D', 'ID_CLIENTE', 'ID CLIENTE', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('E', 'NUM_CUENTA', 'NUMERO DE CTA, CONTRATO, OPERACIÓN, PÓLIZA O NUMERO DE SEGURIDAD SOCIAL', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('F', 'INSTRUMENTO_MONETARIO', 'INSTRUMENTO MONETARIO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('G', 'MONEDA', 'MONEDA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('H', 'MONTO', 'MONTO', $estilos['moneda']),
            \PHPSpreadsheet::ColumnaExcel('I', 'FECHA_OPERACION', 'FECHA DE LA OPERACIÓN', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('J', 'TIPO_RECEPTOR', 'TIPO RECEPTOR', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('K', 'CLAVE_RECEPTOR', 'CLAVE DE RECEPTOR'),
            \PHPSpreadsheet::ColumnaExcel('L', 'NUM_CAJA', 'CAJA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('M', 'ID_CAJERO', 'CAJERO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('N', 'FECHA_HORA', 'FECHA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('O', 'NOTARJETA_CTA', 'NO. TARJETA O CTA DEPOSITO'),
            \PHPSpreadsheet::ColumnaExcel('P', 'TIPOTARJETA', 'TIPO DE TARJETA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('Q', 'COD_AUTORIZACION', 'COD AUTORIZACION', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('R', 'ATRASO', 'ATRASO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('S', 'OFICINA_CLIENTE', 'OFICINA CLIENTE', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('T', 'FEC_NAC', 'FECHA NACIMIENTO', $estilos['fecha']),
            \PHPSpreadsheet::ColumnaExcel('U', 'EDAD', 'EDAD', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('V', 'CICLO', 'CICLO', $estilos['centrado'])
        ];

        $filas = OperacionesDao::ConsultarPagosNacimiento($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::GeneraExcel('Consulta de Pagos X Fecha Nacimiento Cultiva', 'Reporte', 'Consulta de Pagos por Fecha de Nacimiento', $columnas, $filas);
    }

    public function generarExcelPagosIC()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('A', 'CDGCL', 'ID CLIENTE', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('B', 'GRUPO', 'CUENTA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('C', 'ORIGEN', 'Origen', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('D', 'NOMBRE', 'NOMBRE'),
            \PHPSpreadsheet::ColumnaExcel('E', 'ADICIONAL', 'ADICIONAL'),
            \PHPSpreadsheet::ColumnaExcel('F', 'A_PATERNO', 'APELLIDO PATERNO'),
            \PHPSpreadsheet::ColumnaExcel('G', 'A_MATERNO', 'APELLIDO MATERNO'),
            \PHPSpreadsheet::ColumnaExcel('H', 'TIPO_PERSONA', 'TIPO DE PERSONA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('I', 'RFC', 'RFC'),
            \PHPSpreadsheet::ColumnaExcel('J', 'CURP', 'CURP'),
            \PHPSpreadsheet::ColumnaExcel('K', 'RAZON_SOCIAL', 'RAZÓN SOCIAL O DENOMINACIÓN'),
            \PHPSpreadsheet::ColumnaExcel('L', 'FECHA_NAC', 'FECHA DE NACIMIENTO O CONSTITUCIÓN', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('M', 'NACIONALIDAD', 'NACIONALIDAD', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('N', 'DOMICILIO', 'DOMICILIO (calle, número exterior e interior (si aplica) y código postal)'),
            \PHPSpreadsheet::ColumnaExcel('O', 'COLONIA', 'COLONIA'),
            \PHPSpreadsheet::ColumnaExcel('P', 'CIUDAD', 'CIUDAD O POBLACIÓN'),
            \PHPSpreadsheet::ColumnaExcel('Q', 'PAIS', 'PAIS', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('R', 'SUC_ID_ESTADO', 'ESTADO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('S', 'TELEFONO', 'TELEFONO OFICINA/PARTICULAR', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('T', 'ID_ACTIVIDAD_ECONO', 'ACTIVIDAD ECONOMICA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('U', 'CALIFICACION', 'CALIFICACIÓN', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('V', 'ALTA', 'FECHA ALTA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('W', 'ID_SUCURSAL_SISTEMA', 'SUCURSAL', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('X', 'GENERO', 'GENERO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('Y', 'CORREO_ELECTRONICO', 'CORREO ELECTRONICO'),
            \PHPSpreadsheet::ColumnaExcel('Z', 'FIRMA_ELECT', 'FIRMA ELEC.'),
            \PHPSpreadsheet::ColumnaExcel('AA', 'PROFESION', 'PROFESION', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AB', 'OCUPACION', 'OCUPACION', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AC', 'PAIS_NAC', 'PAIS NAC.', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AD', 'EDO_NAC', 'EDO. NAC.', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AE', 'LUGAR_NAC', 'LUGAR NAC.'),
            \PHPSpreadsheet::ColumnaExcel('AF', 'NUMERO_DOCUMENTO', 'NUMERO DE DOCUMENTO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AG', 'CONOCIMIENTO', 'CONOCIMIENTO CLIENTE', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AH', 'INMIGRACION', 'REGISTRO NACIONAL DE INMIGRACION', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AI', 'CUENTA_ORIGINAL', 'CUENTA ORIGINAL', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AJ', 'SITUACION_CREDITO', 'SITUACIÓN CLIENTE', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AK', 'TIPO_DOCUMENTO', 'TIPO DOCUMENTO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AL', 'INDICADOR_EMPLEO', 'INDICADOR EMPLEO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AM', 'EMPRESAS', 'EMPRESA LABORA(Ó)'),
            \PHPSpreadsheet::ColumnaExcel('AN', 'INDICADOR_GOBIERNO', 'INDICADOR GOBIERNO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AO', 'PUESTO', 'PUESTO'),
            \PHPSpreadsheet::ColumnaExcel('AP', 'FECHA_INICIO', 'FECHA INICIO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AQ', 'FEH_FIN', 'FEH FIN', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AR', 'CP', 'CP', $estilos['centrado'])
        ];

        $filas = OperacionesDao::ConsultarClientes($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::GeneraExcel('Identificación de Clientes Cultiva', 'Reporte', 'Identificación de Clientes', $columnas, $filas);
    }

    public function generarExcelClientesCR()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('A', 'CLIENTE', 'CLIENTE', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('B', 'GRUPO', 'NUMERO DE CUENTA- CONTRATO-OPERACIÓN- PÓLIZA O NSS2', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('C', 'CUENTA_RELACION', 'NO. CUENTA RELACIONADA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('D', 'DESCRIPCION_OPERACION', 'DESCRIPCION DE LA OPERACIÓN*', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('E', 'IDENTIFICA_CUENTA', 'IDENTIFICA CUENTA como interna', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('F', 'CONSERVA', 'CONSERVA CUENTA ORIGINAL'),
            \PHPSpreadsheet::ColumnaExcel('G', 'OFICINA_CLIENTE', 'OFICINA CLIENTE', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('H', 'FECHA_INICIO_OPERACION', 'FECHA INICIO OPERACIÓN', $estilos['centrado'])
        ];

        $filas = OperacionesDao::CuentasRelacionadas($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::GeneraExcel('Cuentas Relacionadas Cultiva', 'Reporte', 'Cuentas Relacionadas PLD Cultiva', $columnas, $filas);
    }

    public function generarExcelClientesPT()
    {
        $estilos = \PHPSpreadsheet::GetEstilosExcel();

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('A', 'CDGCL', 'ID CLIENTE', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('B', 'GRUPO', 'Cuenta', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('C', 'INSTRUMENTO', 'INSTRUMENTO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('D', 'TIPO_MONEDA', 'TIPO MONEDA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('E', 'T_CAMBIO', 'T/CAMBIO'),
            \PHPSpreadsheet::ColumnaExcel('F', 'MONT_PRESTAMO', 'MONTO Prest/INV.'),
            \PHPSpreadsheet::ColumnaExcel('G', 'PLAZO', 'PLAZO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('H', 'FRECUENCIA', 'FRECUENCIA', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('I', 'TOTAL_PAGOS', 'TOTAL PAGOS', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('J', 'MONTO_FIN_PAGO', 'Monto C/Pago'),
            \PHPSpreadsheet::ColumnaExcel('K', 'ADELANTAR_PAGO', 'AUT. ADELANTAR PAGO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('L', 'NUMERO_APORTACIONES', 'NO.APORTACIONES', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('M', 'MONTO_APORTACIONES', 'Monto APORTACIONES'),
            \PHPSpreadsheet::ColumnaExcel('N', 'ID_SUCURSAL_SISTEMA', 'SUCURSAL', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('O', 'ORIGEN_RECURSO', 'ORIGEN RECURSOS'),
            \PHPSpreadsheet::ColumnaExcel('P', 'DESTINO_RECURSOS', 'DESTINO RECURSOS'),
            \PHPSpreadsheet::ColumnaExcel('Q', 'FECHA_INICIO_CREDITO', 'FECHA INICIO CREDITO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('R', 'FECHA_FIN', 'FECHA FIN CREDITO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('S', 'DESTINO', 'Destino/nacionalidad', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('T', 'ORIGEN', 'Origen/nacionalidad2', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('U', 'TIPO_OPERACION', 'TIPO OPERACIÓN', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('V', 'INST_MONETARIO', 'INSTR MONETARIOS', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('W', 'TIPO_CREDITO', 'TIPO CRÉDITO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('X', 'PRODUCTO', 'CLAVE PRODUCTO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('Y', 'PAIS_ORIGEN', 'PAIS ORIGEN', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('Z', 'PAIS_DESTINO', 'PAIS DESTINO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AA', 'ALTA_CONTRATO', 'ALTA CONTRATO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AB', 'TIPO_CONTRATO', 'TIPO DE CONTRATO', $estilos['centrado']),
            \PHPSpreadsheet::ColumnaExcel('AC', 'TIP_DOC', 'TIPO DE DOCUMENTO/FOLIO'),
            \PHPSpreadsheet::ColumnaExcel('AD', 'LATLON', 'LATITUD/LONGITUD'),
            \PHPSpreadsheet::ColumnaExcel('AE', 'LOCALIZACION', 'LOCALIZACION'),
            \PHPSpreadsheet::ColumnaExcel('AF', '', 'PROPIETARIO REAL'),
            \PHPSpreadsheet::ColumnaExcel('AG', '', 'PROVEEDOR DE RECURSOS')
        ];

        $filas = OperacionesDao::ConsultarPerfilTransaccional($_GET['Inicial'], $_GET['Final']);

        \PHPSpreadsheet::GeneraExcel('Perfil Transaccional Cultiva', 'Reporte', 'Perfil Transaccional Cultiva', $columnas, $filas);
    }
}
