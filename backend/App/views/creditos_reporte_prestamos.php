<?= $header; ?>

<div class="right_col">
    <div class="panel">
        <div class="panel-header" style="padding: 10px;">
            <div class="x_title">
                <label style="font-size: large;">Reporte de Prestamos</label>
                <div class="clearfix"></div>
            </div>
            <div class="card">
                <div class="card-header" style="margin: 20px 0;">
                    <span class="card-title">Seleccione los parámetros para el reporte.</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Desde</label>
                                <input type="date" class="form-control" id="fechaI" value="<?= date('Y-m-d'); ?>" max="<?= date('Y-m-d', strtotime("+ 7 day")); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Hasta</label>
                                <input type="date" class="form-control" id="fechaF" value="<?= date('Y-m-d'); ?>" max="<?= date('Y-m-d', strtotime("+ 7 day")); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Región</label>
                                <select class="form-control" id="region">
                                    <?= $regiones ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Sucursal</label>
                                <select class="form-control" id="sucursal">
                                    <?= $sucursales ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Situación solicitud</label>
                                <select class="form-control" id="sitSolicitud">
                                    <option value="">Todas</option>
                                    <option value="A">Autorizado</option>
                                    <option value="S">Solicitado</option>
                                    <option value="R">Rechazado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Situación préstamo</label>
                                <select class="form-control" id="sitPrestamo">
                                    <option value="">Todas</option>
                                    <option value="T">Aut. Tesorería</option>
                                    <option value="A">Autorizado</option>
                                    <option value="E">Entregado</option>
                                    <option value="L">Liquidado</option>
                                    <option value="D">Devuelto</option>
                                    <option value="R">Rechazado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2" style="min-height: 68px; display: flex; justify-content: space-between; align-items: center;">
                            <button type="button" class="btn btn-primary" id="generar"><i class="fa fa-search">&nbsp;</i>Generar</button>
                            <button type="button" class="btn btn-success" id="exportar"><i class="fa fa-file-excel-o">&nbsp;</i>Exportar a Excel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="panel-body resultado">
            <div class="row">
                <table class="table table-striped table-bordered table-hover" id="prestamos">
                    <thead>
                        <tr>
                            <th>Crédito</th>
                            <th>Ciclo</th>
                            <th>Nombre grupo</th>
                            <th>Mujeres</th>
                            <th>Hombres</th>
                            <th>Región</th>
                            <th>Sucursal</th>
                            <th>Fecha de solicitud</th>
                            <th>Fecha de autorización</th>
                            <th>Autorizo</th>
                            <th>Duración</th>
                            <th>Tasa</th>
                            <th>Fecha de inicio</th>
                            <th>Fecha de fin</th>
                            <th>Situación de la solicitud</th>
                            <th>Situación del préstamo</th>
                            <th>Parcialidad</th>
                            <th>Cantidad solicitada</th>
                            <th>Días de mora</th>
                            <th>Cantidad entregada</th>
                            <th>Interés generado</th>
                            <th>NO_PUEDO_PAGAR</th>
                            <th>Capital pagado</th>
                            <th>Interés pagado</th>
                            <th>Total pagado</th>
                            <th>Saldo capital</th>
                            <th>Saldo interés</th>
                            <th>Saldo total</th>
                            <th>Mora total</th>
                            <th>Garantía</th>
                            <th>Asesor</th>
                            <th>Gerente</th>
                            <th>Tipo de cartera</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $footer; ?>