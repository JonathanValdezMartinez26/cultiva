<?php

namespace App\controllers;

defined("APPPATH") or die("Access denied");

use Core\View;
use Core\Controller;
use App\models\Tesoreria as TesoreriaDao;

class Tesoreria extends Controller
{
    private $_contenedor;

    function __construct()
    {
        parent::__construct();
        $this->_contenedor = new Contenedor;
    }

    public function index()
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

                const idTabla = "clientes"

                const getParametros = (post = true) => {
                    const p = {
                        fechaI: $("#fechaI").val(),
                        fechaF: $("#fechaF").val()
                    }

                    if (post) return p
                    return Object.keys(p).map((key) => key + "=" + p[key]).join("&")
                }

                const buscarClientes = () => {
                    consultaServidor("/Tesoreria/ConsultaClientes", getParametros(), (res) => {
                        if (!res.success) return respuestaError(idTabla, res.mensaje)
                        if (res.datos.length === 0) return respuestaError(idTabla, "No se encontraron registros para los parámetros solicitados.")

                        respuestaSuccess(idTabla, res.datos)
                    })
                }

                $(document).ready(() => {
                    configuraTabla(idTabla)

                    $("#buscar").click(buscarClientes)
                    $("#exportar").click(() => descargaExcel("/Tesoreria/ExportReporteClientes/?" + getParametros(false)))
                })
            </script>
        HTML;

        View::set('header', $this->_contenedor->header($this->GetExtraHeader("Consulta de clientes")));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render("tesoreria_consulta_clientes");
    }

    public function ConsultaClientes()
    {
        echo json_encode(TesoreriaDao::ConsultaGruposCultiva($_POST));
    }

    public function ExportReporteClientes()
    {
        $columnas = [
            \PHPSpreadsheet::ColumnaExcel('SUCURSAL', 'Sucursal'),
            \PHPSpreadsheet::ColumnaExcel('NOMBRE_GRUPO', 'Grupo'),
            \PHPSpreadsheet::ColumnaExcel('CLIENTE', 'Cliente'),
            \PHPSpreadsheet::ColumnaExcel('DOMICILIO', 'Domicilio'),
        ];

        $filas = TesoreriaDao::ConsultaGruposCultiva($_GET);
        $filas = $filas['success'] ? $filas['datos'] : [];

        \PHPSpreadsheet::DescargaExcel('Reporte de clientes CULTIVA', 'Reporte', 'Clientes', $columnas, $filas);
    }

    public function ReingresarClientesCredito()
    {
        $extraHeader = <<<html
        <title>Reingresar Clientes Cultiva</title>
        <link rel="shortcut icon" href="/img/logo.png">
html;
        $extraFooter = <<<html
      <script>
       
       ponerElCursorAlFinal('Credito');
       
       function ActivarCredito(cdgcl, fecha, motivo){	
           
            if(motivo == '')
                {
                     swal("Atención", "Ingrese un monto mayor a $0", "warning");
                     document.getElementById("monto_e").focus();
                  
                }
            else
                {
                    $.ajax({
                    type: 'POST',
                    url: '/Cultiva/ReactivarCredito/',
                    data: "cdgcl="+cdgcl,
                    success: function(respuesta) {
                         if(respuesta=='1'){
                    
                                swal("Registro guardado exitosamente", {
                                      icon: "success",
                                    });
                        location.reload();
                        }else 
                            {
                                swal(respuesta, {
                                      icon: "error",
                                    });
                            }
                    }
                    });
                }
    }
    
       $(document).ready(function(){
            $("#muestra-cupones").tablesorter();
          var oTable = $('#muestra-cupones').DataTable({
                "columnDefs": [{
                    "orderable": false,
                    "targets": 0
                }],
                 "order": false
            });
            // Remove accented character from search input as well
            $('#muestra-cupones input[type=search]').keyup( function () {
                var table = $('#example').DataTable();
                table.search(
                    jQuery.fn.DataTable.ext.type.search.html(this.value)
                ).draw();
            });
            var checkAll = 0;
            
        });
      
      </script>
html;

        $credito = $_GET['Credito'];

        if ($credito != '') {

            $Clientes = TesoreriaDao::ReingresarClientesCredito($credito);
            $tabla = '';

            foreach ($Clientes[0] as $key => $value) {

                $tabla .= <<<html
                <tr style="padding: 0px !important;">
                    <td style="padding: 10px !important;">{$value['CDGNS']}</td>
                    <td style="padding: 10px !important;">{$value['CDGCL']}</td>
                    <td style="padding: 10px !important;">{$value['NOMBRE_CLIENTE']}</td>
                    <td style="padding: 10px !important;">{$value['FECHA_BAJA']}</td>
                    <td style="padding: 10px !important;">{$value['MOTIVO_BAJA']}</td>
                    <td> <button type="button" class="btn btn-danger btn-circle" onclick="ActivarCredito('{$value['CDGCL']}', '{$value['FECHA_BAJA_REAL']}', '{$value['CODIGO_MOTIVO']}');"><i class="fa fa-check"></i></button></td>
                </tr>
html;
            }
            View::set('header', $this->_contenedor->header($extraHeader));
            View::set('footer', $this->_contenedor->footer($extraFooter));
            View::set('tabla', $tabla);
            View::set('Nombre', $Clientes[1]['NOMBRE']);
            View::render("reingresar_clientes_cultiva_sec");
        } else {
            View::set('header', $this->_contenedor->header($extraHeader));
            View::set('footer', $this->_contenedor->footer($extraFooter));
            View::render("reingresar_clientes_cultiva_ini");
        }
    }
}
