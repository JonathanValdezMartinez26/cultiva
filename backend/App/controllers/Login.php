<?php

namespace App\controllers;

defined("APPPATH") or die("Access denied");

use \Core\View;
use \Core\MasterDom;
use \App\models\Login as LoginDao;

class Login
{
    function __construct()
    {
    }

    public function index()
    {
        $extraHeader = <<<html
        <link rel="stylesheet" type="text/css" href="/css/bootstrap/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="/css/contenido/custom.min.css">
        <link rel="stylesheet" type="text/css" href="/css/validate/screen.css">
        html;

        $extraFooter = <<<html
        <script type="text/javascript" src="/js/jquery.min.js"></script>
        <script type="text/javascript" src="/js/validate/jquery.validate.js"></script>
        <script>
            const enviar_formulario = (e) => {
                if (event.keyCode == 13) $("#btnEntrar").click()
            }
            
            const mayusculas = (e) => {
                e.target.value = e.target.value.toUpperCase()
            }
            
            $(document).ready(function () {
                document.getElementById("usuario").focus()

                $.validator.addMethod(
                    "checkUserName",
                    function (value, element) {
                        var response = false
                        $.ajax({
                            type: "POST",
                            async: false,
                            url: "/Login/isUserValidate",
                            data: { usuario: $("#usuario").val() },
                            success: function (data) {
                                response = data == "true" ? true : false

                                $("#availability").html(
                                    '<span class="' + (response ? 'text-success glyphicon glyphicon-ok' : 'text-danger glyphicon glyphicon-remove') + '"></span>'
                                )
                                $("#btnEntrar").attr("disabled", !response)
                            }
                        })
                        return response
                    },
                    "El usuario no es correcto, o no tiene acceso al sistema, verifique."
                )

                $("#login").validate({
                    rules: {
                        usuario: {
                            required: true,
                            checkUserName: true
                        },
                        password: {
                            required: true
                        }
                    },
                    messages: {
                        usuario: {
                            required: "Este campo es requerido"
                        },
                        password: {
                            required: "Este campo es requerido"
                        }
                    }
                })

                $("#btnEntrar").click(function () {
                    $.ajax({
                        type: "POST",
                        url: "/Login/verificarUsuario",
                        data: $("#login").serialize(),
                        success: function (response) {
                            if (response != "") {
                                var usuario = jQuery.parseJSON(response)
                                if (usuario.nombre != "") {
                                    $("#login").append(
                                        '<input type="hidden" name="autentication" id="autentication" value="OK"/>'
                                    )
                                    $("#login").append(
                                        '<input type="hidden" name="nombre" id="nombre" value="' +
                                            usuario.nombre +
                                            '"/>'
                                    )
                                    $("#login").submit()
                                } else {
                                    swal(
                                        "Error de autenticación ",
                                        "El usuario o contraseña son incorrectos, consulte al administrador",
                                        "error"
                                    )
                                }
                            } else {
                                swal(
                                    "Error de autenticación ",
                                    "El usuario o contraseña son incorrectos, consulte al administrador",
                                    "error"
                                )
                            }
                        }
                    })
                })
            })
        </script>
        html;
        View::set('header', $extraHeader);
        View::set('footer', $extraFooter);
        View::render("login");
    }

    public function isUserValidate()
    {
        echo (count(LoginDao::getUser($_POST['usuario'])) >= 1) ? 'true' : 'false';
    }

    public function verificarUsuario()
    {
        $usuario = new \stdClass();
        $usuario->_usuario = MasterDom::getData("usuario");
        $usuario->_password = MasterDom::getData("password");
        $user = LoginDao::getById($usuario);
        
        if (count($user) >= 1) {
            $user['NOMBRE'] = mb_convert_encoding($user['NOMBRE'], 'UTF-8');
            echo json_encode($user);
        }
    }

    public function crearSession()
    {
        $usuario = new \stdClass();
        $usuario->_usuario = MasterDom::getData("usuario");
        $usuario->_password = MasterDom::getData("password");
        $user = LoginDao::getById($usuario);

        session_start();
        $_SESSION['usuario'] = $user[0]['CODIGO'];
        $_SESSION['nombre'] = $user[0]['NOMBRE'];
        $_SESSION['puesto'] = $user[0]['PUESTO'];
        $_SESSION['cdgco'] = $user[0]['CDGCO'];
        $_SESSION['perfil'] = $user[0]['PERFIL'];

        header("location: /Principal/");
    }

    public function cerrarSession()
    {
        unset($_SESSION);
        session_unset();
        session_destroy();
        header("Location: /Login/");
    }
}
