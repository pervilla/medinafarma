<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>


<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Comisiones de Ventas</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Comisiones de Ventas</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <!-- /.card -->

                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title">Avance de Ventas</h3>
                        <div class="card-tools">
                            <a href="#" class="btn btn-tool btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="#" class="btn btn-tool btn-sm">
                                <i class="fas fa-bars"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body table-responsive p-0">
                        <table id="comiciones" class="table table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th>Vendedor</th>
                                    <th>Ventas Centro</th>
                                    <th>Ventas PMeza</th>
                                    <th>Ventas Total</th>
                                    <th>Avance</th>

                                </tr>
                            </thead>
                            <tbody>

                                <?php foreach ($comisiones as $comision) { ?>
                                    <tr>
                                        <td>
                                            <img src="dist/img/default-150x150.png" alt="Product 1" class="img-circle img-size-32 mr-2">
                                            <?php echo $comision->VEM_NOMBRE; ?>
                                        </td>
                                        <td><?php echo number_format($comision->COMISION, 2); ?></td>
                                        <td><?php echo number_format($comision->COMISION3, 2); ?></td>
                                        <td><?php echo number_format($comision->TOTAL, 2); ?></td>
                                        <td>
                                            <small class="text-success mr-1">
                                                <i class="fas fa-arrow-up"></i>
                                                <?php echo number_format($comision->AVANCE, 2) . "%"; ?>
                                            </small>
                                            <?php echo number_format($comision->VEM_META, 2); ?>
                                        </td>

                                    </tr>
                                <?php } ?>

                            </tbody>
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
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>
<script>
    $(document).ready(function () {

        $('#reservation').daterangepicker();
        var dtable = $('#comiciones').DataTable({
            ajax: {
                url: "http://localhost:8080/comiciones/get_comiciones",
                type: "POST",
                dataSrc: '',
                data: {
                    fecha: $("input#reservation").val(),
                    operacion: 5
                }
            },
            columns: [
                {data: 'VEM_NOMBRE'},
                {data: 'COMISION'},
                {data: 'COMISION3'},
                {data: 'TOTAL'},
                {defaultContent: "<button class='btn btn-block btn-outline-primary btn-xs'>Ver</button>"}
            ],
            searching: false,
            order: [[2, 'asc']],
            rowGroup: {
                dataSrc: 2
            }
        });

        $('#comiciones').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    });
    </scrip>
    <?= $this->endSection(); ?>