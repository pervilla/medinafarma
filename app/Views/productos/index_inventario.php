<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header">

</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-boxes"></i> Reportes y Stock de Inventario</h3>
                        <div class="card-tools">
                            <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                                <!-- Filtro Cajas -->
                                <div class="btn-group mr-2" role="group" aria-label="Filtro Cajas">
                                    <button type="button" id="caja_cnt" class="btn btn-<?= $color == 'success' ? $color : 'default'; ?> btn-sm" title="Caja Centro"><i class="fas fa-store"></i> Centro</button>
                                    <button type="button" id="caja_pmz" class="btn btn-<?= $color == 'info' ? $color : 'default'; ?> btn-sm" title="Caja PMeza"><i class="fas fa-store"></i> PMeza</button>
                                    <button type="button" id="caja_jjc" class="btn btn-<?= $color == 'danger' ? $color : 'default'; ?> btn-sm" title="Caja Juanjuicillo"><i class="fas fa-store"></i> Juanjuicillo</button>
                                </div>
                                
                                <!-- Filtro Stock -->
                                <div class="btn-group btn-group-toggle mr-2" data-toggle="buttons">
                                    <label class="btn bg-olive btn-sm active">
                                        <input type="radio" name="options" id="option_b1" value='2' autocomplete="off"> Todos
                                    </label>
                                    <label class="btn bg-olive btn-sm">
                                        <input type="radio" name="options" id="option_b2" value='1' autocomplete="off" checked=""> Con Stock
                                    </label>
                                </div>

                                <!-- Reportes -->
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-file-alt"></i> Reportes
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" role="menu">
                                        <a class="dropdown-item" href="<?= site_url('productos/createpdf') ?>"><i class='fas fa-file-pdf text-danger'></i> Inventario C/Stock</a>
                                        <a class="dropdown-item" href="<?= site_url('productos/createpdfsv') ?>"><i class='fas fa-file-pdf text-danger'></i> Inventario S/Stock</a>
                                        <a class="dropdown-item" href="<?= site_url('productos/createlistaprecios') ?>"><i class='fas fa-file-pdf text-danger'></i> Lista de Precios</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal-mas-vendidos"><i class='fas fa-star text-warning'></i> Más Vendidos</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="<?= site_url('productos/createpdf_control_inventario?tipo=01') ?>" target="_blank"><i class='fas fa-clipboard-check text-success'></i> Control Ventas (Top 200 Rot.)</a>
                                        <a class="dropdown-item" href="<?= site_url('productos/createpdf_control_inventario?tipo=02') ?>" target="_blank"><i class='fas fa-clipboard-check text-success'></i> Control Ventas (Top 50 Costo)</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="productos_centro" class="table table-bordered table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Familia</th>
                                    <th>Articulo</th>
                                    <th>Equiv</th>
                                    <th>Unid</th>
                                    <th>Stock</th>
                                    <th>Precio</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</div>
<!-- /.content -->

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script>
    $(document).ready(function() {
        var dtable = $('#productos_centro').DataTable({
            ajax: {
                url: "<?= site_url('productos/get_stock_articulos') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    stock: function() {
                        return $("input[name=options]:checked").val();
                    }
                }
            },
            columns: [{
                    data: 'ART_KEY'
                },
                {
                    data: 'TAB_NOMLARGO',
                    width: "15%",
                    className: 'dt-body-right'
                },
                {
                    data: 'ART_NOMBRE'
                },
                {
                    data: 'PRE_EQUIV',
                    width: "5%",
                    className: 'dt-body-right'
                },
                {
                    data: 'PRE_UNIDAD',
                    width: "10%",
                    className: 'dt-body-right'
                },
                {
                    data: 'ARM_STOCK'
                },
                {
                    data: 'PRE_PRE1'
                },
            ],
            order: [
                [1, 'asc'],
                [2, 'asc']
            ],
            rowGroup: {
                dataSrc: 5
            },
            searching: true,
            paging: true,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            dom: 'Bfrtip'
        });
        $("input[name=options]").click(function() {
            dtable.ajax.reload();
        });


    });


    $("#caja_cnt").click(function(e) {
        set_caja(e, 1);
    });
    $("#caja_pmz").click(function(e) {
        set_caja(e, 3);
    });
    $("#caja_jjc").click(function(e) {
        set_caja(e, 2);
    });

    function set_caja(e, x) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "<?= site_url('caja/set_caja') ?>",
            data: {
                caja: x,
                opci: 'caja'
            },
            success: function(result) {
                location.reload();
                return false;
            },
            error: function(result) {
                alert('error');
            }
        });
    }
</script>




<!-- Modal Mas Vendidos -->
<div class="modal fade" id="modal-mas-vendidos" tabindex="-1" role="dialog" aria-labelledby="modalMasVendidosLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMasVendidosLabel">Reporte: Inventario Mas Vendidos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formMasVendidos">
                    <div class="form-group">
                        <label for="anio">Año</label>
                        <input type="number" class="form-control" id="anio" name="anio" value="<?= date('Y') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="cantidad">Cantidad de Productos</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" value="100" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnGenerarReporte">Generar Reporte</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('btnGenerarReporte').addEventListener('click', function() {
        var anio = document.getElementById('anio').value;
        var cantidad = document.getElementById('cantidad').value;
        var url = "<?= site_url('productos/createpdf_masvendidos') ?>?anio=" + anio + "&cantidad=" + cantidad;
        window.open(url, '_blank');
        $('#modal-mas-vendidos').modal('hide');
    });
</script>

<?= $this->endSection(); ?>