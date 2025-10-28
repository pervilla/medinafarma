<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Creditos y Adelantos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Simple Tables</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Listado de Creditos</h3>
                        <div class="card-tools">
                        <div class="input-group input-group-sm mb-12">
                            <div class="input-group-prepend">
                                <button type="button" class="btn btn-danger">Mes/Año</button>
                            </div>
                            <!-- /btn-group -->
                            <select class="form-control" id="mes" name="mes">
                            <?php 
                            $i=1; 
                            $mes = ['','ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
                            while ($i <= 12) { $sel = date('n')==$i?"selected='selected'":"" ?>
                                <?="<option value='$i' $sel>".$mes[$i]."</option>"; ?>
                           <?php $i++; } ?>
                            </select>                     
                            <select class="form-control" id="anio" name="anio">
                            <?php $i=2021; while ($i <= date('Y')) { $sel = date('Y')==$i?"selected='selected'":"" ?>
                                <?="<option value='$i' $sel>".date("y", strtotime($i))."</option>"; ?>
                           <?php $i++; } ?>
                            </select>
                            <span class="input-group-append">
                                <button type="button" id="buscar" name="buscar" class="btn btn-success"><i class="fa fa-search"> </i> Ver</button>
                            </span>
                        </div>                                              
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="table_creditos" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Trabajador</th>
                                    <th>Creditos</th>
                                    <th style="width: 40px">Detalle</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->                    
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 id="emp_table_detalle" class="card-title">Detalle : </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="table_detalle" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th style="width: 40px">Monto</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
<?= $this->endSection(); ?>


<?= $this->section('footer'); ?>
<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script>
    $(document).ready(function () {        
        var dtable = $('#table_creditos').DataTable({
            ajax: {
                url: "<?= site_url('empleado/get_creditos') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    mes: function () { return $( "select#mes option:checked" ).val(); },
                    anio: function () { return $( "select#anio option:checked" ).val(); }
                }
            },
            columns: [
                {data: 'CMV_CODVEN'},
                {data: 'VEM_NOMBRE'},
                {data: 'DEUDA'},
                {defaultContent: "<button class='btn btn-block btn-outline-primary btn-xs'>Ver</button>"}
            ],
            searching: false,
            paging: false,
            order: [[2, 'DESC']],
            rowGroup: {
                dataSrc: 2
            }
        });
        $('#table_creditos tbody').on('click', 'button', function () {
            var data = dtable.row($(this).parents('tr')).data();
            $("#emp_table_detalle").text("Detalle : " + data['VEM_NOMBRE']);
            $("#table_detalle > tbody").html("");
            $.post("get_creditos_empleado", {
                mes: $( "select#mes option:checked" ).val(),
                anio: $( "select#anio option:checked" ).val(),
                empl: data['CMV_CODVEN']
            }, function (htmlexterno) {                
                $("#table_detalle > tbody").html(htmlexterno);
            });
        });
        $("#buscar").click(function () {
            dtable.ajax.reload();           
        }); 
    });
</script>
<?= $this->endSection(); ?>