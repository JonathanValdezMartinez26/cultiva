<?php

namespace App\controllers;

defined("APPPATH") or die("Access denied");

use \Core\Controller;

require_once dirname(__DIR__) . '/../public/librerias/mpdf/mpdf.php';
require_once dirname(__DIR__) . '/../public/librerias/phpexcel/Classes/PHPExcel.php';

class Contenedor extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function header($extra = '')
    {
        $usuario = $this->__usuario;
        $nombre = $this->__nombre;
        $perfil = $this->__perfil;

        $header = <<<html
        <!DOCTYPE html>
        <html lang="es">
            <head>
                <meta http-equiv="Expires" content="0">
                <meta http-equiv="Last-Modified" content="0">
                <meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
                <meta http-equiv="Pragma" content="no-cache">
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <meta charset="utf-8">
                
                <link rel="shortcut icon" href="/img/logo_ico.png">
                <link rel="stylesheet" type="text/css" href="/css/nprogress.css">
                <link rel="stylesheet" type="text/css" href="/css/loader.css">
                <link rel="stylesheet" type="text/css" href="/css/tabla/sb-admin-2.css">
                <link rel="stylesheet" type="text/css" href="/css/bootstrap/datatables.bootstrap.css">
                <link rel="stylesheet" type="text/css" href="/css/bootstrap/bootstrap.css">
                <link rel="stylesheet" type="text/css" href="/css/bootstrap/bootstrap-switch.css">
                <link rel="stylesheet" type="text/css" href="/css/validate/screen.css">
                <link rel="stylesheet" type="text/css" href="/css/bootstrap/bootstrap.min.css">
                <link rel="stylesheet" type="text/css" href="/css/font-awesome.min.css">
                <link rel="stylesheet" type="text/css" href="/css/menu/menu5custom.min.css">
                <link rel="stylesheet" type="text/css" href="/css/green.css">
                <link rel="stylesheet" type="text/css" href="/css/custom.min.css">
                $extra 
            </head>
        html;

        $menu = <<<html
        <body class="nav-md">
            <div class="container body" >
                <div class="main_container" style="background: #ffffff">
                    <div class="col-md-3 left_col">
                        <div class="left_col scroll-view">
                            <div class="navbar nav_title">
                                <a href="/Principal/" class="site_title" style="display: flex; align-items: center; justify-content: center; padding: 0; margin: 0;">
                                    <i class="fa fa-home" style="display: none;" id="ico_home"></i>
                                    <img src="/img/logo_nombre.png" alt="Login" width="210px" id="img_home">
                                </a>
                            </div>
                            <div class="clearfix"></div>
                            <div class="profile clearfix">
                                <div class="profile_pic">
                                    <img src="/img/profile_default.jpg" alt="..." class="img-circle profile_img">
                                </div>
                                <div class="profile_info">
                                    <span><b>USUARIO:</b> {$usuario}</span>
                                    <br>
                                    <span><b>PERFIL:</b> <span class="fa fa-key"></span> {$perfil}</span>
                                </div>
                            </div>
                            <hr>
                            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                                <div class="menu_section">
                                    <h3>GENERAL </h3>     
        html;

        if ($this->__perfil == 'ADMIN' || $this->__usuario == 'OFCLD' || $this->__usuario == 'PLDCO') {
            $menu .= <<<html
            <ul class="nav side-menu">
                <li><a><i class="glyphicon glyphicon glyphicon-th-list"> </i>&nbsp; Operaciones <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                        <li><a href="/Operaciones/ReportePLDDesembolsos/">PLD Reporte Desembolsos</a></li>
                        <li><a href="/Operaciones/ReportePLDPagos/">PLD Reporte Pagos</a></li>
                        <li><a href="/Operaciones/ReportePLDPagosNacimiento/">PLD R. Pagos Edad</a></li>
                        <li><a href="/Operaciones/IdentificacionClientes/">Identificación (Clientes)</a></li>
                        <li><a href="/Operaciones/CuentasRelacionadas/">Cuentas Relacionadas</a></li>
                        <li><a href="/Operaciones/PerfilTransaccional/">Perfil Transaccional</a></li>
                    </ul>
                </li>
            </ul>
            <ul class="nav side-menu">
                <li><a><i class="glyphicon glyphicon glyphicon glyphicon-globe"></i>&nbsp;Api Condusef<span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                        <li><a href="/ApiCondusef/AddRedeco/">Registrar Quejas REDECO</a></li>
                        <li><a href="/ApiCondusef/AddReune/">Registrar Quejas REUNE</a></li>
                    </ul>
                </li>
            </ul>
            html;
        }

        $menu .= <<<html
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="top_nav ">
                        <div class="nav_menu">
                            <nav>
                                <div class="nav toggle">
                                    <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                                </div>
                                <ul class="nav navbar-nav navbar-right">
                                    <li class="">
                                        <a href="" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                            <span class=" fa fa-user"></span> {$nombre}
                                            <span class=" fa fa-angle-down"></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-usermenu pull-right">
                                            <li><a href="/Login/cerrarSession">
                                                <i class="fa fa-sign-out pull-right"></i>Cerrar Sesión</a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
        html;
        return $header . $menu;
    }

    public function footer($extra = '')
    {
        $footer = <<<html
                </div>
                <script src="/js/jquery.min.js"></script>
                <script src="/js/moment/moment.min.js"></script>
                <script src="/js/bootstrap.min.js"></script>
                <script src="/js/bootstrap/bootstrap-switch.js"></script>
                <script src="/js/nprogress.js"></script>
                <script src="/js/custom.min.js"></script>
                <script src="/js/validate/jquery.validate.js"></script>
                <script src="/js/tabla/jquery.dataTables.min.js"></script>
                <script src="/js/tabla/dataTables.bootstrap.min.js"></script>
                <script src="/js/tabla/jquery.tablesorter.js"></script>
                <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js" ></script>
                <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js" ></script>
                <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js" ></script>
                <script src="//cdn.datatables.net/buttons/1.4.2/js/buttons.html5.min.js" ></script>
                <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
                <script src="https://cdn.datatables.net/buttons/1.4.2/js/dataTables.buttons.min.js" ></script>
                $extra
            </body>
        </html>
        html;

        return $footer;
    }
}
