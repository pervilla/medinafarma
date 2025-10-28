<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid"> 
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Comisiones de Vendedores</h3>
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
                    <div class="card-body table-responsive">
                        <table id="table_comisiones" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Vendedor</th>
                                    <th>Ventas Centro</th>
                                    <th>Ventas JJcillo</th>
                                    <th>Ventas PMeza</th>
                                    <th>Ventas Total</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th>Vendedor</th>
                                    <th>Ventas Centro</th>
                                    <th>Ventas JJcillo</th>
                                    <th>Ventas PMeza</th>
                                    <th>Ventas Total</th>
                                </tr>
                            </tfoot>
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
    $(document).ready(function () {
       

        var dtable = $('#table_comisiones').DataTable({
            ajax: {
                url: "<?= site_url('comisiones/get_comisiones') ?>",
                type: "POST",
                dataSrc: '',
                data: { 
                  mes: function () { return $( "select#mes option:checked" ).val(); },
                  anio: function () { return $( "select#anio option:checked" ).val(); }
                }
            },
            columns: [
                {data: 'VEM_NOMBRE'},
                {data: 'COMISION'},
                {data: 'COMISION2'},
                {data: 'COMISION3'},
                {data: 'TOTAL'}
            ],
            order: [[4, 'desc']],
            rowGroup: {
                dataSrc: 4
            },
            searching: false,
            paging: false,
            responsive: true, lengthChange: false, autoWidth: false, dom: 'Bfrtip'
        });
        $("#buscar").click(function () {
            dtable.ajax.reload();           
        }); 



    });

</script>
<?= $this->endSection(); ?>