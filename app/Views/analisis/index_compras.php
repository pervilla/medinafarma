<?php /** @var CodeIgniter\View\View $this*/ ?>
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
               <div class="card-header"><div class="card-tools"></div>
                  <form>
                     <input type="hidden" name="art" id="art" value='000'>
                     <div class="input-group mt-2">
                     <div class="input-group-prepend">
                           <span class="input-group-text bg-danger">Proveedor</span>
                     </div>
                     <select id="b_cliente" name="b_cliente"  data-placeholder="Buscar Proveedor" data-allow-clear="1"></select>
                     <div class="input-group-append">
                           <button id="b_buscar" class="btn  btn-success" type="button">Buscar</button>          
                     </div>
                     </div> 
                  </form>
               </div>
               <div class="card-body table-responsive">
                  <table id="table_productos" class="table table-bordered">
                     <thead>
                        <tr>
                           <th>Serie</th>
                           <th>Número</th>
                           <th>Fecha</th>
                           <th>Proveedor</th>
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
         <div class="col-md-7">
            <div class="form-group">
               <div class="card">
                  <div class="card-header border-0"></div>
                  <div class="card-body table-responsive">
                     <table id="table_compras" class="table table-bordered">
                        <thead>
                           <tr>
                              <th>CLIENTE</th>
                              <th>PRODUCTO</th>
							  <th>FECHA</th>
                              <th>UND</th>
                              <th>COSTO</th>
                              
                              <th></th>
                           </tr>
                        </thead>
                        <tbody>
                        </tbody>
                     </table>
                  </div>
               </div>
               <!-- /.card -->

               <div class="card">
                  <div class="card-header border-0"></div>
                  <div class="card-body table-responsive">
                     <table id="table_cuadro" class="table table-bordered">
                        <thead>
                           <tr>
                              <th>AÑO</th>
                              <th>MES</th>
                              <th>COMPRA</th>
                              <th>VENTA</th>
                              <th>SALIDAS</th>                              
                              <th>INGRESOS</th>
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
      <div class="row">
         
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


<script>
   var editor;
   var serie=0;
   var factura=0;
   var fecha= moment().format('DD/MM/YYYY');
   
$(document).ready(function() {
    $('#reservation').daterangepicker();
    $('#b_cliente').select2({
        theme: 'bootstrap4',
        width: $('#b_cliente').data('width') ? $('#b_cliente').data('width') : $('#b_cliente').hasClass('w-100') ? '100%' : 'style',
        placeholder: "Buscar Proveedor",
        allowClear: Boolean($('#b_cliente').data('allow-clear')),
        closeOnSelect: !$('#b_cliente').attr('multiple'),
        ajax: {
            url: "<?= site_url('personas/get_proveedores') ?>",
            dataType: "json",
            processResults: function(data) {
                return {
                    results: data,
                };
            },
        },

        escapeMarkup: function(markup) {
            return markup;
        },
    });

var dtable = $('#table_productos').DataTable( {
   ajax: {
      url: "<?= site_url('analisis/get_compras') ?>",
      type: "POST",
      dataSrc:'',
      data: { 
         codcli: function () { return $("select#b_cliente option:checked").val(); },
         art: function () { return ''; },
         operacion: 20
      }
   },
   columns:[
      {data:'ART_KEY'},
      {data:'ART_NOMBRE'},
      {data:'PRE_UNIDAD'},
      {data:'STOCK'},
      {defaultContent: "<button class='btn btn-block btn-outline-primary btn-xs'>Ver</button>" }
   ],
   fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
      if (aData.ARM_STOCK > 0) {
            $(nRow).addClass('bg-success');
      }
      return nRow;
   },
   searching: true,
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

   var dtablecompras = $('#table_compras').DataTable( {
   ajax: {
      url: "<?= site_url('analisis/get_compras') ?>",
      type: "POST",
      dataSrc:'',
      data: { 
         codcli: function () { return ''; },
         art: function () { return $('input#art').val();  },
         operacion: 20
      }
   },
   columns:[
      {data:'CLI_NOMBRE'},
      {data:'ART_NOMBRE'},
	  {data:'FAR_FECHA',
                render: function(data, type, row, meta) {
                    if (row.FAR_FECHA!='') {
                        var birthDate = new Date(row.FAR_FECHA);
                        var age = birthDate.getFullYear();
                        var m = birthDate.getMonth();
                        var da = birthDate.getDate();
                        var age = da+"/"+ m+"/"+age;  
                    } else {
                        var age = "Sin Fecha";
                    }
                    return age
                } },
      {data:'PRE_UNIDAD'},
      {data:'COSTO', render: $.fn.dataTable.render.number( ',', '.', 2, 'S/. ' )},
      {defaultContent: "<button class='btn btn-block btn-outline-primary btn-xs'>Ver</button>" }
   ],
   searching: false,
        paging: true,
        orderable: false,
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        select: true,
        ordering: false,
        order: [[3, "ASC"]],
        oLanguage: {"sInfo" : " "},
        keys: true

   });   
   var dtablecuadro = $('#table_cuadro').DataTable( {
   ajax: {
      url: "<?= site_url('analisis/get_cuadro') ?>",
      type: "POST",
      dataSrc:'',
      data: { 
         codcli: function () { return ''; },
         art: function () { return $('input#art').val();  },
         operacion: 20
      }
   },
   columns:[
      {data:'ANIO'},
      {data:'MES'},
      {data:'COMPRAS', render: $.fn.dataTable.render.number( ',', '.', 0, '' ), width: "15%",className: 'dt-body-right'},
      {data:'VENTAS', render: $.fn.dataTable.render.number( ',', '.', 0, '' ), width: "15%",className: 'dt-body-right'},
      {data:'SALIDAS', render: $.fn.dataTable.render.number( ',', '.', 0, '' ), width: "15%",className: 'dt-body-right'},
      {data:'INGRESOS', render: $.fn.dataTable.render.number( ',', '.', 0, '' ), width: "15%",className: 'dt-body-right'}
   ],columnDefs: [
    {
        targets: -1,
        className: 'dt-body-right'
    }
  ],
   searching: false,
        paging: true,
        orderable: false,
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        select: true,
        ordering: false,
        order: [[0, "ASC"],[1, "ASC"]],
        oLanguage: {"sInfo" : " "},
        keys: true

   });   

   $('#table_productos tbody').on( 'click', 'button', function (event) {
      var data = dtable.row( $(this).parents('tr') ).data();
      serie=data['ALL_NUMSER'];
      factura=data['ALL_NUMFAC'];
      fecha=data['ALL_FECHA_PRO'];  
      $('input#art').val(data['ART_KEY']);
      dtablecompras.ajax.reload();
      dtablecuadro.ajax.reload();
      if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
      }
      else {
         dtable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
      }
   });
   $('#table_productos tbody').on('click', 'tr', function () {
   var data = dtable.row($(this)).data();
      serie=data['ALL_NUMSER'];
      factura=data['ALL_NUMFAC'];
      fecha=data['ALL_FECHA_PRO']; 
      $('input#art').val(data['ART_KEY']);   
      dtablecompras.ajax.reload();
      dtablecuadro.ajax.reload();
      if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
      } else {
         dtable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
      }
   });

   $("#b_buscar").click(function () {
      dtable.ajax.reload();           
   }); 


})
   
</script>
<?= $this->endSection(); ?>