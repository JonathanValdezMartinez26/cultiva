<?php

use App\components\MuestraPDF;

?>

<?= $header ?>

<div class="right_col">
    <div class="panel">
        <div class="panel-header" style="padding: 10px;">
            <div class="x_title">
                <label style="font-size: large;">Reporte de circulo de crédito</label>
                <div class="clearfix"></div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header" style="margin: 20px 0;">
                            <span class="card-title">Ingrese un numero de cliente</span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="region">Region</label>
                                        <select class="form-control" id="region">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="sucursal">Sucursal</label>
                                        <select class="form-control" id="sucursal">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="grupo">Grupo</label>
                                        <input type="text" class="form-control" id="grupo" />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group" style="min-height: 68px; display: flex; align-items: center; justify-content: space-between;">
                                        <button type="button" class="btn btn-primary" id="buscar"><i class="fa fa-search">&nbsp;</i>Buscar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="panel-body resultado">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped table-bordered table-hover" id="tablaPrincipal">
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th><i class="glyphicon glyphicon-eye-open" style="cursor: pointer; font-size: 1.5em; color: black;"></i></th>
                                <th><i class="glyphicon glyphicon-cloud-upload" style="cursor: pointer; font-size: 1.5em; color: red;"></i></th>
                                <th><i class="glyphicon glyphicon-refresh" style="cursor: pointer; font-size: 1.5em; color: blue;"></i></th>
                                <th style="width: 60%;">Clientes</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCDC" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <center>
                    <h2 class="modal-title" id="modalCDCLabel">Datos del cliente</h2>
                </center>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <center>
                                <h4>Identificación</h4>
                            </center>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="noCliente">Cliente</label>
                                <input type="text" class="form-control" id="noCliente" readonly>
                                <input type="text" id="folio" hidden>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="rfc">RFC</label>
                                <input type="text" class="form-control" id="rfc" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fecha">Fecha de nacimiento</label>
                                <input type="text" class="form-control" id="fecha" readonly>
                                <input type="hidden" id="fechaCDC">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nombre1">Primer nombre</label>
                                <input type="text" class="form-control" id="nombre1" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nombre2">Segundo nombre</label>
                                <input type="text" class="form-control" id="nombre2" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="apellido1">Primer apellido</label>
                                <input type="text" class="form-control" id="apellido1" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="apellido2">Segundo apellido</label>
                                <input type="text" class="form-control" id="apellido2" readonly>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <center>
                                <h4>Domicilio</h4>
                            </center>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="calle">Calle y numero</label>
                                <input type="text" class="form-control" id="calle" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="colonia">Colonia</label>
                                <input type="text" class="form-control" id="colonia" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="municipio">Municipio</label>
                                <input type="text" class="form-control" id="municipio" readonly>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="ciudad">Ciudad</label>
                                <input type="text" class="form-control" id="ciudad" readonly>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="estadoNombre">Estado</label>
                                <input type="text" class="form-control" id="estadoNombre" readonly>
                                <input type="text" id="estado" hidden>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="cp">CP</label>
                                <input type="text" class="form-control" id="cp" readonly>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <center>
                                <h4>Documentación</h4>
                            </center>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="autorizacion">Documento de autorización</label>
                                <input type="file" class="form-control" id="autorizacion" accept="application/pdf">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="identificacion">Documento de identificación</label>
                                <input type="file" class="form-control" id="identificacion" accept="application/pdf">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <p style="text-align: center;">Valide que la información de los campos coincida con la documentación, si encuentra inconsistencias reportelas al area correspondiente.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="consultaCDC">Subir documentos</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDocPendientes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <center>
                    <h2 class="modal-title" id="modalCDCLabel">Clientes con documentación pendiente</h2>
                </center>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <table atyle="width: 100%;">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Folio</th>
                                <th style='width: 30%;'>Autorización</th>
                                <th style='width: 30%;'>Identificación</th>
                            </tr>
                        </thead>
                        <tbody id="listado">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="guardarPendientes">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?= MuestraPDF::Mostrar() ?>

<?= $footer ?>