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
                                        <label for="cliente">No. de cliente</label>
                                        <input type="text" class="form-control" id="cliente" placeholder="000000" maxlength="6">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group" style="min-height: 68px; display: flex; align-items: center; justify-content: space-between;">
                                        <button type="button" class="btn btn-primary" id="buscar"><i class="fa fa-search">&nbsp;</i> Buscar</button>
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
                                <th rowspan="2">Cliente</th>
                                <th rowspan="2">Nombre</th>
                                <th rowspan="2">Folio consulta</th>
                                <th rowspan="2">Fecha consulta</th>
                                <th rowspan="2">Caducidad</th>
                                <th style="text-align: center;">Documentos</th>
                            </tr>
                            <tr>
                                <th style="display:flex; justify-content: space-between;">
                                    <div>Autorización</div>|
                                    <div>INE</div>|
                                    <div>Reporte</div>
                                </th>
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
                    <h2 class="modal-title" id="modalCDCLabel">Datos de consulta</h2>
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
                <button type="button" class="btn btn-primary" id="consultaCDC">Consultar</button>
            </div>
        </div>
    </div>
</div>

<?= MuestraPDF::Mostrar() ?>

<?= $footer ?>