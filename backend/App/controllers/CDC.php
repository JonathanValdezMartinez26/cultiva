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

    private $getIcono = <<<JAVASCRIPT
        const getIcono = (tipo, funcion, texto = "") => {
            const tipos = {
                ver: { icono: "eye-open", color: "black" },
                subir: { icono: "cloud-upload", color: "red" },
                actualizar: { icono: "refresh", color: "blue" },
                nuevo: { icono: "plus", color: "orange" },
                ok: { icono: "ok-circle", color: "green" },
                default: { icono: "grain", color: "black" }
            }

            const t = tipos[tipo]
            const espacio = texto ? "&nbsp;" : ""
            return "<i class='glyphicon glyphicon-" + t.icono + "' style='cursor: pointer; font-size: 1.5em; color: " + t.color + "; ' onclick='" + funcion + "'>" + espacio + "</i>" + texto
        }
    JAVASCRIPT;
    private $validaCaducidad = <<<JAVASCRIPT
        const validaCaducidad = (fecha) => {
            if (!fecha) return false

            const partes = fecha.split("/")
            const caducidad = new Date(
                parseInt(partes[2], 10),
                parseInt(partes[1], 10) - 1,
                parseInt(partes[0], 10)
            )
            const hoy = new Date().setHours(0, 0, 0, 0)
            return caducidad < hoy
        }
    JAVASCRIPT;
    private $consultaCDC = <<<JAVASCRIPT
        const consultaCDC = () => {
            const aut = $("#autorizacion")[0].files[0]
            const id = $("#identificacion")[0].files[0]

            if (validaArchivo(aut) !== true) return
            if (validaArchivo(id) !== true) return

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
    JAVASCRIPT;
    private $verPDF = <<<JAVASCRIPT
        const verPDF = (cliente, folio = "", documento = "") => {
            let metodo = "GetDocumento"
            const host = window.location.origin
            $("#PDF").append($("<embed>", {
                src: host + "/CDC/" + metodo + "/?cliente=" + cliente + "&folio=" + folio + "&documento=" + documento,
                style: "width: 100%; height: 100%; z-index: 1000; position: relative;",
                id: "PDF_viewer"
            }))

            document.querySelector("#muestraPDF").show()
        }
        const cerrarPDF = () => {
            $("#PDF_viewer").remove()
            document.querySelector("#muestraPDF").close()
        }
    JAVASCRIPT;

    function __construct()
    {
        parent::__construct();
        $this->_contenedor = new Contenedor;
        $this->cnfg = App::getConfig();
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
                {$this->getIcono}
                {$this->validaCaducidad}

                const idTabla = "tablaPrincipal"

                const getParametrosBusqueda = () => {
                    if (!$("#cliente").val()) return showError("Debe proporcionar un número de cliente.")
                    
                    const p = {}

                    p.cliente = $("#cliente").val()

                    return p
                }

                const getParametrosConsulta = () => {
                    if (!$("#autorizacion")[0].files[0]) return showError("Debe seleccionar el archivo de autorización.")
                    if (!$("#identificacion")[0].files[0]) return showError("Debe seleccionar el archivo de identificación del cliente.")

                    const datos = new FormData();

                    datos.append("cliente", $("#noCliente").val())
                    datos.append("nombre1", $("#nombre1").val())
                    datos.append("nombre2", $("#nombre2").val())
                    datos.append("apellido1", $("#apellido1").val())
                    datos.append("apellido2", $("#apellido2").val())
                    datos.append("fecha", $("#fechaCDC").val())
                    datos.append("rfc", $("#rfc").val())
                    datos.append("calle", $("#calle").val())
                    datos.append("colonia", $("#colonia").val())
                    datos.append("municipio", $("#municipio").val())
                    datos.append("ciudad", $("#ciudad").val())
                    datos.append("estado", $("#estado").val())
                    datos.append("cp", $("#cp").val())

                    datos.append("autorizacion", $("#autorizacion")[0].files[0])
                    datos.append("identificacion", $("#identificacion")[0].files[0])

                    return datos
                }

                const getParametrosActualizacion = () => {
                    if (!$("#autorizacion")[0].files[0]) return showError("Debe seleccionar el archivo de autorización.")
                    if (!$("#identificacion")[0].files[0]) return showError("Debe seleccionar el archivo de identificación del cliente.")

                    const datos = new FormData();

                    datos.append("cliente", $("#noCliente").val())
                    datos.append("folio", $("#" + idTabla + " tbody tr").eq(0).find("td").eq(2).text())

                    datos.append("autorizacion", $("#autorizacion")[0].files[0])
                    datos.append("identificacion", $("#identificacion")[0].files[0])

                    return datos
                }

                const buscarConsultas = () => {
                    const parametros = getParametrosBusqueda()
                    if (!parametros) return

                    consultaServidor("/CDC/GetConsultaCliente", parametros, (res) => {
                        if (!res.success) return respuestaError(idTabla, res.mensaje)
                        if (res.datos.length === 0) return respuestaError(idTabla, "No se encontró información para el número de cliente proporcionado.")
                        
                        const datos = res.datos.map((datos, fila) => {
                            let boton = document.createElement("div")
                            boton.style = "display: flex; justify-content: space-evenly;"
                            boton.innerHTML = ""

                            if (datos.FOLIO === null) {
                                boton.innerHTML += getIcono("actualizar", "capturaDatosConsulta(" + datos.CLIENTE + ", true)")
                                boton.innerHTML += "<input type='hidden' value='" + JSON.stringify(datos) + "' id='" + datos.CLIENTE + "'/>"
                            } else if (datos.AUTORIZACION == 0 || datos.IDENTIFICACION == 0) {
                                boton.innerHTML += getIcono("subir", "capturaDatosConsulta(" + datos.CLIENTE + ", false)")
                                boton.innerHTML += "<input type='hidden' value='" + JSON.stringify(datos) + "' id='" + datos.CLIENTE + "'/>"
                                boton.innerHTML += getIcono("ver" , "verPDF(" + datos.CLIENTE + "," + datos.FOLIO + ", \"reporte\")")
                            } else {
                                if (validaCaducidad(datos.CADUCIDAD)) {
                                    boton.innerHTML += getIcono("actualizar", "capturaDatosConsulta(" + datos.CLIENTE + ", true)")
                                    boton.innerHTML += "<input type='hidden' value='" + JSON.stringify(datos) + "' id='" + datos.CLIENTE + "'/>"
                                } else boton.innerHTML += getIcono("ver" , "verPDF(" + datos.CLIENTE + "," + datos.FOLIO + ", \"reporte\")")
                            }
                            
                            return [
                                datos.CLIENTE,
                                datos.NOMBRE,
                                datos.FOLIO,
                                datos.FECHA,
                                datos.CADUCIDAD,
                                boton.outerHTML
                            ]
                        })

                        actualizaDatosTabla(idTabla, datos)
                        $(".resultado").toggleClass("conDatos", true)
                    })
                }

                const actualizaModal = (datos = {}) => {
                    $("#noCliente").val(datos.CLIENTE ?? "")
                    $("#rfc").val(datos.RFC ?? "")
                    $("#fecha").val(datos.NACIMIENTO ?? "")
                    $("#fechaCDC").val(datos.NACIMIENTO_CDC ?? "")
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
                    $("#identificacion").val("")
                }

                const capturaDatosConsulta = (id, consulta) => {
                    const fila = JSON.parse($("#" + idTabla + " #" + id).val())
                    actualizaModal(fila)

                    $("#consultaCDC").text(consulta ? "Consultar": "Subir Documentos")
                    $("#modalCDC").modal("show")
                }

                const consultaCDC = () => {
                    const aut = $("#autorizacion")[0].files[0]
                    const id = $("#identificacion")[0].files[0]

                    if (validaArchivo(aut) !== true) return
                    if (validaArchivo(id) !== true) return

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

                $(document).ready(() => {
                    configuraTabla(idTabla)
                    $("#cliente").keypress((e) => { if (e.which === 13) buscarConsultas() })
                    $("#buscar").click(buscarConsultas)
                    $("#consultaCDC").click(consultaCDC)
                })
            </script>
        HTML;

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Consulta CDC (Admin)")));
        View::set('footer', $this->_contenedor->footer($js));
        View::render('cdc_consulta');
    }

    public function ConsultaGrupal()
    {
        $regSuc = CDCDao::GetRegionSucursal();
        $regSuc = $regSuc['success'] ? json_encode($regSuc['datos']) : [];
        $suc = ''; //$_SESSION['cdgco'] ?? '';

        $js = <<<HTML
            <script>
                {$this->mensajes}
                {$this->configuraTabla}
                {$this->actualizaDatosTabla}
                {$this->consultaServidor}
                {$this->respuestaError}
                {$this->getIcono}
                {$this->validaCaducidad}

                const idTabla = "tablaPrincipal"
                const regSuc = JSON.parse('$regSuc')

                const getParametrosBusqueda = () => {
                    const p = {}

                    p.region = $("#region").val()
                    p.sucursal = $("#sucursal").val()
                    p.grupo = $("#grupo").val()

                    return p
                }

                const getParametrosConsulta = () => {
                    if (!$("#autorizacion")[0].files[0]) return showError("Debe seleccionar el archivo de autorización.")
                    if (!$("#identificacion")[0].files[0]) return showError("Debe seleccionar el archivo de identificación del cliente.")

                    const datos = new FormData();

                    datos.append("cliente", $("#noCliente").val())
                    datos.append("nombre1", $("#nombre1").val())
                    datos.append("nombre2", $("#nombre2").val())
                    datos.append("apellido1", $("#apellido1").val())
                    datos.append("apellido2", $("#apellido2").val())
                    datos.append("fecha", $("#fechaCDC").val())
                    datos.append("rfc", $("#rfc").val())
                    datos.append("calle", $("#calle").val())
                    datos.append("colonia", $("#colonia").val())
                    datos.append("municipio", $("#municipio").val())
                    datos.append("ciudad", $("#ciudad").val())
                    datos.append("estado", $("#estado").val())
                    datos.append("cp", $("#cp").val())

                    datos.append("autorizacion", $("#autorizacion")[0].files[0])
                    datos.append("identificacion", $("#identificacion")[0].files[0])

                    return datos
                }

                const getParametrosActualizacion = () => {
                    if (!$("#autorizacion")[0].files[0]) return showError("Debe seleccionar el archivo de autorización.")
                    if (!$("#identificacion")[0].files[0]) return showError("Debe seleccionar el archivo de identificación del cliente.")

                    const datos = new FormData();

                    datos.append("cliente", $("#noCliente").val())
                    datos.append("folio", $("#folio").val())

                    datos.append("autorizacion", $("#autorizacion")[0].files[0])
                    datos.append("identificacion", $("#identificacion")[0].files[0])

                    return datos
                }

                const getDatosCliente = (datos) => {
                    const cliente = { ...datos }
                    delete cliente.REGION
                    delete cliente.SUCURSAL
                    delete cliente.GRUPO

                    return cliente
                }

                const agrupaResultado = (datos) => {
                    const agrupado = []
                    datos.forEach((item, fila) => {
                        const cliente = getDatosCliente(item, fila)
                        let existente = agrupado.find(grupo => 
                            grupo.REGION === item.REGION && 
                            grupo.SUCURSAL === item.SUCURSAL && 
                            grupo.GRUPO === item.GRUPO
                        )

                        if (existente) existente.CLIENTES.push(cliente)
                        else {
                            existente = {
                                VER: 0,
                                SUBIR: 0,
                                ACTUALIZAR: 0,
                                REGION: item.REGION,
                                SUCURSAL: item.SUCURSAL,
                                GRUPO: item.GRUPO,
                                CLIENTES: [cliente]
                            }
                            agrupado.push(existente)
                        }

                        if (cliente.AUTORIZACION == 1 && cliente.IDENTIFICACION == 1)
                            if (validaCaducidad(cliente.CADUCIDAD)) existente.ACTUALIZAR++
                            else existente.VER++
                        else existente.SUBIR++
                    })

                    return agrupado
                }

                const generaTablaClientes = (clientes, fila) => {
                    const tabla = document.createElement("table")
                    const cabecera = document.createElement("thead")
                    const cuerpo = document.createElement("tbody")
                    const filaCabecera = document.createElement("tr")
                    const campos = ["Cliente", "Nombre", "Folio", "Fecha", "Caducidad", "Reporte"]
                    campos.forEach((campo) => {
                        const th = document.createElement("th")
                        th.innerText = campo
                        filaCabecera.appendChild(th)
                    })
                    cabecera.appendChild(filaCabecera)
                    tabla.appendChild(cabecera)

                    const datosVisibles = ["CLIENTE", "NOMBRE", "FOLIO", "FECHA", "CADUCIDAD"]
                    clientes.forEach((cliente, fila) => {
                        const tr = document.createElement("tr")
                        Object.keys(cliente).forEach((campo) => {
                            if (!datosVisibles.includes(campo)) return
                            const td = document.createElement("td")
                            td.innerText = cliente[campo]
                            tr.appendChild(td)
                        })
                        const reporte = document.createElement("td")
                        reporte.style = "display: flex; justify-content: space-evenly;"

                        if (cliente.AUTORIZACION == 0 || cliente.IDENTIFICACION == 0) {
                            reporte.innerHTML += getIcono("subir", "capturaDatosConsulta(" + cliente.CLIENTE + ", false)")
                            reporte.innerHTML += "<input type='hidden' value='" + JSON.stringify(cliente) + "' id='" + cliente.CLIENTE + "'/>"
                            reporte.innerHTML += getIcono("ver" , "verPDF(" + cliente.CLIENTE + "," + cliente.FOLIO + ", \"reporte\")")
                        } else {
                            if (validaCaducidad(cliente.CADUCIDAD)) {
                                reporte.innerHTML += getIcono("actualizar", "capturaDatosConsulta(" + cliente.CLIENTE + ", true)")
                                reporte.innerHTML += "<input type='hidden' value='" + JSON.stringify(cliente) + "' id='" + cliente.CLIENTE + "'/>"
                            } else reporte.innerHTML += getIcono("ver" , "verPDF(" + cliente.CLIENTE + "," + cliente.FOLIO + ", \"reporte\")")
                        }

                        tr.appendChild(reporte)
                        cuerpo.appendChild(tr)
                    })

                    tabla.style = "width: 100%; margin-top: 10px;"
                    tabla.appendChild(cuerpo)
                    return tabla.outerHTML
                }

                const buscarConsultas = () => {
                    const parametros = getParametrosBusqueda()
                    if (!parametros) return

                    consultaServidor("/CDC/GetConsultaGlobal", parametros, (res) => {
                        if (!res.success) return respuestaError(idTabla, res.mensaje)
                        if (res.datos.length === 0) return respuestaError(idTabla, "No se encontró información para el número de cliente proporcionado.")
                        
                        const datos = agrupaResultado(res.datos).map((item, fila) => {
                            const detalles = $("<details>")
                            const titulo = $("<summary>")

                            titulo.html("<i class='glyphicon glyphicon-user'>&nbsp;</i>Mostrar")
                            titulo.css("cursor", "pointer")

                            detalles.append(titulo)
                            detalles.append(generaTablaClientes(item.CLIENTES))
                            detalles.css("text-align", "left")

                            const subir = item.SUBIR > 0 ? 
                            "<div onclick='listaDocumentosPendientes(event)' style='cursor: pointer; font-weight: bold; height: 100%; width: 100%;'>" + item.SUBIR + "</div>"
                            : item.SUBIR

                            return [
                                item.GRUPO,
                                item.VER,
                                subir,
                                item.ACTUALIZAR,
                                detalles.prop("outerHTML")
                            ]
                        })

                        actualizaDatosTabla(idTabla, datos)
                        $(".resultado").toggleClass("conDatos", true)
                    })
                }

                const listaDocumentosPendientes = (e) => {
                    $("#modalDocPendientes #listado").empty()
                    
                    const fila = e.target.closest("tr")
                    const tabla = $(fila).find("table")

                    if (tabla.length > 0) {
                        const listado = $("#listado")
                        tabla.find('input').each(function () {
                            let informacion = $(this).val()
                            datos = JSON.parse(informacion)
                            listado.append("<tr><td>" + datos.CLIENTE + "</td><td>" + datos.FOLIO + "</td><td><input type='file' class='custom-file-input' accept='application/pdf' style='width: 90%; margin: 10px 0;'></td><td><input type='file' class='custom-file-input' accept='application/pdf' style='width: 90%; margin: 10px 0;'></td></tr>")
                        })
                        
                        $("#modalDocPendientes #listado").append(listado)
                    } else $("#modalDocPendientes #listado").html("<p>No hay documentos pendientes</p>")


                    $("#modalDocPendientes").modal("show")
                }

                const actualizaSucursales = () => {
                    const region = $("#region").val()
                    $("#sucursal").empty()
                    $("#sucursal").append(new Option("Todas", ""))

                    regSuc.filter((reg) => reg.REGION === region || region === "")
                        .sort((a, b) => a.NOMBRE_SUCURSAL.localeCompare(b.NOMBRE_SUCURSAL))
                        .forEach((suc) => {
                            if (suc.REGION === region || region === "") $("#sucursal").append(new Option(suc.NOMBRE_SUCURSAL, suc.SUCURSAL))
                            if (suc.SUCURSAL === "$suc") $("#sucursal").val(suc.SUCURSAL)
                        })
                }

                const actualizaModal = (datos = {}) => {
                    $("#noCliente").val(datos.CLIENTE ?? "")
                    $("#folio").val(datos.FOLIO ?? "")
                    $("#rfc").val(datos.RFC ?? "")
                    $("#fecha").val(datos.NACIMIENTO ?? "")
                    $("#fechaCDC").val(datos.NACIMIENTO_CDC ?? "")
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
                    $("#identificacion").val("")
                }

                const capturaDatosConsulta = (id, consulta) => {
                    const fila = JSON.parse($("#" +idTabla + " #" + id).val())
                    actualizaModal(fila)

                    $("#consultaCDC").text(consulta ? "Consultar": "Subir Documentos")
                    $("#modalCDC").modal("show")
                }

                const consultaCDC = () => {
                    const aut = $("#autorizacion")[0].files[0]
                    const id = $("#identificacion")[0].files[0]

                    if (validaArchivo(aut) !== true) return
                    if (validaArchivo(id) !== true) return

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

                const docPendientes = () => {
                    const documentos = $("#modalDocPendientes #listado")

                    const datos = new FormData()
                    let completo = true

                    documentos.find("tr").each(function (fila) {
                        if (!completo) return
                        const autorizacion = $(this).find("input[type='file']").eq(0)[0].files[0]
                        const identificacion = $(this).find("input[type='file']").eq(1)[0].files[0]

                        if (!autorizacion || !identificacion) {
                            completo = false
                            return showError("Debe seleccionar ambos archivos de soporte para el cliente " + $(this).find("td").eq(0).text())
                        }

                        datos.append("clientes[" + fila + "][cliente]", $(this).find("td").eq(0).text())
                        datos.append("clientes[" + fila + "][folio]", $(this).find("td").eq(1).text())
                        datos.append("clientes[" + fila + "][autorizacion]", autorizacion)
                        datos.append("clientes[" + fila + "][identificacion]", identificacion)
                    })

                    if (!completo) return

                    confirmarMovimiento("Circulo de crédito", "¿Seguro desea subir estos archivos?")
                    .then((continuar) => {
                        if (!continuar) return

                        consultaServidor("/CDC/SubeDocPendientes", datos, (res) => {
                            if (!res.success) return showError(res.mensaje)
                            showSuccess(res.mensaje).then(() => {
                                $("#modalDocPendientes").modal("hide")
                                buscarConsultas()
                            })
                        }, "POST", "JSON", false, false)
                    })
                }

                $(document).ready(() => {
                    configuraTabla(idTabla)
                    $("#region").append(new Option("Todas", ""))
                    regSuc.forEach((reg) => {
                        if ($("#region option[value='" + reg.REGION + "']").length === 0)
                            $("#region").append(new Option(reg.NOMBRE_REGION, reg.REGION))
                        
                        if (reg.SUCURSAL === "$suc") $("#region").val(reg.REGION)
                    })

                    $("#region").change(actualizaSucursales)
                    $("#cliente").keypress((e) => { if (e.which === 13) buscarConsultas() })
                    $("#buscar").click(buscarConsultas)
                    $("#consultaCDC").click(consultaCDC)
                    $("#guardarPendientes").click(docPendientes)

                    actualizaSucursales()
                    buscarConsultas()
                })

                $(document).on("click", "summary", function () {
                    let detalles = $(this).parent();
                    $(this).html("<i class='glyphicon glyphicon-user'>&nbsp;</i>" + (detalles.prop("open") ? "Mostrar" : "Ocultar"));
                })
            </script>
        HTML;

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Mis consultas")));
        View::set('footer', $this->_contenedor->footer($js));
        View::render('cdc_consulta_grupal');
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
                {$this->consultaCDC}

                const idTabla = "tablaPrincipal"

                const getParametrosBusqueda = () => {
                    if (!$("#cliente").val()) return showError("Debe proporcionar un número de cliente.")
                    
                    const p = {}

                    p.cliente = $("#cliente").val()

                    return p
                }

                const getParametrosConsulta = () => {
                    if (!$("#autorizacion")[0].files[0]) return showError("Debe seleccionar el archivo de autorización.")
                    if (!$("#identificacion")[0].files[0]) return showError("Debe seleccionar el archivo de identificación del cliente.")

                    const datos = new FormData();

                    datos.append("cliente", $("#noCliente").val())
                    datos.append("nombre1", $("#nombre1").val())
                    datos.append("nombre2", $("#nombre2").val())
                    datos.append("apellido1", $("#apellido1").val())
                    datos.append("apellido2", $("#apellido2").val())
                    datos.append("fecha", $("#fechaCDC").val())
                    datos.append("rfc", $("#rfc").val())
                    datos.append("calle", $("#calle").val())
                    datos.append("colonia", $("#colonia").val())
                    datos.append("municipio", $("#municipio").val())
                    datos.append("ciudad", $("#ciudad").val())
                    datos.append("estado", $("#estado").val())
                    datos.append("cp", $("#cp").val())

                    datos.append("autorizacion", $("#autorizacion")[0].files[0])
                    datos.append("identificacion", $("#identificacion")[0].files[0])

                    return datos
                }

                const getConfirmacionCargaArchivo = () => {
                    const confirmacion = document.createElement("div")
                    const t1 = document.createElement("p")
                    const condiciones = document.createElement("ul")
                    const archivos = document.createElement("input")

                    t1.innerText = "Para realizar la carga de documentos los archivos deben cumplir las siguientes condiciones:"

                    condiciones.style = "text-align: left; margin-left: 50px;"
                    condiciones.innerHTML += "<li>El archivo debe ser de tipo PDF.</li>"
                    condiciones.innerHTML += "<li>El archivo no debe superar los 2MB.</li>"
                    
                    archivos.type = "file"
                    archivos.accept = ".pdf"
                    archivos.style = "width: 100%;"

                    confirmacion.id = "confirmacion"
                    confirmacion.appendChild(t1)
                    confirmacion.appendChild(condiciones)
                    confirmacion.appendChild(archivos)

                    return confirmacion
                }

                const getIcono = (icono, color, funcion) => {
                    return "<i class='glyphicon glyphicon-" + icono + "' style='cursor: pointer; font-size: 1.5em; color: " + color + "; ' onclick='" + funcion + "'></i>"
                }

                const buscarConsultas = () => {
                    const parametros = getParametrosBusqueda()
                    if (!parametros) return

                    consultaServidor("/CDC/GetConsultaCliente", parametros, (res) => {
                        if (!res.success) return respuestaError(idTabla, res.mensaje)
                        if (res.datos.length === 0) return respuestaError(idTabla, "No se encontró información para el número de cliente proporcionado.")

                        const datos = res.datos.map((item, fila) => {
                            let autorizacion = ""
                            let identificacion = ""
                            let reporte = ""

                            if (item.AUTORIZACION == 0) autorizacion += getIcono("cloud-upload", "red", "cargaArchivo(" + item.CLIENTE + "," + item.FOLIO + ", \"autorizacion\")")
                            else autorizacion += getIcono("ok-circle", "green", "verPDF(" + item.CLIENTE + "," + item.FOLIO + ", \"autorizacion\")")

                            if (item.IDENTIFICACION == 0) identificacion += getIcono("cloud-upload", "red", "cargaArchivo(" + item.CLIENTE + "," + item.FOLIO + ", \"identificacion\")")
                            else identificacion += getIcono("ok-circle", "green", "verPDF(" + item.CLIENTE + "," + item.FOLIO + ", \"identificacion\")")


                            if (fila === 0 && validaCaducidad(item.CADUCIDAD)) {    
                                reporte += getIcono("refresh", "blue", "capturaDatosConsulta(" + fila + ")")
                                reporte += "<input type='hidden' value='" + JSON.stringify(item) + "' id='" + fila + "'/>"
                            } else reporte += getIcono("eye-open", "black", "verPDF(" + item.CLIENTE + "," + item.FOLIO + ", \"reporte\")")
                        
                            const documentos = "<div style='display: flex; justify-content: space-evenly;'>" + autorizacion + "|" + identificacion + "|" + reporte + "</div>"
                            
                            return [
                                item.CLIENTE,
                                item.NOMBRE,
                                item.FOLIO,
                                item.FECHA,
                                item.CADUCIDAD,
                                documentos
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

                const cargaArchivo = (cliente, folio, documento) => {
                    confirmarMovimiento("Circulo de crédito", null, getConfirmacionCargaArchivo())
                    .then((continuar) => {
                        if (!continuar) return

                        const archivos = $("#confirmacion input[type='file']")[0].files
                        if (archivos.length === 0) return showError("Debe seleccionar un archivo.")
                        if (!validaArchivo(archivos)) return
                        
                        confirmarMovimiento("Circulo de crédito", "Se guardara este archivo como " + documento + ".\\n¿continuar?")
                        .then((continuar) => {
                            if (!continuar) return
                            
                            const datos = new FormData()
                            datos.append("cliente", cliente)
                            datos.append("folio", folio)
                            datos.append(documento, archivos[0])
                            datos.append("unico", "true")
                            
                            consultaServidor("/CDC/SubeDocumentosCDC", datos, (res) => {
                                if (!res.success) return showError(res.mensaje)
                                showSuccess(res.mensaje).then(() => buscarConsultas())
                            }, "POST", "JSON", false, false)
                        })
                    })
                }

                const actualizaModal = (datos = {}) => {
                    $("#noCliente").val(datos.CLIENTE ?? "")
                    $("#rfc").val(datos.RFC ?? "")
                    $("#fecha").val(datos.NACIMIENTO ?? "")
                    $("#fechaCDC").val(datos.NACIMIENTO_CDC ?? "")
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
                    $("#identificacion").val("")
                }

                const capturaDatosConsulta = (id) => {
                    const fila = JSON.parse($("#" + id).val())
                    actualizaModal(fila)
                    $("#modalCDC").modal("show")
                }

                const validaArchivo = (archivo) => {
                    if (!archivo) return showError("Debe seleccionar los archivos de soporte.")
                    if (archivo.size > 2097152) return showError("El archivo no debe superar los 2MB.")
                    if (archivo.type !== "application/pdf") return showError("El archivo debe ser de tipo PDF.")

                    return true
                }

                $(document).ready(() => {
                    configuraTabla(idTabla)
                    $("#cliente").keypress((e) => { if (e.which === 13) buscarConsultas() })
                    $("#buscar").click(buscarConsultas)
                    $("#consultaCDC").click(consultaCDC)
                })
            </script>
        HTML;

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Consulta CDC por cliente")));
        View::set('footer', $this->_contenedor->footer($js));
        View::render('cdc_consulta_admin');
    }

    public function ConsultaGlobal()
    {
        $regSuc = CDCDao::GetRegionSucursal();
        $regSuc = $regSuc['success'] ? json_encode($regSuc['datos']) : [];

        $js = <<<HTML
            <script>
                {$this->mensajes}
                {$this->configuraTabla}
                {$this->actualizaDatosTabla}
                {$this->consultaServidor}
                {$this->respuestaError}
                {$this->validaCaducidad}
                {$this->consultaCDC}

                const idTabla = "tablaPrincipal"
                const regSuc = JSON.parse('$regSuc')

                const getParametrosBusqueda = () => {
                    const p = {}

                    p.region = $("#region").val()
                    p.sucursal = $("#sucursal").val()
                    p.grupo = $("#grupo").val()

                    return p
                }

                const getParametrosConsulta = () => {
                    if (!$("#autorizacion")[0].files[0]) return showError("Debe seleccionar el archivo de autorización.")
                    if (!$("#identificacion")[0].files[0]) return showError("Debe seleccionar el archivo de identificación del cliente.")

                    const datos = new FormData();

                    datos.append("cliente", $("#noCliente").val())
                    datos.append("nombre1", $("#nombre1").val())
                    datos.append("nombre2", $("#nombre2").val())
                    datos.append("apellido1", $("#apellido1").val())
                    datos.append("apellido2", $("#apellido2").val())
                    datos.append("fecha", $("#fechaCDC").val())
                    datos.append("rfc", $("#rfc").val())
                    datos.append("calle", $("#calle").val())
                    datos.append("colonia", $("#colonia").val())
                    datos.append("municipio", $("#municipio").val())
                    datos.append("ciudad", $("#ciudad").val())
                    datos.append("estado", $("#estado").val())
                    datos.append("cp", $("#cp").val())

                    datos.append("autorizacion", $("#autorizacion")[0].files[0])
                    datos.append("identificacion", $("#identificacion")[0].files[0])

                    return datos
                }

                const getIcono = (icono, color, funcion) => {
                    return "<i class='glyphicon glyphicon-" + icono + "' style='cursor: pointer; font-size: 1.5em; color: " + color + "; ' onclick='" + funcion + "'></i>"
                }

                const buscarConsultas = () => {
                    const parametros = getParametrosBusqueda()
                    if (!parametros) return

                    consultaServidor("/CDC/GetConsultaGlobal", parametros, (res) => {
                        if (!res.success) return respuestaError(idTabla, res.mensaje)
                        if (res.datos.length === 0) return respuestaError(idTabla, "No se encontró información con los parámetros solicitados.")

                        const datos = res.datos.map((item, fila) => {
                            let autorizacion = ""
                            let identificacion = ""
                            let reporte = ""

                            const i = "<i class='glyphicon glyphicon-ICONO' style='cursor: pointer; font-size: 1.5em; color: COLOR; ' onclick='FUNCION'></i>"

                            if (item.AUTORIZACION == 0) autorizacion += getIcono("cloud-upload", "red", "cargaArchivo(" + item.CLIENTE + "," + item.FOLIO + ", \"autorizacion\")")
                            else autorizacion += getIcono("ok-circle", "green", "verPDF(" + item.CLIENTE + "," + item.FOLIO + ", \"autorizacion\")")

                            if (item.IDENTIFICACION == 0) identificacion += getIcono("cloud-upload", "red", "cargaArchivo(" + item.CLIENTE + "," + item.FOLIO + ", \"identificacion\")")
                            else identificacion += getIcono("ok-circle", "green", "verPDF(" + item.CLIENTE + "," + item.FOLIO + ", \"identificacion\")")


                            if (!validaCaducidad(item.CADUCIDAD)) reporte += getIcono("eye-open", "black", "verPDF(" + item.CLIENTE + "," + item.FOLIO + ", \"reporte\")")
                            else {    
                                reporte += getIcono("refresh", "blue" , "capturaDatosConsulta(" + fila + ")")
                                reporte += "<input type='hidden' value='" + JSON.stringify(item) + "' id='" + fila + "'/>"
                            }
                            
                            const documentos = "<div style='display: flex; justify-content: space-evenly;'>" + autorizacion + "|" + identificacion + "|" + reporte + "</div>"

                            return [
                                item.REGION,
                                item.SUCURSAL,
                                item.GRUPO,
                                item.CLIENTE,
                                item.NOMBRE,
                                item.FOLIO,
                                item.FECHA,
                                item.CADUCIDAD,
                                documentos
                            ]
                        })

                        actualizaDatosTabla(idTabla, datos)
                        $(".resultado").toggleClass("conDatos", true)
                    })
                }

                const actualizaModal = (datos = {}) => {
                    $("#noCliente").val(datos.CLIENTE ?? "")
                    $("#rfc").val(datos.RFC ?? "")
                    $("#fecha").val(datos.NACIMIENTO ?? "")
                    $("#fechaCDC").val(datos.NACIMIENTO_CDC ?? "")
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
                    $("#identificacion").val("")
                }

                const capturaDatosConsulta = (id) => {
                    const fila = JSON.parse($("#" + id).val())
                    actualizaModal(fila)
                    $("#modalCDC").modal("show")
                }

                const validaArchivo = (archivo) => {
                    if (!archivo) return showError("Debe seleccionar los archivos de soporte.")
                    if (archivo.size > 2097152) return showError("El archivo " + archivo.name + " supera los 2MB.")

                    return true
                }

                const actualizaSucursales = () => {
                    const region = $("#region").val()
                    $("#sucursal").empty()
                    $("#sucursal").append(new Option("Todas", ""))

                    regSuc.filter((reg) => reg.REGION === region || region === "")
                        .sort((a, b) => a.NOMBRE_SUCURSAL.localeCompare(b.NOMBRE_SUCURSAL))
                        .forEach((suc) => {
                            if (suc.REGION === region || region === "") $("#sucursal").append(new Option(suc.NOMBRE_SUCURSAL, suc.SUCURSAL))
                        })
                }

                const getConfirmacionCargaMasiva = () => {
                    const confirmacion = document.createElement("div")
                    const t1 = document.createElement("p")
                    const condiciones = document.createElement("ul")
                    const t2 = document.createElement("p")
                    const archivos = document.createElement("input")

                    t1.innerText = "Para realizar la carga masiva de documentos los archivos deben cumplir las siguientes condiciones:"

                    condiciones.style = "text-align: left; margin-left: 50px;"
                    condiciones.innerHTML += "<li>El archivo debe ser de tipo PDF.</li>"
                    condiciones.innerHTML += "<li>El archivo no debe superar los 2MB.</li>"
                    condiciones.innerHTML += "<li>El nombre del archivo debe tener el formato requerido.<br>(No. de cliente_identificador)</li>"
                    
                    t2.innerHTML += "Identificadores de documentos:<br>"
                    t2.innerHTML += "AUT: Autorización<br>"
                    t2.innerHTML += "ID: Identificación<br>"
                    t2.innerHTML += "<b>Ejemplo de nombre de archivo: 123456_AUT</b>"

                    archivos.type = "file"
                    archivos.accept = ".pdf"
                    archivos.multiple = true
                    archivos.style = "width: 100%;"

                    confirmacion.id = "confirmacion"
                    confirmacion.appendChild(t1)
                    confirmacion.appendChild(condiciones)
                    confirmacion.appendChild(t2)
                    confirmacion.appendChild(archivos)

                    return confirmacion
                }
                
                const getConfirmacionCargaArchivo = () => {
                    const confirmacion = document.createElement("div")
                    const t1 = document.createElement("p")
                    const condiciones = document.createElement("ul")
                    const archivos = document.createElement("input")

                    t1.innerText = "Para realizar la carga de documentos los archivos deben cumplir las siguientes condiciones:"

                    condiciones.style = "text-align: left; margin-left: 50px;"
                    condiciones.innerHTML += "<li>El archivo debe ser de tipo PDF.</li>"
                    condiciones.innerHTML += "<li>El archivo no debe superar los 2MB.</li>"
                    
                    archivos.type = "file"
                    archivos.accept = ".pdf"
                    archivos.style = "width: 100%;"

                    confirmacion.id = "confirmacion"
                    confirmacion.appendChild(t1)
                    confirmacion.appendChild(condiciones)
                    confirmacion.appendChild(archivos)

                    return confirmacion
                }

                const validaCargaMasiva = (archivos) => {
                    let resultado = false
                    const retornaError = (mensaje) => {
                        showError(mensaje)
                        resultado = false
                        return resultado
                    }

                    if (archivos.length === 0) retornaError("Debe seleccionar al menos un archivo.")

                    Array.from(archivos).some((archivo) => {
                        if (archivo.size > 2097152) return !retornaError("El archivo " + archivo.name + " supera los 2MB.")

                        const [cliente, id] = archivo.name.split(".")[0].split("_")
                        if (cliente.length !== 6 || isNaN(cliente)) return !retornaError("El número de cliente en el archivo " + archivo.name + " no es válido.")
                        if (id.toUpperCase() !== "AUT" && id.toUpperCase() !== "ID") return !retornaError("El identificador de documento en el archivo " + archivo.name + " no es válido.")
                        resultado = true
                    })

                    return resultado
                }

                const cargaMasivaPDF = () => {
                    confirmarMovimiento("Circulo de crédito", null, getConfirmacionCargaMasiva())
                    .then((continuar) => {
                        if (!continuar) return

                        const archivos = $("#confirmacion input[type='file']")[0].files
                        if (archivos.length === 0) return showError("Debe seleccionar al menos un archivo.")
                        if (!validaCargaMasiva(archivos)) return

                        confirmarMovimiento("Circulo de crédito", "Se guardaran unicamente los archivos faltantes en la consulta mas reciente de cada cliente.\\n¿Seguro desea continuar?")
                        .then((continuar) => {
                            if (!continuar) return

                            const carga = new FormData()
                            Array.from(archivos).forEach((archivo) => carga.append("archivos[]", archivo))
                            
                            consultaServidor("/CDC/CargaDocMasiva", carga, (res) => {
                                if (!res.success) return showError(res.mensaje)
                                showSuccess(res.mensaje).then(() => buscarConsultas())
                            }, "POST", "JSON", false, false)
                        })
                    })
                }

                const cargaArchivo = (cliente, folio, documento) => {
                    confirmarMovimiento("Circulo de crédito", null, getConfirmacionCargaArchivo())
                    .then((continuar) => {
                        if (!continuar) return

                        const archivos = $("#confirmacion input[type='file']")[0].files
                        if (archivos.length === 0) return showError("Debe seleccionar un archivo.")
                        if (!validaArchivo(archivos)) return
                        
                        confirmarMovimiento("Circulo de crédito", "Se guardara este archivo como " + documento + ".\\n¿continuar?")
                        .then((continuar) => {
                            if (!continuar) return
                            
                            const datos = new FormData()
                            datos.append("cliente", cliente)
                            datos.append("folio", folio)
                            datos.append(documento, archivos[0])
                            datos.append("unico", "true")
                            
                            consultaServidor("/CDC/SubeDocumentosCDC", datos, (res) => {
                                if (!res.success) return showError(res.mensaje)
                                showSuccess(res.mensaje).then(() => buscarConsultas())
                            }, "POST", "JSON", false, false)
                        })
                    })
                }

                $(document).ready(() => {
                    configuraTabla(idTabla)
                    $("#region").append(new Option("Todas", ""))
                    regSuc.forEach((reg) => {
                        if ($("#region option[value='" + reg.REGION + "']").length === 0)
                            $("#region").append(new Option(reg.NOMBRE_REGION, reg.REGION))
                    })

                    $("#region").change(actualizaSucursales)
                    $("#cliente").keypress((e) => { if (e.which === 13) buscarConsultas() })
                    $("#buscar").click(buscarConsultas)
                    $("#cargaMasiva").click(cargaMasivaPDF)
                    $("#consultaCDC").click(consultaCDC)

                    actualizaSucursales()
                    buscarConsultas()
                })
            </script>
        HTML;

        View::set('header', $this->_contenedor->header(self::GetExtraHeader("Consulta CDC global")));
        View::set('footer', $this->_contenedor->footer($js));
        View::render('cdc_consulta_global');
    }

    public function GetConsultaCliente()
    {
        $resultado = CDCDao::GetConsultaCliente($_POST);
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

    public function GetConsultaGlobal()
    {
        self::RespondeJSON(CDCDao::GetConsultaGlobal($_POST));
    }

    public function ConsultaCDC($cuerpo, $ep, $config = [])
    {
        $cnfg = [
            'CERT_CDC' => $this->cnfg['CERT_CDC'],
            'CERT_CULTIVA' => $this->cnfg['CERT_CULTIVA'],
            'PASS_CERT' => $this->cnfg['PASS_CERT'],
            'URL' => $config['URL'] ?? $this->cnfg['URL_CDC'],
            'API_KEY' => $config['API_KEY'] ?? $this->cnfg['API_KEY'],
            'USER_CDC' => $this->cnfg['USER_CDC'],
            'PASS_CDC' => $this->cnfg['PASS_CDC'],
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
                    'x-signature: ' . $firma,
                    'x-api-key: ' . $cnfg['API_KEY'],
                    'username: ' . $cnfg['USER_CDC'],
                    'password: ' . $cnfg['PASS_CDC']
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
            $codigo = curl_getinfo($ci, CURLINFO_HTTP_CODE);
            if ($codigo === 404) return self::GetRespuesta(false, 'Circulo de crédito no cuenta con información con los datos proporcionados del cliente.', null, json_decode($resultado, true));
            if ($codigo !== 200) return self::GetRespuesta(false, 'Error al consultar los servicios de CDC.', $cnfg, json_decode($resultado, true));
            if ($cnfg['valida'] && !$firmas->isDigitalSigantureValid($resultado, $hdrs['x-signature'])) return self::GetRespuesta(false, 'Error en la respuesta de CDC.', null, 'La firma de respuesta no es válida.');

            $resultado = str_replace(["\r", "\n"], "", $resultado);
            $reporte = self::Reporte_PDF_CDC($resultado);
            $resJSON = json_decode($resultado, true) ?? $resultado;
            $res = [
                'json' => $resJSON
            ];

            if ($reporte['success']) $res['reporte'] = $reporte['datos'];
            return self::GetRespuesta(true, 'Consulta exitosa', $res);
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
            'folio' => $consultaCDC['json']['folioConsulta'],
            'resultado' => json_encode($consultaCDC['json']),
            'usuario' => $_SESSION['usuario'] ?? null,
        ];

        if ($infoCliente['autorizacion']) $guardar['autorizacion'] = $infoCliente['autorizacion'];
        if ($infoCliente['identificacion']) $guardar['identificacion'] = $infoCliente['identificacion'];
        if ($consultaCDC['reporte']) $guardar['reporte'] = $consultaCDC['reporte'];

        return CDCDao::SetResultadoCDC($guardar);
    }

    public function GetDocumento()
    {
        $res = null;
        $consulta = CDCDao::GetDocumento($_GET);

        if (!$consulta['success']) {
            echo self::ErrorPDF($consulta['mensaje']);
            return;
        }

        $pdf = $consulta['datos']['PDF'];
        $archivo = is_resource($pdf) ? stream_get_contents($pdf) : $pdf;
        $nombre = strtoupper($_GET['documento']) . '_' . $_GET['cliente'] . '_' . $_GET['folio'] . '.pdf';

        if ($_GET['documento'] === 'reporte' && empty($archivo)) {
            $reporte = self::Reporte_PDF_CDC($consulta['datos']['RESULTADO']);

            if ($reporte['success']) {
                $cliente = [
                    'cliente' => $_GET['cliente'],
                    'folio' => $_GET['folio'],
                    'reporte' => $reporte['datos']
                ];

                $res = CDCDao::SetDocumentosCDC($cliente);
                if ($res['success']) {
                    $consulta = CDCDao::GetDocumento($_GET);
                    $pdf = $consulta['datos']['PDF'];
                    $archivo = is_resource($pdf) ? stream_get_contents($pdf) : $pdf;
                }
            }
        }

        if (empty($archivo)) {
            echo self::ErrorPDF('El documento solicitado no ha sido registrado aun.');
            return;
        }

        header("Content-Type: application/pdf");
        header('Content-Disposition: inline; filename="' . $nombre);
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
                if (!isset($_FILES['identificacion']) && $_FILES['identificacion']['error'] !== UPLOAD_ERR_OK) return self::Responde(false, 'No se incluyó el archivo de identificación del cliente.');

                $_POST['autorizacion'] = fopen($_FILES['autorizacion']['tmp_name'], 'rb');
                $_POST['identificacion'] = fopen($_FILES['identificacion']['tmp_name'], 'rb');

                self::RespondeJSON(self::SetResultadoCDC($_POST, $cdc['datos']));
            } catch (\Exception $e) {
                return self::Responde(false, 'Error al guardar los datos en la base.', null, $e->getMessage());
            } finally {
                if (isset($_POST['autorizacion'])) fclose($_POST['autorizacion']);
                if (isset($_POST['identificacion'])) fclose($_POST['identificacion']);
                if (isset($cdc['datos']['reporte'])) fclose($cdc['datos']['reporte']);
            }
        }
    }

    public function GeneraConsultaCDC_API()
    {
        $resultado = CDCDao::GetConsultaCliente($_GET);

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
            'fecha' => $ultimaConsulta['NACIMIENTO_CDC'],
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
            if (!isset($_FILES['autorizacion']) && !$_POST['unico'] && $_FILES['autorizacion']['error'] !== UPLOAD_ERR_OK) return self::Responde(false, 'No se incluyó el archivo de autorización.');
            if (!isset($_FILES['identificacion']) && !$_POST['unico']  && $_FILES['identificacion']['error'] !== UPLOAD_ERR_OK) return self::Responde(false, 'No se incluyó el archivo de identificación del cliente.');

            if (isset($_FILES['autorizacion'])) $_POST['autorizacion'] = fopen($_FILES['autorizacion']['tmp_name'], 'rb');
            if (isset($_FILES['identificacion'])) $_POST['identificacion'] = fopen($_FILES['identificacion']['tmp_name'], 'rb');
            self::RespondeJSON(CDCDao::SetDocumentosCDC($_POST));
        } catch (\Exception $e) {
            return self::Responde(false, 'Error al guardar los documentos en la base.', null, $e->getMessage());
        } finally {
            if (isset($_POST['autorizacion'])) fclose($_POST['autorizacion']);
            if (isset($_POST['identificacion'])) fclose($_POST['identificacion']);
        }
    }

    public function SubeDocPendientes()
    {
        $resultados = [];
        foreach ($_POST['clientes'] as $indice => $cliente) {
            try {
                $validacion = $_FILES['clientes']['name'][$indice];
                $archivos = $_FILES['clientes']['tmp_name'][$indice];
                if ($validacion['autorizacion']) $cliente['autorizacion'] = fopen($archivos['autorizacion'], 'rb');
                if ($validacion['identificacion']) $cliente['identificacion'] = fopen($archivos['identificacion'], 'rb');

                $resultados[] = CDCDao::SetDocumentosCDC($cliente);
            } catch (\Exception $e) {
                $resultados[] = self::Responde(false, 'Error al procesar los documentos del cliente ' . $cliente['cliente'] . '.', null, $e->getMessage());
            } finally {
                if ($cliente['autorizacion']) fclose($cliente['autorizacion']);
                if ($cliente['identificacion']) fclose($cliente['identificacion']);
            }
        }

        return self::Responde(true, 'Documentos procesados correctamente.', $resultados);
    }

    public function CargaDocMasiva()
    {
        if (!isset($_FILES['archivos'])) return self::Responde(false, 'No se incluyó ningún archivo para la carga masiva.');
        $guardar = [];
        $errores = [];

        foreach ($_FILES['archivos']['tmp_name'] as $key => $archivo) {
            if ($_FILES['archivos']['error'][$key] !== UPLOAD_ERR_OK) {
                $errores[$key] = 'Error al cargar el archivo ' . $_FILES['archivos']['name'][$key] . '.';
                continue;
            }

            $nombre = str_replace('.pdf', '', $_FILES['archivos']['name'][$key]);
            [$cliente, $documento] = explode('_', $nombre);

            if (strlen($cliente) !== 6 || !is_numeric($cliente)) {
                $errores[$key] = 'El número de cliente en el archivo ' . $nombre . ' no es válido.';
                continue;
            }

            if ($documento !== 'AUT' && $documento !== 'ID') {
                $errores[$key] = 'El identificador de documento en el archivo ' . $nombre . ' no es válido.';
                continue;
            }

            $guardar[$cliente][$documento] = fopen($archivo, 'rb');
        }

        if (count($guardar) === 0) return self::Responde(false, 'No se logro procesar ningún documento.', null, $errores);

        $qrys = [];
        $prms = [];
        foreach ($guardar as $cliente => $documentos) {
            $d = [];
            $d['cliente'] = $cliente;
            if ($documentos['AUT']) $d['autorizacion'] = $documentos['AUT'];
            if ($documentos['ID']) $d['identificacion'] = $documentos['ID'];

            [$qrys[], $prms[]] = CDCDao::GetQrysCargaDocMasiva($d);
        }

        $resultado = CDCDao::CargaDocMasiva($qrys, $prms);
        return self::Responde(true, 'Documentos procesados correctamente.', $resultado, $errores);
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
            'primerNombre' => $datos['nombre1'],
            'apellidoPaterno' => $datos['apellido1'],
            'apellidoMaterno' => $datos['apellido2'],
            'fechaNacimiento' => $datos['fecha'],
            'RFC' => $datos['rfc'],
            'nacionalidad' => $datos['nacionalidad'] ?? 'MX',
            'domicilio' => [
                'direccion' => $datos['calle'],
                'coloniaPoblacion' => $datos['colonia'],
                'delegacionMunicipio' => $datos['municipio'],
                'ciudad' => $datos['ciudad'],
                'estado' => $datos['estado'],
                'CP' => $datos['cp']
            ]
        ];

        if ($datos['nombre2'] !== null && $datos['nombre2'] !== '') $campos['segundoNombre'] = $datos['nombre2'];

        $campos = json_encode($campos);

        // Inicio: Configuración solo para pruebas
        // $campos = json_decode(file_get_contents(__DIR__ . '/../config/datosDemo_CDC.json'), true);
        // $campos = json_encode($campos[array_rand($campos)]);
        // $TST_cnfg = $this->GetTstCnfg();
        // return $this->ConsultaCDC($campos, $ep, $TST_cnfg);
        // Fin: Configuración solo para pruebas

        return $this->ConsultaCDC($campos, $ep);
    }

    private function GetTstCnfg()
    {
        return [
            'URL' => $this->cnfg['URL_CDC_TST'],
            'API_KEY' => $this->cnfg['API_KEY_TST'],
            'valida' => false
        ];
    }

    private function Reporte_PDF_CDC($reporte)
    {
        $archivo = tempnam(sys_get_temp_dir(), 'CDC_');
        file_put_contents($archivo, $reporte);
        $jar = __DIR__ . '/../../libs/CDC/Reporte_PDF_CDC.jar';
        $fecha = date('Y-m-d');

        $cmd = "java -jar $jar $archivo $fecha";

        $errores = null;
        $salidas = [
            '1' => ['pipe', 'w'],
            '2' => ['pipe', 'w']
        ];

        $proceso = proc_open($cmd, $salidas, $pipes);
        $resultado = stream_get_contents($pipes[1]);
        $errores = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proceso);

        if (file_exists($archivo)) unlink($archivo);

        if (strpos($errores, 'Exception ') !== false || strpos($errores, 'Error ') !== false || strpos($resultado, 'Unrecognized field') !== false) {
            // echo "<pre>Comando:\n$cmd\n\n";
            // echo "Resultado:\n$resultado\n\n";
            // echo "Errores:\n$errores\n\n</pre>";
            // exit;

            return self::GetRespuesta(false, 'Error al generar el reporte en PDF.', null, $errores);
        }

        try {
            $pdf = base64_decode($resultado);
            $stream = fopen('php://memory', 'rb+');
            fwrite($stream, $pdf);
            rewind($stream);
            if ($stream === false) return self::GetRespuesta(false, 'Error al generar el reporte en PDF.', null, 'No se logro generar el reporte en PDF.');
        } catch (\Exception $e) {
            return self::GetRespuesta(false, 'Error al generar el reporte en PDF.', null, $e->getMessage());
        }

        return self::GetRespuesta(true, 'Reporte generado correctamente.', $stream);
    }
}
