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
        $js = <<<HTML
            <script>
                {$this->mensajes}
                {$this->consultaServidor}
                {$this->configuraTabla}
                {$this->descargaExcel}

                const idTablaPrincipal = "tablaPrincipal"

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

                const buscarReferencias = () => {
                    const credito = $("#noCredito").val()

                    consultaServidor("/Creditos/GetReporteReferencias", { credito }, (res) => {
                        if (!res.success) return respuestaError(res.mensaje)
                        if (res.datos.length === 0) return respuestaError("No se encontraron registros para los parámetros solicitados.")

                        const tipo = getElementoRef()
                        const datos = res.datos.map((item) => {
                            const paycash = getElementoRef(item.REF_PAGO_PAYCASH, item.REF_COMISION_PAYCASH)
                            const bancopel = getElementoRef(item.REF_PAGO_BANCOPPEL, item.REF_COMISION_BANCOPPEL)
                            const oxxo = getElementoRef(item.REF_PAGO_OXXO, item.REF_COMISION_OXXO)

                            const nItem = {
                                Credito: item.CREDITO,
                                Grupo: item.GRUPO,
                                Sucursal: item.SUCURSAL,
                                Tipo: tipo.outerHTML,
                                Paycash: paycash.outerHTML,
                                Bancopel: bancopel.outerHTML,
                                Oxxo: oxxo.outerHTML
                            }
                            return nItem
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
                    const credito = $("#noCredito").val()

                    descargaExcel("/Creditos/ExportReporteReferencias/?credito=" + credito)
                }

                $(document).ready(() => {
                    configuraTabla(idTablaPrincipal)
                    $("#buscar").click(buscarReferencias)
                    $("#descargarExcel").click(exportarExcel)
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
            \PHPSpreadsheet::ColumnaExcel('CREDITO', 'Crédito', $texto),
            \PHPSpreadsheet::ColumnaExcel('GRUPO', 'Grupo'),
            \PHPSpreadsheet::ColumnaExcel('SUCURSAL', 'Sucursal'),
            \PHPSpreadsheet::ColumnaExcel('REF_PAGO_OXXO', 'Pago Oxxo', $texto),
            \PHPSpreadsheet::ColumnaExcel('REF_PAGO_PAYCASH', 'Pago Paycash', $texto),
            \PHPSpreadsheet::ColumnaExcel('REF_PAGO_BANCOPPEL', 'Pago Bancoppel', $texto),
            \PHPSpreadsheet::ColumnaExcel('REF_COMISION_OXXO', 'Comisión Oxxo', $texto),
            \PHPSpreadsheet::ColumnaExcel('REF_COMISION_PAYCASH', 'Comisión Paycash', $texto),
            \PHPSpreadsheet::ColumnaExcel('REF_COMISION_BANCOPPEL', 'Comisión Bancoppel', $texto)
        ];

        $datos = CreditosDao::GetReporteReferencias($_GET);
        $filas = $datos['success'] ? $datos['datos'] : [];

        \PHPSpreadsheet::DescargaExcel('Referencias Cultiva', 'Reporte', 'Referencias', $columnas, $filas);
    }
}
