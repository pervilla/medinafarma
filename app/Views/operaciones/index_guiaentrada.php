<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->
<!-- Main content -->
<section class="content">
   <div class="container-fluid">
   <div class="row">
         <div class="col-lg-7">
            <div class="card">
               <div class="card-header">
                  <h3 class="card-title">Exportar Guias</h3>
                  <div class="card-tools">
                     <div class="row">
                        <div class="input-group input-group-sm col-lg-6">
                           <button type="button" id="caja_cnt" class="btn btn-<?=$color=='success'?$color:'default';?> btn-sm"><i class="fa fa-inbox"> </i> Centro</button>
                           <button type="button" id="caja_pmz" class="btn btn-<?=$color=='info'?$color:'default';?> btn-sm"><i class="fa fa-inbox"> </i> Almacen</button>
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
                  <table id="operaciones" class="table table-bordered">
                     <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Numero</th>
                            <th>Concepto</th>
                            <th>Importe</th>
                            <th>Guia</th>
                            <th></th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                  </table>
               </div>
            </div>
            <!-- /.card -->
         </div>
         <!-- /.col-md-6 -->
         <div class="col-md-5">
            <div class="form-group">
               <div class="card">
                  <div class="card-header border-0"></div>
                  <div class="card-body table-responsive">
                     <table id="table_factura" class="table table-bordered">
                        <thead>
                           <tr>
                              <th>COD</th>
                              <th>PRODUCTO</th>
                              <th>UND</th>                              
                              <th>CANT</th>
                              <th>PRECIO</th>                           
                              <th>TOTAL</th>
                           </tr>
                        </thead>
                        <tbody>
                        </tbody>
                     </table>
                  </div>
               </div>
               <!-- /.card -->
            </div>
         </div>
      </div>
      <!-- /.row -->  
    </div>
   </div>
   </div>
   <div class="modal fade" id="modal-overlay" style="display: none;" aria-hidden="true">
      <div class="modal-dialog">
         <div class="modal-content bg-primary">
            <div class="overlay">
               <i class="fas fa-2x fa-sync fa-spin"></i>
            </div>
            <div class="modal-header">
               <h4 class="modal-title">Migración de Guia</h4>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">×</span>
               </button>
            </div>
            <div class="modal-body">
               <div class="input-group mb-3">
                  <div class="input-group-prepend">
                     <span class="input-group-text"><i class="fas fa-file"></i></span>
                  </div>
                  <input type="text" class="form-control" id="ALL_NUMSER" name="ALL_NUMSER">
                  <input type="text" class="form-control" id="ALL_NUMFAC" name="ALL_NUMFAC">
                  <input type="text" class="form-control" id="ALL_FECHA_PRO" name="ALL_FECHA_PRO">
               </div>
               <div class="input-group mb-3">
                  <div class="input-group-prepend">
                     <span class="input-group-text"><i class="fas fa-building"></i></span>
                  </div>
                  <select class="form-control" id="Local" name="Local">
                     <option value="0" selected="selected">- SELECCIONE LOCAL RECIBE MERCADERIA-</option>
                     <option value="2">JUANJUICILLO</option>
                     <option value="3">PEÑAMEZA</option>
                  </select>
               </div>
               <div class="input-group mb-3">
                  <div class="input-group-prepend">
                     <span class="input-group-text"><i class="fas fa-file"></i></span>
                  </div>
                  <input type="text" class="form-control" id="RPT_ALL_NUMSER" name="RPT_ALL_NUMSER" placeholder="Respuesta Nro Serie">
                  <input type="text" class="form-control" id="RPT_ALL_NUMFAC" name="RPT_ALL_NUMFAC" placeholder="Respuesta Nro Guia">
               </div>
            </div>
            <div class="modal-footer justify-content-between">
               <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
               <button type="button" id="exportar" class="btn btn-outline-light">Exportar</button>
            </div>
         </div>
         <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
   </div>
</section>
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
   var serie=0;
   var factura=0;
   var fecha= moment().format('YYYY-MM-DD');
    $('#reservation').daterangepicker();
        var dtable = $('#operaciones').DataTable({
            ajax: {
                url: "<?= site_url('operaciones/get_guias') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    fecha: function () { return $("input#reservation").val(); },
                    factura: function () { return $("input#factura").val(); }, 
                    operacion: 6
                }
            },
            columns: [
                {data: 'ALL_FECHA_PRO',render: function(data, type, row, meta) {return moment(row.ALL_FECHA_PRO).format('DD/MM');}},
                {data: 'ALL_NUMSER',render: function(data, type, row, meta) {return row.ALL_NUMSER+'-'+row.ALL_NUMFAC;}},
                {data: 'ALL_CONCEPTO'},
                {data: 'ALL_IMPORTE_AMORT', render: $.fn.dataTable.render.number( ',', '.', 2, 'S/. ' ), className: "text-right"},
                {data: 'ALL_CTAG1'},
                
                {defaultContent: "<div class='btn-group btn-group-sm' role='group' aria-label='Small button group'><button type='button' id='verguia' class='btn btn-warning'><i class='fas fa-eye'></i> Ver</button></div>"},
            ],
            searching: false,
            paging: true,
            ordering: false,
             responsive: true, lengthChange: false, autoWidth: false, dom: 'Bfrtip'
        });
        var dtablefac = $('#table_factura').DataTable( {
               ajax: {
                 url: "<?= site_url('operaciones/get_operacion') ?>",
                 type: "POST",
                 dataSrc:'',
                 data: { 
                     fecha: function () { return moment(fecha).format('DD/MM/YYYY'); },
                     serie: function () { return serie; },
                     factura: function () { return factura; },
                     operacion: 6
                 }
               },
               columns:[
                  {data:'FAR_CODART'},
                  {data:'ART_NOMBRE'},
                  {data:'FAR_DESCRI'},
                  {data:'FAR_CANTIDAD', render: $.fn.dataTable.render.number( ',', '.', 0, '' ), className: "text-right"},
                  {data:'FAR_PRECIO', render: $.fn.dataTable.render.number( ',', '.', 2, 'S/.' ), className: "text-right"},                  
                  {data:'FAR_SUBTOTAL', render: $.fn.dataTable.render.number( ',', '.', 2, 'S/.' ), className: "text-right"},
               ],

             searching: false,
             paging: false,
             ordering: false,
             responsive: true, lengthChange: false, autoWidth: false, dom: 'Bfrtip'
             });

    $("#buscar").click(function () {
        dtable.ajax.reload();           
    }); 

    $('#operaciones tbody').on('click', '#exportarguia', function () {
        var data = dtable.row($(this).parents('tr')).data();
        if(data['ALL_CTAG1']>0){
         $("#exportar").hide();
        }else{
         $("#exportar").show();
        }     
        $("#ALL_NUMSER").val(data['ALL_NUMSER']);
        $("#ALL_NUMFAC").val(data['ALL_NUMFAC']);
        $("#ALL_FECHA_PRO").val(data['ALL_FECHA_PRO']);
        $("#RPT_ALL_NUMSER").val('');
        $("#RPT_ALL_NUMFAC").val('');
        $("#Local").val("0").change();
        $('#modal-overlay').modal('show');
        $('.overlay').hide();
    });

    $('#operaciones tbody').on( 'click', '#verguia', function (event) {
               var data = dtable.row( $(this).parents('tr') ).data();
               serie=data['ALL_NUMSER'];
               factura=data['ALL_NUMFAC'];
               fecha=data['ALL_FECHA_PRO'];    
               dtablefac.ajax.reload();
               
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





    



    
});

</script>
<?= $this->endSection(); ?>