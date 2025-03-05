<?php

namespace App\components;

class MuestraPDF
{
    private static function getHTML()
    {
        return <<<HTML
            <dialog id="muestraPDF">
                <div id="contenido">
                    <div id="PDF">
                        <img id="cargando" src="/img/wait.gif" alt="Cargando..." style="width: 100px; height: 100px; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 101;">
                    </div>
                    <div id="botones">
                        <button type="button" class="btn btn-primary" id="cerrarPDF" onclick=cerrarPDF()>Cerrar</button>
                    </div>
                </div>
            </dialog>
        HTML;
    }

    private static function getScript()
    {
        return <<<HTML
            <script>
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
            </script>
        HTML;
    }

    private static function getEstilo()
    {
        return <<<HTML
            <style>
                #muestraPDF {
                    transition: opacity 0.3s ease-in-out, display 0.3s ease-in-out allow-discrete;
                    opacity: 0;
                    width: 100vw;
                    height: 100vh;
                    margin: 0;
                    padding: 0;
                    position: fixed;
                    left: 0;
                    top: 0;
                    z-index: 100;
                    background-color: rgba(0, 0, 0, 0.5);
                    border: none;
                    align-items: center;
                    justify-content: center;
            
                    #contenido {
                        background: white;
                        padding: 20px;
                        border-radius: 10px;
                        width: 50%;
                        height: 75%;
                        text-align: center;
                        scale: 0;
                        transition: scale 0.3s ease-in-out;
            
                        #PDF {
                            width: 100%;
                            height: 95%;
                        }
            
                        #botones {
                            display: flex;
                            justify-content: center;
                            margin-top: 10px;
            
                            button {
                                margin: 0;
                            }
                        }
                    }
            
                    &[open] {
                        display: flex;
                        opacity: 1;
                        transition: opacity 0.3s ease-in-out;
            
                        @starting-style {
                            opacity: 0;
                        }
            
                        #contenido {
                            scale: 1;
                            transition: scale 0.3s ease-in-out;
            
                            @starting-style {
                                scale: 0;
                            }
                        }
                    }
                }
            </style>
        HTML;
    }

    public static function Mostrar()
    {
        $contenedor = self::getHTML();
        $contenedor .= self::getScript();
        $contenedor .= self::getEstilo();

        return $contenedor;
    }
}
