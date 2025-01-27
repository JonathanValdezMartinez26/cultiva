<?= $header ?>

<div class="right_col">
    <div class="panel">
        <div class="panel-header" style="padding: 10px;">
            <div class="x_title">
                <label style="font-size: large;">Reporte de referencias de pagos</label>
                <div class="clearfix"></div>
            </div>
            <div class="card">
                <div class="card-header" style="margin: 20px 0;">
                    <span class="card-title">Configure los parámetros de búsqueda</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="noCredito">No. Crédito</label>
                                <input type="text" class="form-control" id="noCredito" placeholder="000000" maxlength="6">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary" id="buscar"><i class="fa fa-search"></i> Buscar</button>
                            <button type="button" class="btn btn-success" id="descargarExcel"><i class="fa fa-file-excel-o"></i> Exportar a Excel</button>
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
                                <th>Crédito</th>
                                <th>Grupo</th>
                                <th>Sucursal</th>
                                <th>Región</th>
                                <th>Tipo</th>
                                <th>Paycash</th>
                                <th>Bancopel</th>
                                <th>Oxxo</th>
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

<?= $footer ?>