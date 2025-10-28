<?= $this->extend('templates/admin_template'); ?>
	<?= $this->section('content'); ?>
   <style>
      @media screen and (max-width: 980px) {
      .desktop {
         display: none;
         }
      }
   </style>
<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->
<!-- Main content -->
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-5">
            <div class="card">
               <div class="card-header">
                <div class="card-tools"></div>
               <div class="input-group input-group-sm col-lg-6">
                           <div class="input-group-prepend">                           
                           <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                           </div>
                           <input type="text" class="form-control float-right" id="reservation">
                           <span class="input-group-append">
                           <button type="button" id="buscar" name="buscar" class="btn btn-success"><i class="fa fa-search"> </i> Buscar</button>
                           </span>
                        </div>
               </div>
               <div class="card-body table-responsive">
                  <table id="table_centro" class="table table-bordered">
                     <thead>
                        <tr>
                           <th>FECHA</th>
                           <th>SERVICIO</th>
                           <th>PACIENTE</th>
                           <th>NRO.PAGO</th>
                           <th>MONTO</th>
                           <th>EMPLEADO</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                  </table>
               </div>
               <div class="card-body">
                    <button id='DLtoExcel-2' class="btn btn-danger">Exportar las tablas a Excel</button>
                </div>
            </div>
            <!-- /.card -->
         </div>
         <!-- /.col-md-6 -->
         <div class="col-md-7">
            <div class="form-group">
               <div class="card">
                  <div class="card-header border-0"></div>
                  <div class="card-body table-responsive-sm">
                  <table id="table_jcillo" 
                  class="table table-bordered">
                     <thead>
                        <tr>
                           <th>FECHA</th>
                           <th>SERVICIO</th>
                           <th>PACIENTE</th>
                           <th>NRO.PAGO</th>
                           <th>MONTO</th>
                           <th>EMPLEADO</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                  </table>
               </div>
               </div>
               <!-- /.card -->

                <div class="card">
                    <div class="card-header border-0"></div>
                    <div class="card-body table-responsive">
                        <table id="table_pm" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>FECHA</th>
                                    <th>SERVICIO</th>
                                    <th>PACIENTE</th>
                                    <th>NRO.PAGO</th>
                                    <th>MONTO</th>
                                    <th>EMPLEADO</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
               <!-- /.card -->

            </div>
         </div>
      </div>
      <!-- /.row -->
      <div class="row">      
      <div id="dvjson"></div>
      </div>
   </div>
   <!-- /.container-fluid -->
</div>
<!-- /.content -->

<?= $this->endSection(); ?>


<?= $this->section('footer'); ?>
<!-- Select2 -->
<link rel="stylesheet" href="../../plugins/select2/css/select2.min.css" />
<link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css" />
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<!--link rel="stylesheet" href="../../plugins/datatables/jquery.dataTables.min.css"-->
<link rel="stylesheet" href="<?= site_url('../../plugins/datatables-keytable/css/keyTable.dataTables.min.css') ?>">
<!-- Select2 -->
<script src="../../plugins/select2/js/select2.full.min.js"></script>
<script src="../../plugins/bootstrap-switch/js/bootstrap-switch.js"></script>
<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-select/js/dataTables.select.min.js"></script>
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script src="../../plugins/Editor/js/dataTables.editor.min.js"></script>
<script src="<?= site_url('../../plugins/datatables-keytable/js/dataTables.keyTable.min.js') ?>"></script>
<script src='<?= site_url('dist/js/excelexportjs.js')?>'></script>

<script>
   var editor;
   var serie=0;
   var factura=0;
   var fecha= moment().format('DD/MM/YYYY');
   var data01=[];
   var data02=[];
   var data03=[];

$(document).ready(function() {
    $('#reservation').daterangepicker();

var dtablect = $('#table_centro').DataTable( {
   ajax: {
      url: "<?= site_url('consultorio/get_pagos') ?>",
      type: "POST",
      dataSrc:function (json_data) { for(var i in json_data) data01.push(json_data [i]); return json_data; },
      data: { 
        fecha: function () { return $("input#reservation").val(); },
        local: 1
      }
   },
   columns:[ 
      {data:'ALL_FECHA_PRO'},
      {data:'ART_NOMBRE'},
      {data:'far_cliente'},
      {data:'COMPROBANTE'},
      {data:'FAR_PRECIO'},
      {data:'VEN'}
   ],
        paging: true,
        orderable: false,
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        select: true,
        ordering: false,
        oLanguage: {"sInfo" : " "},
        keys: true
   });   

   var dtablejcillo = $('#table_jcillo').DataTable( {
   ajax: {
      url: "<?= site_url('consultorio/get_pagos') ?>",
      type: "POST",
      dataSrc:function (json_data) { for(var i in json_data) data01.push(json_data [i]); return json_data; },
      data: { 
        fecha: function () { return $("input#reservation").val(); },
        local: 2
      }
   },
   columns:[ 
      {data:'ALL_FECHA_PRO'},
      {data:'ART_NOMBRE'},
      {data:'far_cliente'},
      {data:'COMPROBANTE'},
      {data:'FAR_PRECIO'},
      {data:'VEN'}
   ],
        paging: true,
        orderable: false,
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        select: true,
        ordering: false,
        oLanguage: {"sInfo" : " "},
        keys: true
   }); 

   var dtablepm = $('#table_pm').DataTable( {
   ajax: {
      url: "<?= site_url('consultorio/get_pagos') ?>",
      type: "POST",
      dataSrc:function (json_data) { for(var i in json_data) data01.push(json_data[i]); return json_data; },
      data: { 
        fecha: function () { return $("input#reservation").val(); },
        local: 3
      }
   },
   columns:[ 
      {data:'ALL_FECHA_PRO'},
      {data:'ART_NOMBRE'},
      {data:'far_cliente'},
      {data:'COMPROBANTE'},
      {data:'FAR_PRECIO'},
      {data:'VEN'}
   ],
        paging: true,
        orderable: false,
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        select: true,
        ordering: false,
        oLanguage: {"sInfo" : " "},
        keys: true
   });  


   $("#buscar").click(function () {
    data01 = [];
    dtablect.ajax.reload();       
    dtablejcillo.ajax.reload();  
    dtablepm.ajax.reload();  
   }); 

   var $btnDLtoExcel = $('#DLtoExcel-2');
    $btnDLtoExcel.on('click', function () {
        $("#dvjson").excelexportjs({
                    containerid: "dvjson"
                       , datatype: 'json'
                       , dataset: data01
                       , columns: getColumns(data01)     
                });

    });

})
   
</script>
<?= $this->endSection(); ?>