<?= $header; ?>

<div class="right_col">
    <div class="panel">
        <div class="panel-header" style="padding: 10px;">
            <div class="x_title">
                <label style="font-size: large;"> Consulta de clientes</label>
                <div class="clearfix"></div>
            </div>
            <div class="card">
                <div class="card-header" style="margin: 20px 0;">
                    <span class="card-title">Seleccione el rango de fechas para el reporte.</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Desde</label>
                                <input type="date" class="form-control" id="fechaI" value="<?= date('Y-m-d'); ?>" max="<?= date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Hasta</label>
                                <input type="date" class="form-control" id="fechaF" value="<?= date('Y-m-d'); ?>" max="<?= date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-2" style="min-height: 68px; display: flex; justify-content: space-between; align-items: center;">
                            <button type="button" class="btn btn-primary" id="buscar"><i class="fa fa-search">&nbsp;</i>Buscar</button>
                            <button type="button" class="btn btn-success" id="exportar"><i class="fa fa-file-excel-o">&nbsp;</i>Exportar a Excel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="panel-body resultado">
            <div class="row">
                <table class="table table-striped table-bordered table-hover" id="clientes">
                    <thead>
                        <tr>
                            <th>Sucursal</th>
                            <th>Crédito</th>
                            <th>Nombre del Grupo</th>
                            <th>Ciclo</th>
                            <th>Nombre del Cliente</th>
                            <th>Dirección del Cliente</th>
                            <th>Fecha Solicitud</th>
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

<?= $footer; ?>