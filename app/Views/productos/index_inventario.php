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
                        <h3 class="card-title">Reporte</h3>
                        <div class="card-tools">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-default">Reportes</button>
                                <button type="button" class="btn btn-sm btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false">
                                    <span class="sr-only">Ver Reportes</span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    <a class="dropdown-item" href="<?= site_url('productos/createpdf') ?>"><i class='fas fa-file-pdf'></i> Inventario C/Stock</a>
                                    <a class="dropdown-item" href="<?= site_url('productos/createpdfsv') ?>"><i class='fas fa-file-pdf'></i> Inventario S/Stock</a>
                                    <a class="dropdown-item" href="<?= site_url('productos/createlistaprecios') ?>"><i class='fas fa-file-pdf'></i> Lista de Precios</a>                                       
                                </div>
                            </div>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn bg-olive btn-sm active">
                                    <input type="radio" name="options" id="option_b1" value='2' autocomplete="off"> Todos
                                </label>
                                <label class="btn bg-olive btn-sm">
                                    <input type="radio" name="options" id="option_b2" value='1' autocomplete="off" checked=""> Con Stock
                                </label>
                            </div>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <button type="button" id="caja_cnt" class="btn btn-<?= $color == 'success' ? $color : 'default'; ?> btn-sm"><i class="fa fa-inbox"> </i> Cja Centro</button>
                            <button type="button" id="caja_pmz" class="btn btn-<?= $color == 'info' ? $color : 'default'; ?> btn-sm"><i class="fa fa-inbox"> </i> Cja PMeza</button>
                            <button type="button" id="caja_jjc" class="btn btn-<?= $color == 'danger' ? $color : 'default'; ?> btn-sm"><i class="fa fa-inbox"> </i> Cja Juanjuicillo</button>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="productos_centro" class="table table-bordered">
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
                    width: "10%",
                    className: 'dt-body-right'
                },
                {
                    data: 'ART_NOMBRE'
                },
                {
                    data: 'PRE_EQUIV',
                    width: "10%",
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
            searching: false,
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



<?= $this->endSection(); ?>