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
                        
                        <div class="row">
                            <div class="input-group input-group-sm col-lg-6">
                                <button type="button" id="caja_cnt" class="btn btn-<?=$color=='success'?$color:'default';?> btn-sm"><i class="fa fa-inbox"> </i> Centro</button>
                                <button type="button" id="caja_pmz" class="btn btn-<?=$color=='info'?$color:'default';?> btn-sm"><i class="fa fa-inbox"> </i> PMeza</button>
                                <button type="button" id="caja_jjc" class="btn btn-<?=$color=='danger'?$color:'default';?> btn-sm"><i class="fa fa-inbox"> </i> Juanjuicillo</button>
                            </div>
                            <div class="input-group input-group-sm col-lg-6">
                                <div class="input-group-prepend">
                                <button type="button" class="btn btn-danger">Factura</button>
                                </div>
                                <input type="text" class="form-control" id="factura" placeholder="Número">	
                                <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" class="form-control float-right" id="reservation">
                                <span class="input-group-append">
                                <button type="button" id="buscar" name="buscar" class="btn btn-success"><i class="fa fa-search"> </i> Ver</button>
                                </span>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="productos_centro" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>TIPO</th>
                                    <th>NUM.DOC</th>
                                    <th>DOC.PROV</th>
                                    <th>CLIENTE</th>
                                    <th>FECHA</th>
                                    <th>CODIGO</th>
                                    <th>ARTICULO</th>
                                    <th>STK.SIST</th>
                                    <th>CANT</th>
                                    <th>DESC</th>
                                    <th>PRECIO</th>
                                    <th>LABORATORIO</th>
                                    <th>DCI</th>
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
$(document).ready(function () {
    $('#reservation').daterangepicker();
        var dtable = $('#productos_centro').DataTable({
            ajax: {
                url: "<?= site_url('productos/get_mov_controlados') ?>",
                type: "POST",
                dataSrc: '',
                data: { 
                    fecha: function () { return $("input#reservation").val(); },
                    factura: function () { return $("input#factura").val(); }
                }
            },
            columns: [
                {data: 'TIPO'},
                {data: 'FAR_NUMSER',
                    render: function(data, type, row, meta) {

                        var rpta = '<a href="<?=site_url('operaciones/receta_print')?>/'+<?=$caja?>+'/'+row.FAR_TIPMOV+'/'+row.FAR_NUMSER+'/'+row.FAR_NUMFAC+'/'+row.FAR_FECHA+'" class="d-block" target="_blank"><i class="fas fa-photo-video"></i> '+ row.FAR_NUMSER + '-' + row.FAR_NUMFAC +'</a>'
                         
                        return rpta;
                    }
                },
                {data: 'FACTURA_PROV'},                
                {data: 'CLIENTE'},
                {data: 'FAR_FECHA'},
                {data: 'ART_KEY', width: "10%",className: 'dt-body-right'},
                {data: 'ART_NOMBRE'},
                {data: 'STOCK'},
                {data: 'CANTIDAD', render: $.fn.dataTable.render.number( ',', '.', 0, '' ), width: "10%",className: 'dt-body-right'},
                {data: 'FAR_DESCRI'},
                {data: 'FAR_PRECIO', render: $.fn.dataTable.render.number( ',', '.', 2, 'S/. ' )},
                {data: 'LABORATORIO'},
                {data: 'DCI'},


            ],
            searching: false,
            paging: true,
            responsive: true, lengthChange: false, autoWidth: false, dom: 'Bfrtip'
        });
    $("#buscar").click(function () {
        dtable.ajax.reload();           
    }); 



});


$("#caja_cnt").click(function(e) {
    set_caja(e,1);
});
$("#caja_pmz").click(function(e) {
    set_caja(e,3);
});
$("#caja_jjc").click(function(e) {
    set_caja(e,2);
});
function set_caja(e,x) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "<?= site_url('caja/set_caja') ?>",
        data: { 
			caja:x ,opci: 'caja'
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