<?php

namespace App\controllers;

defined("APPPATH") or die("Access denied");

use Core\View;
use Core\Controller;
use App\models\Creditos as CreditosDao;

class Creditos extends Controller
{
    private $_contenedor;

    function __construct()
    {
        parent::__construct();
        $this->_contenedor = new Contenedor;
    }

    public function ReporteReferencias()
    {
        $regSuc = CreditosDao::GetRegionSucursal();
        $regSuc = $regSuc['success'] ? json_encode($regSuc['datos']) : [];
        $suc = $_SESSION['cdgco'] ?? '';

        $js = <<<HTML
            <script>
                {$this->mensajes}
                {$this->consultaServidor}
                {$this->configuraTabla}
                {$this->descargaExcel}

                const idTablaPrincipal = "tablaPrincipal"
                const regSuc = JSON.parse('$regSuc')

                const actualizaDatosTabla = (id, datos) => {
                    const tabla = $("#" + id).DataTable()
                    tabla.clear().draw()
                    datos.forEach((item) => {
                        if (Array.isArray(item)) tabla.row.add(item).draw(false)
                        else tabla.row.add(Object.values(item)).draw(false)
                    })
                }

                const respuestaError = (mensaje) => {
                    $(".resultado").toggleClass("conDatos", false)
                    showError(mensaje).then(() => actualizaDatosTabla(idTablaPrincipal, []))
                }

                const getParametros = () => {
                    const p = {}

                    if ($("#credito").val()) p.credito = $("#credito").val()
                    else {
                        if ($("#situacion").val()) p.situacion = $("#situacion").val()
                        if ($("#region").val()) p.region = $("#region").val()
                        if ($("#sucursal").val()) p.sucursal = $("#sucursal").val()
                    }

                    return p
                }

                const buscarReferencias = () => {
                    consultaServidor("/Creditos/GetReporteReferencias", getParametros(), (res) => {
                        if (!res.success) return respuestaError(res.mensaje)
                        if (res.datos.length === 0) return respuestaError("No se encontraron registros para los parámetros solicitados.")

                        const tipo = getElementoRef()
                        const datos = res.datos.map((item) => {
                            const paycash = getElementoRef(item.REF_PAGO_PAYCASH, item.REF_COMISION_PAYCASH)
                            const bancopel = getElementoRef(item.REF_PAGO_BANCOPPEL, item.REF_COMISION_BANCOPPEL)
                            const oxxo = getElementoRef(item.REF_PAGO_OXXO, item.REF_COMISION_OXXO)

                            return [
                                item.GRUPO,
                                item.CREDITO,
                                item.ULTIMO_CICLO,
                                item.SITUACION,
                                item.SUCURSAL,
                                item.REGION,
                                tipo.outerHTML,
                                paycash.outerHTML,
                                bancopel.outerHTML,
                                oxxo.outerHTML
                            ]
                        })

                        actualizaDatosTabla(idTablaPrincipal, datos)
                        $(".resultado").toggleClass("conDatos", true)
                    })
                }

                const getElementoRef = (pago = "Pago", comision = "Comisión") => {
                    const tipo = document.createElement("div")
                    const pagos = document.createElement("div")
                    const hr = document.createElement("hr")
                    const comisiones = document.createElement("div")
                    const nd = "<b>No Disponible</b>"

                    pagos.innerHTML = "<span>" + (pago ?? nd) + "</span>"
                    comisiones.innerHTML = "<span>" + (comision ?? nd) + "</span>"

                    tipo.appendChild(pagos)
                    tipo.appendChild(hr)
                    tipo.appendChild(comisiones)
                    return tipo
                }

                const exportarExcel = () => {
                    const parametros = getParametros()
                    const qry = Object.keys(parametros).map((key) => key + "=" + parametros[key]).join("&")

                    descargaExcel("/Creditos/ExportReporteReferencias/?" + qry)
                }

                const actualizaSucursales = () => {
                    const region = $("#region").val()
                    $("#sucursal").empty()
                    $("#sucursal").append(new Option("Todas", ""))
                    regSuc.forEach((suc) => {
                        if (suc.REGION === region || region === "") $("#sucursal").append(new Option(suc.NOMBRE_SUCURSAL, suc.SUCURSAL))
                    })
                }

                $(document).ready(() => {
                    configuraTabla(idTablaPrincipal)
                    $("#buscar").click(buscarReferencias)
                    $("#descargarExcel").click(exportarExcel)

                    $("#region").append(new Option("Todas", ""))
                    regSuc.forEach((reg) => {
                        if ($("#region option[value='" + reg.REGION + "']").length === 0)
                            $("#region").append(new Option(reg.NOMBRE_REGION, reg.REGION))

                        if (reg.SUCURSAL === "$suc") $("#region").val(reg.REGION)
                    })
                    actualizaSucursales()

                    $("#region").change(actualizaSucursales)
                })
            </script>
        HTML;

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Reporte de Referencias")));
        View::set('footer', $this->_contenedor->footer($js));
        View::render('reporte_referencias');
    }

    public function GetReporteReferencias()
    {
        echo json_encode(CreditosDao::GetReporteReferencias($_POST));
    }

    public function ExportReporteReferencias()
    {

        $estilos = \PHPSpreadsheet::GetEstilosExcel();
        $texto = ['estilo' => $estilos['texto_centrado']];

        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('GRUPO', 'Grupo'),
            \PHPSpreadsheet::ColumnaExcel('CREDITO', 'Crédito', $texto),
            \PHPSpreadsheet::ColumnaExcel('ULTIMO_CICLO', 'Ultimo Ciclo', $texto),
            \PHPSpreadsheet::ColumnaExcel('SITUACION', 'Situación'),
            \PHPSpreadsheet::ColumnaExcel('REGION', 'Región'),
            \PHPSpreadsheet::ColumnaExcel('SUCURSAL', 'Sucursal'),
            \PHPSpreadsheet::ColumnaExcel('Pago', [
                \PHPSpreadsheet::ColumnaExcel('REF_PAGO_PAYCASH', 'Paycash', $texto),
                \PHPSpreadsheet::ColumnaExcel('REF_PAGO_BANCOPPEL', 'Bancoppel', $texto),
                \PHPSpreadsheet::ColumnaExcel('REF_PAGO_OXXO', 'Oxxo', $texto),
            ]),
            \PHPSpreadsheet::ColumnaExcel('Comisión', [
                \PHPSpreadsheet::ColumnaExcel('REF_COMISION_PAYCASH', 'Paycash', $texto),
                \PHPSpreadsheet::ColumnaExcel('REF_COMISION_BANCOPPEL', 'Bancoppel', $texto),
                \PHPSpreadsheet::ColumnaExcel('REF_COMISION_OXXO', 'Oxxo', $texto),
            ]),
        ];

        $datos = CreditosDao::GetReporteReferencias($_GET);
        $filas = $datos['success'] ? $datos['datos'] : [];

        \PHPSpreadsheet::DescargaExcel('Referencias Cultiva', 'Reporte', 'Referencias', $columnas, $filas);
    }
}
