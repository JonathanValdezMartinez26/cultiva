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
                    datos.forEach((item) => tabla.row.add(Object.values(item)).draw(false))
                }

                const respuestaError = (mensaje) => {
                    $(".resultado").toggleClass("conDatos", false)
                    showError(mensaje).then(() => actualizaDatosTabla(idTablaPrincipal, []))
                }

                const buscarReferencias = () => {
                    const fecha = $("#fecha").val()
                    const noCredito = $("#noCredito").val()
                    const institucion = $("#institucion").val()

                    consultaServidor("/Creditos/GetReporteReferencias", { fecha, institucion, noCredito }, (res) => {
                        if (!res.success) return respuestaError(res.mensaje)
                        if (res.datos.length === 0) return respuestaError("No se encontraron registros para los parámetros solicitados.")

                        actualizaDatosTabla(idTablaPrincipal, res.datos)
                        $(".resultado").toggleClass("conDatos", true)
                    })
                }

                const exportarExcel = () => {
                    const fecha = $("#fecha").val()
                    const noCredito = $("#noCredito").val()
                    const institucion = $("#institucion").val()

                    consultaServidor("/Creditos/ExportReporteReferencias", { fecha, institucion, noCredito }, (res) => {
                        if (!res.success) return respuestaError(res.mensaje)
                        if (res.datos.length === 0) return respuestaError("No se encontraron registros para los parámetros solicitados.")

                        const datos = res.datos.map((item) => Object.values(item))
                        const columnas = Object.keys(res.datos[0])
                        const titulo = "Reporte de Referencias"
                        descargaExcel(datos, columnas, titulo)
                    })
                }

                $(document).ready(() => {
                    configuraTabla(idTablaPrincipal)
                    $("#buscar").click(buscarReferencias)
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
}