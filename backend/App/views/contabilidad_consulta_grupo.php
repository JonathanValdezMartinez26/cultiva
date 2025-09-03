<?= $header; ?>

<div class="right_col">
    <div class="panel">
        <div class="panel-header" style="padding: 10px;">
            <div class="x_title">
                <label style="font-size: large;">Consulta por Grupo</label>
                <div class="clearfix"></div>
            </div>
            <div class="card">
                <div class="card-header" style="margin: 20px 0;">
                    <span class="card-title">Ingrese el numero de grupo y el ciclo</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <input type="text" class="form-control" id="noGrupoBuscar" placeholder="Número de grupo" maxlength="6">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <input type="text" class="form-control" id="cicloBuscar" placeholder="Ciclo" maxlength="6">
                            </div>
                        </div>
                        <div class="col-md-2">
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
                <table class="table table-striped table-bordered table-hover" id="grupo">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Nombre Grupo</th>
                            <th>Cliente</th>
                            <th>Préstamo</th>
                            <th>Seguro Financiado</th>
                            <th>Total del crédito</th>
                            <th>Garantía</th>
                            <th>Fecha de inicio</th>
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