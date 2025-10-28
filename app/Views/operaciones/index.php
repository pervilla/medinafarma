<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>

<style>
   @media screen and (max-width: 980px) {
      .desktop {
         display: none;
      }
   }

   /* Mejoras para la selección de filas */
   .table-operaciones tbody tr {
      cursor: pointer;
      transition: all 0.2s ease;
   }

   .table-operaciones tbody tr:hover {
      background-color: #f8f9fa !important;
      transform: translateX(2px);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
   }

   .table-operaciones tbody tr.selected {
      background-color: #007bff !important;
      color: white !important;
      box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
      transform: translateX(4px);
   }

   .table-operaciones tbody tr.selected td {
      border-color: #0056b3 !important;
   }

   /* Header del comprobante activo */
   .comprobante-activo {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      color: white;
      border-radius: 8px;
      margin-bottom: 10px;
      box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
      animation: slideIn 0.3s ease;
   }

   @keyframes slideIn {
      from {
         opacity: 0;
         transform: translateY(-10px);
      }

      to {
         opacity: 1;
         transform: translateY(0);
      }
   }

   .comprobante-activo .badge {
      font-size: 0.9em;
   }

   /* Estado de no selección */
   .no-seleccion {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
      color: white;
      border-radius: 8px;
      margin-bottom: 10px;
      text-align: center;
      padding: 20px;
   }

   /* Breadcrumbs personalizados */
   .breadcrumb-custom {
      background: #f8f9fa;
      border-radius: 6px;
      padding: 8px 15px;
      margin-bottom: 15px;
      border-left: 4px solid #007bff;
   }

   /* Botones mejorados */
   .btn-ver-detalle {
      padding: 2px 8px;
      font-size: 0.75rem;
      border-radius: 4px;
      transition: all 0.2s ease;
   }

   .btn-ver-detalle:hover {
      transform: scale(1.05);
   }

   /* Card header con información */
   .card-header-info {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border-radius: 8px 8px 0 0;
   }

/* Card footer con degradado inverso */
.card-footer-info {
    background: linear-gradient(135deg, #20c997 0%, #28a745 100%); /* Degradado invertido */
    color: white;
    border-radius: 0 0 8px 8px;
    padding: 1rem;
    text-align: right; /* Alineación derecha típica de footers */
    font-size: 0.85rem;
}
   /* Indicador de estado */
   .estado-trabajo {
      position: relative;
      overflow: hidden;
   }

   .estado-trabajo::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: #007bff;
      animation: pulse 2s infinite;
   }

   @keyframes pulse {

      0%,
      100% {
         opacity: 1;
      }

      50% {
         opacity: 0.5;
      }
   }

   /* Contador de elementos */
   .contador-elementos {
      background: rgb(180, 80, 160);
      border-radius: 15px;
      padding: 2px 8px;
      font-size: 0.8em;
      font-weight: bold;
   }

   /* Mejoras responsive */
   @media (max-width: 768px) {
      .comprobante-activo .row {
         text-align: center;
      }

      .comprobante-activo .col-md-3 {
         margin-bottom: 5px;
      }
   }

   div.dataTables_wrapper div.dataTables_info {
      padding: .85em;
   }

   .pagination {
      padding: .85em;
   }
</style>

<!-- Content Header (Page header) -->
<div class="content-header">
   <div class="container-fluid">
      <div class="breadcrumb-custom">
         <i class="fas fa-file-invoice-dollar"></i>
         <strong>Gestión de Comprobantes</strong>
         <span class="ml-2 text-muted">
            | Selecciona un comprobante para ver su detalle
         </span>
      </div>
   </div>
</div>

<!-- Main content -->
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <!-- Panel Izquierdo - Lista de Comprobantes -->
         <div class="col-lg-5">
            <div class="card">
               <div class="card-header card-header-info">
                  <h5 class="mb-0">
                     <i class="fas fa-list"></i> Lista de Comprobantes
                     <span id="contador-comprobantes" class="contador-elementos float-right">0 registros</span>
                  </h5>
                  <div class="card-tools mt-2">
                     <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                           <button type="button" class="btn btn-light">
                              <i class="fas fa-file-invoice"></i> Factura
                           </button>
                        </div>
                        <input type="text" class="form-control" id="factura" placeholder="Número de factura">
                        <div class="input-group-prepend">
                           <span class="input-group-text">
                              <i class="far fa-calendar-alt"></i>
                           </span>
                        </div>
                        <input type="text" class="form-control" id="reservation" placeholder="Rango de fechas">
                        <div class="input-group-append">
                           <button type="button" id="buscar" class="btn btn-warning">
                              <i class="fa fa-search"></i> Buscar
                           </button>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="card-body table-responsive p-0">
                  <table id="table_operaciones" class="table table-operaciones table-hover table-sm">
                     <thead class="thead-light">
                        <tr>
                           <th><i class="fas fa-hashtag"></i> Serie</th>
                           <th><i class="fas fa-file-invoice"></i> Número</th>
                           <th><i class="fas fa-calendar"></i> Fecha</th>
                           <th><i class="fas fa-user"></i> Proveedor</th>
                           <th><i class="fas fa-dollar-sign"></i> Total</th>
                           <th><i class="fas fa-eye"></i> Acción</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                  </table>
               </div>
               <div class="card-footer card-footer-info">
                  
                  <h5 class="mb-0">
                     Gestión de Precios Temporales <i class="fas fa-calculator"></i> 
                  </h5>
                     <button id="btn-aplicar-precios" class="btn btn-warning me-2">
                        <i class="bi bi-plus-circle"></i> Aplicar Precios Temporales
                     </button>

                     <button id="btn-eliminar-precios" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar Precios Temporales
                     </button>

                     <div id="resultado" class="mt-3"></div>
                  
               </div>
            </div>
         </div>


         <!-- Panel Derecho - Detalle del Comprobante -->
         <div class="col-lg-7">
            <!-- Header del Comprobante Activo -->
            <div id="comprobante-activo-header" class="no-seleccion">
               <i class="fas fa-info-circle"></i>
               <h6 class="mb-0">Selecciona un comprobante de la lista para ver su detalle</h6>
            </div>

            <div class="card estado-trabajo">
               <div class="card-header">
                  <h5 class="mb-0">
                     <i class="fas fa-list-ul"></i> Detalle de Productos
                     <span id="contador-productos" class="contador-elementos float-right">0 productos</span>
                  </h5>
               </div>
               <div class="card-body table-responsive p-0">
                  <table id="table_factura" class="table table-striped table-hover table-sm">
                     <thead class="thead-dark">
                        <tr>
                           <th><i class="fas fa-barcode"></i> COD</th>
                           <th><i class="fas fa-box"></i> PRODUCTO</th>
                           <th><i class="fas fa-ruler"></i> UND</th>
                           <th><i class="fas fa-tag"></i> PRECIO</th>
                           <th><i class="fas fa-sort-numeric-up"></i> CANT</th>
                           <th><i class="fas fa-calculator"></i> SUB</th>
                           <th><i class="fas fa-dollar-sign"></i> PRECIO</th>
                           <th><i class="fas fa-chart-line"></i> MARGEN</th>
                           <th><i class="fas fa-cog"></i> Acción</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>


<!-- Modal para porcentaje -->
<div class="modal fade" id="porcentajeModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Aplicar Precios Temporales</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPorcentaje">
               <div class="modal-body">
                  <div class="mb-3">
                        <label for="inputPorcentaje" class="form-label">Porcentaje de Ganancia:</label>
                        <input type="number" step="0.01" class="form-control" id="inputPorcentaje" required>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary">Aplicar</button>
               </div>
            </form>
      </div>
   </div>
</div>
<!-- Modal para precios -->
<div class="modal fade" id="modal-xl" style="display: none;" aria-hidden="true">
   <div class="modal-dialog modal-xl">
      <div class="modal-content">
         <div class="overlay">
            <i class="fas fa-2x fa-sync fa-spin"></i>
         </div>
         <div class="modal-header bg-primary text-white">
            <h4 class="modal-title">
               <i class="fas fa-tags"></i> Gestión de Precios
               <small id="modal-producto-info" class="ml-2"></small>
            </h4>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">×</span>
            </button>
         </div>
         <div class="modal-body">
            <div class="row">
               <div class="col-md-6">
                  <div class="input-group mb-3">
                     <div class="input-group-prepend">
                        <button type="button" class="btn btn-info">
                           <i class="fas fa-barcode"></i> CODIGO
                        </button>
                     </div>
                     <input type="text" class="col-md-2" id="PRE_CODART" name="PRE_CODART" class="form-control">
                     <input type="text" id="PRE_NOMBRE" name="PRE_NOMBRE" class="form-control">
                  </div>
               </div>
            </div>
            <!-- Headers de la tabla de precios -->
            <div class="input-group mb-3" style="margin-bottom:0px!important;">
               <div class="input-group-prepend">
                  <span class="input-group-text bg-secondary text-white">
                     <i class="fas fa-table"></i>
                  </span>
               </div>
               <input type="text" class="form-control" value="Unidades" disabled>
               <input type="text" class="form-control" value="Equiv." disabled>
               <input type="text" class="form-control" value="Rep." disabled>
               <input type="text" class="form-control" value="(%)" disabled>
               <input type="text" class="form-control" value="Prec.1" disabled>
               <input type="text" class="form-control desktop" value="(%)" disabled>
               <input type="text" class="form-control desktop" value="Prec.2" disabled>
               <input type="text" class="form-control desktop" value="(%)" disabled>
               <input type="text" class="form-control desktop" value="Prec.3" disabled>
               <input type="text" class="form-control desktop" value="(%)" disabled>
               <input type="text" class="form-control desktop" value="Prec.4" disabled>
               <input type="text" class="form-control desktop" value="(%)" disabled>
               <input type="text" class="form-control desktop" value="Prec.5" disabled>
            </div>

            <!-- Filas de datos -->
            <div id="cost_1" class="input-group mb-3" style="margin-bottom:0px!important;">
               <div class="input-group-prepend">
                  <span class="input-group-text bg-success text-white">
                     <i class="fas fa-layer-group"></i>
                  </span>
               </div>
               <input type="text" class="form-control" id="PRE_UNIDAD_1" name="PRE_UNIDAD_1" disabled>
               <input type="text" class="form-control" id="PRE_EQUIV_1" name="PRE_EQUIV_1" disabled>
               <input type="text" class="form-control" id="ARM_COSPRO_1" name="ARM_COSPRO_1" onclick="modificaCosto()" readonly="readonly">
               <input type="text" class="form-control" id="PRE_POR1_1" name="PRE_POR1_1">
               <input type="text" class="form-control" id="PRE_PRE1_1" name="PRE_PRE1_1">
               <input type="text" class="form-control desktop" id="PRE_POR2_1" name="PRE_POR2_1">
               <input type="text" class="form-control desktop" id="PRE_PRE2_1" name="PRE_PRE2_1">
               <input type="text" class="form-control desktop" id="PRE_POR3_1" name="PRE_POR3_1">
               <input type="text" class="form-control desktop" id="PRE_PRE3_1" name="PRE_PRE3_1">
               <input type="text" class="form-control desktop" id="PRE_POR4_1" name="PRE_POR4_1">
               <input type="text" class="form-control desktop" id="PRE_PRE4_1" name="PRE_PRE4_1">
               <input type="text" class="form-control desktop" id="PRE_POR5_1" name="PRE_POR5_1">
               <input type="text" class="form-control desktop" id="PRE_PRE5_1" name="PRE_PRE5_1">
            </div>

            <div id="cost_2" class="input-group mb-3" style="margin-bottom:0px!important;">
               <div class="input-group-prepend">
                  <span class="input-group-text bg-info text-white">
                     <i class="fas fa-layer-group"></i>
                  </span>
               </div>
               <input type="text" class="form-control" id="PRE_UNIDAD_2" name="PRE_UNIDAD_2" disabled>
               <input type="text" class="form-control" id="PRE_EQUIV_2" name="PRE_EQUIV_2" disabled>
               <input type="text" class="form-control" id="ARM_COSPRO_2" name="ARM_COSPRO_2" onclick="modificaCosto()" readonly="readonly">
               <input type="text" class="form-control" id="PRE_POR1_2" name="PRE_POR1_2">
               <input type="text" class="form-control" id="PRE_PRE1_2" name="PRE_PRE1_2">
               <input type="text" class="form-control desktop" id="PRE_POR2_2" name="PRE_POR2_2">
               <input type="text" class="form-control desktop" id="PRE_PRE2_2" name="PRE_PRE2_2">
               <input type="text" class="form-control desktop" id="PRE_POR3_2" name="PRE_POR3_2">
               <input type="text" class="form-control desktop" id="PRE_PRE3_2" name="PRE_PRE3_2">
               <input type="text" class="form-control desktop" id="PRE_POR4_2" name="PRE_POR4_2">
               <input type="text" class="form-control desktop" id="PRE_PRE4_2" name="PRE_PRE4_2">
               <input type="text" class="form-control desktop" id="PRE_POR5_2" name="PRE_POR5_2">
               <input type="text" class="form-control desktop" id="PRE_PRE5_2" name="PRE_PRE5_2">
            </div>
         </div>
         <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
               <i class="fas fa-times"></i> Cerrar
            </button>
            <button type="button" id="SavePrecios" class="btn btn-success">
               <i class="fas fa-save"></i> Guardar Cambios
            </button>
         </div>
      </div>
   </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<!-- ============================================= -->
<!-- LIBRERÍAS MÍNIMAS NECESARIAS PARA DATATABLES -->
<!-- ============================================= -->

<!-- DataTables CSS Core (obligatorio) -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">

<!-- DataTables JavaScript Core (obligatorio) -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

<!-- DataTables Responsive (para dispositivos móviles) -->
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<!-- DateRangePicker (para el filtro de fechas) -->
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>

<!-- Toast -->
<link rel="stylesheet" href="../../plugins/toastr/toastr.min.css">
<script src="../../plugins/toastr/toastr.min.js"></script>
<!-- ============================================= -->
<!-- LIBRERÍAS ELIMINADAS (ya no necesarias) -->
<!-- ============================================= -->
<!-- 
❌ ELIMINADAS - Ya no necesarias al quitar dom: 'Bfrtip':

CSS:
- datatables-buttons/css/buttons.bootstrap4.min.css

JS:
- datatables-buttons/js/dataTables.buttons.min.js
- datatables-buttons/js/buttons.bootstrap4.min.js  
- datatables-buttons/js/buttons.html5.min.js
- datatables-buttons/js/buttons.print.min.js
- datatables-buttons/js/buttons.colVis.min.js
- datatables-select/js/dataTables.select.min.js
- Editor/js/dataTables.editor.min.js
- jszip/jszip.min.js (para exportar Excel)
- pdfmake/pdfmake.min.js (para exportar PDF)
- pdfmake/vfs_fonts.js (para exportar PDF)
- datatables/jquery.dataTables.min.css (duplicado)
-->

<script>
   var editor;
   var serie = 0;
   var factura = 0;
   var fecha = moment().format('DD/MM/YYYY');
   var comprobanteActivo = null; // Variable para almacenar datos del comprobante activo

   // Función para actualizar el header del comprobante activo
   function actualizarComprobanteActivo(data) {
      comprobanteActivo = data;

      var headerHtml = `
         <div class="comprobante-activo">
            <div class="p-3">
               <div class="row align-items-center">
                  <div class="col-md-3">
                     <h6 class="mb-1">
                        <i class="fas fa-file-invoice"></i> Comprobante
                     </h6>
                     <span class="badge badge-light">${data.ALL_NUMSER}-${data.ALL_NUMFAC}</span>
                  </div>
                  <div class="col-md-3">
                     <h6 class="mb-1">
                        <i class="fas fa-calendar"></i> Fecha
                     </h6>
                     <span class="badge badge-light">${data.ALL_FECHA_PRO}</span>
                  </div>
                  <div class="col-md-4">
                     <h6 class="mb-1">
                        <i class="fas fa-user"></i> Proveedor
                     </h6>
                     <span class="badge badge-light">${data.CLI_NOMBRE}</span>
                  </div>
                  <div class="col-md-2">
                     <h6 class="mb-1">
                        <i class="fas fa-dollar-sign"></i> Total
                     </h6>
                     <span class="badge badge-warning">${data.ALL_IMPORTE_AMORT}</span>
                  </div>
               </div>
               <div class="row mt-2">
                  <div class="col-12">
                     <small>
                        <i class="fas fa-info-circle"></i>
                        Trabajando en este comprobante - Los cambios se aplicarán aquí
                     </small>
                  </div>
               </div>
            </div>
         </div>
      `;

      $('#comprobante-activo-header').html(headerHtml);
   }

   // Función para limpiar la selección
   function limpiarComprobanteActivo() {
      comprobanteActivo = null;
      $('#comprobante-activo-header').html(`
         <div class="no-seleccion">
            <i class="fas fa-info-circle"></i>
            <h6 class="mb-0">Selecciona un comprobante de la lista para ver su detalle</h6>
         </div>
      `);
   }

   // Función para actualizar contadores
   function actualizarContadores() {
      // Contador de comprobantes
      var totalComprobantes = $('#table_operaciones').DataTable().data().length;
      $('#contador-comprobantes').text(totalComprobantes + ' registros');

      // Contador de productos
      var totalProductos = $('#table_factura').DataTable().data().length;
      $('#contador-productos').text(totalProductos + ' productos');
   }

   $(document).ready(function() {
      const modal = new bootstrap.Modal(document.getElementById('porcentajeModal'));
        const resultado = $('#resultado');
      $('#reservation').daterangepicker();

      // Configuración de la tabla de operaciones OPTIMIZADA
      var dtable = $('#table_operaciones').DataTable({
         ajax: {
            url: "<?= site_url('operaciones/get_operaciones') ?>",
            type: "POST",
            dataSrc: '',
            data: {
               fecha: function() {
                  return $("#reservation").val();
               },
               factura: function() {
                  return $("#factura").val();
               },
               operacion: 20
            },
            complete: function() {
               actualizarContadores();
            }
         },
         columns: [{
               data: 'ALL_NUMSER'
            },
            {
               data: 'ALL_NUMFAC'
            },
            {
               data: 'ALL_FECHA_PRO'
            },
            {
               data: 'CLI_NOMBRE',
               render: function(data, type, row) {
                  return data.length > 25 ? data.substring(0, 25) + '...' : data;
               }
            },
            {
               data: 'ALL_IMPORTE_AMORT',
               render: function(data, type, row) {
                  return '<span class="badge badge-success">$' + data + '</span>';
               }
            },
            {
               defaultContent: "<button class='btn btn-primary btn-ver-detalle'><i class='fas fa-eye'></i></button>"
            }
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
      });

      // Manejador de clic en botón Ver
      $('#table_operaciones tbody').on('click', 'button', function(event) {
         event.stopPropagation(); // Evitar que se propague al tr

         var data = dtable.row($(this).parents('tr')).data();
         seleccionarComprobante(data, $(this).parents('tr'));
      });

      // Manejador de clic en fila
      $('#table_operaciones tbody').on('click', 'tr', function() {
         var data = dtable.row($(this)).data();
         if (data) {
            seleccionarComprobante(data, $(this));
         }
      });

      // Función para seleccionar comprobante
      function seleccionarComprobante(data, $row) {
         // Actualizar variables globales
         serie = data['ALL_NUMSER'];
         factura = data['ALL_NUMFAC'];
         fecha = data['ALL_FECHA_PRO'];

         // Actualizar header del comprobante activo
         actualizarComprobanteActivo(data);

         // Recargar tabla de factura
         dtablefac.ajax.reload();

         // Manejar selección visual
         dtable.$('tr.selected').removeClass('selected');
         $row.addClass('selected');

         // Scroll suave al panel derecho en móviles
         if ($(window).width() < 768) {
            $('html, body').animate({
               scrollTop: $('.col-lg-7').offset().top - 20
            }, 500);
         }
      }

      // Manejador del botón buscar
      $("#buscar").click(function() {
         dtable.ajax.reload();
         limpiarComprobanteActivo(); // Limpiar selección al buscar
      });

      // Configuración de la tabla de factura OPTIMIZADA
      var dtablefac = $('#table_factura').DataTable({
         ajax: {
            url: "<?= site_url('operaciones/get_operacion') ?>",
            type: "POST",
            dataSrc: '',
            data: {
               fecha: function() {
                  return fecha;
               },
               serie: function() {
                  return serie;
               },
               factura: function() {
                  return factura;
               },
               operacion: 20
            },
            complete: function() {
               actualizarContadores();
            }
         },
         columns: [{
               data: 'FAR_CODART'
            },
            {
               data: 'ART_NOMBRE',
               render: function(data, type, row) {
                  return data.length > 30 ? data.substring(0, 30) + '...' : data;
               }
            },
            {
               data: 'FAR_DESCRI'
            },
            {
               data: 'FAR_PRECIO',
               render: function(data, type, row) {
                  return '$' + parseFloat(data).toFixed(2);
               }
            },
            {
               data: 'FAR_CANTIDAD'
            },
            {
               data: 'FAR_SUBTOTAL',
               render: function(data, type, row) {
                  return '$' + parseFloat(data).toFixed(2);
               }
            },
            {
               data: 'PRE_PRE1',
               render: function(data, type, row) {
                  return '$' + parseFloat(data || 0).toFixed(2);
               }
            },
            {
               data: 'MARGEN',
               render: function(data, type, row, meta) {
                  var valor = Math.round(data * 1000) / 1000;
                  var clase = data > 10 ? 'text-success' : data < 0 ? 'text-danger' : 'text-warning';
                  var icono = data > 10 ? 'fa-arrow-up' : data < 0 ? 'fa-arrow-down' : 'fa-arrow-right';
                  return `<small class="${clase}"><i class="fas ${icono}"></i> ${valor}%</small>`;
               }
            },
            {
               defaultContent: "<button class='btn btn-info btn-ver-detalle'><i class='fas fa-edit'></i></button>"
            }
         ],
         order: [
            [1, 'asc'],
            [2, 'asc']
         ],
         rowGroup: {
            dataSrc: 5
         },
         searching: false,
         paging: false,
         responsive: true,
         lengthChange: false,
         autoWidth: false,

      });

      // Manejadores para la tabla de factura (sin cambios en la funcionalidad original)
      $('#table_factura tbody').on('click', 'button', function() {
         var data = dtablefac.row($(this).parents('tr')).data();
         abrirModalPrecios(data);
      });

      $('#table_factura tbody').on('click', 'tr', function() {
         var data = dtablefac.row($(this)).data();
         if (data) {
            abrirModalPrecios(data);
         }
      });

      // Función para abrir modal de precios
      function abrirModalPrecios(data) {
         // Actualizar título del modal con información del producto
         $('#modal-producto-info').text(`- ${data.ART_NOMBRE}`);

         $("#modal-xl").modal('show');

         $.ajax({
            type: "POST",
            url: "<?= site_url('operaciones/get_precios_edit') ?>",
            data: {
               key: data['FAR_CODART']
            },
            dataType: 'json',
            beforeSend: function() {
               $('.overlay').show();
            },
            success: function(datos) {
               $("#cost_1 input[type=text]").each(function() {
                  this.value = ''
               });
               $("#cost_2 input[type=text]").each(function() {
                  this.value = ''
               });
               $.each(datos, function(i, item) {
                  var index = parseInt(i) + 1;
                  $("#PRE_CODART").val(item.PRE_CODART);
                  $("#PRE_NOMBRE").val(data['ART_NOMBRE']);
                  $("#PRE_UNIDAD_" + index).val(item.PRE_UNIDAD);
                  $("#PRE_EQUIV_" + index).val(item.PRE_EQUIV);
                  $("#ARM_COSPRO_" + index).val(item.ARM_COSPRO);
                  $("#PRE_POR1_" + index).val(item.PRE_POR1);
                  $("#PRE_PRE1_" + index).val(item.PRE_PRE1);
                  $("#PRE_POR2_" + index).val(item.PRE_POR2);
                  $("#PRE_PRE2_" + index).val(item.PRE_PRE2);
                  $("#PRE_POR3_" + index).val(item.PRE_POR3);
                  $("#PRE_PRE3_" + index).val(item.PRE_PRE3);
                  $("#PRE_POR4_" + index).val(item.PRE_POR4);
                  $("#PRE_PRE4_" + index).val(item.PRE_PRE4);
                  $("#PRE_POR5_" + index).val(item.PRE_POR5);
                  $("#PRE_PRE5_" + index).val(item.PRE_PRE5);
               });
            },
            complete: function() {
               $('.overlay').hide();
            }
         });
      }

      // Manejador del botón SavePrecios (sin cambios)
      $("#SavePrecios").click(function() {
         // 🔍 Validación básica antes de enviar
         var codigo = $("#PRE_CODART").val().trim();

         if (!codigo) {
            if (typeof toastr !== 'undefined') {
               toastr.error('Código de artículo requerido');
            } else {
               alert('Error: Código de artículo requerido');
            }
            return;
         }

         var data1 = $("#cost_1 input[type=text]").serialize();
         var data2 = $("#cost_2 input[type=text]").serialize();

         // Validar que hay datos para enviar
         if (!data1 && !data2) {
            if (typeof toastr !== 'undefined') {
               toastr.error('No hay datos de precios para actualizar');
            } else {
               alert('Error: No hay datos de precios para actualizar');
            }
            return;
         }

         // 📊 Debug: mostrar datos que se van a enviar
         console.log('📤 Enviando datos:', {
            codigo: codigo,
            data1: data1,
            data2: data2
         });

         $.ajax({
            type: "POST",
            url: "<?= site_url('operaciones/set_precios') ?>",
            data: {
               codi: codigo,
               dat1: data1,
               dat2: data2
            },
            dataType: 'json',
            timeout: 15000, // 15 segundos timeout
            beforeSend: function() {
               $('.overlay').show();
               // Deshabilitar botón para evitar doble envío
               $("#SavePrecios").prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            },
            success: function(response) {
               console.log('✅ Respuesta del servidor:', response);

               // Verificar si la respuesta tiene la estructura esperada
               if (response && typeof response === 'object') {

                  if (response.success === true) {
                     // ✅ ÉXITO: recargar tabla y cerrar modal
                     console.log('🔄 Recargando tabla...');
                     dtablefac.ajax.reload();

                     console.log('❌ Cerrando modal...');
                     $("#modal-xl").modal('hide');

                     // Mostrar notificación de éxito
                     var mensaje = response.message || 'Precios actualizados correctamente';
                     if (typeof toastr !== 'undefined') {
                        toastr.success(mensaje);
                     } else {
                        alert('✅ ' + mensaje);
                     }

                     // Log adicional si hay detalles
                     if (response.details && response.details.length > 0) {
                        console.log('📋 Detalles de actualización:', response.details);
                     }

                  } else {
                     // ❌ ERROR en la operación
                     console.error('❌ Error en operación:', response);
                     var errorMsg = response.message || 'Error desconocido al actualizar precios';

                     if (typeof toastr !== 'undefined') {
                        toastr.error(errorMsg);
                     } else {
                        alert('❌ Error: ' + errorMsg);
                     }

                     // Mostrar errores específicos en consola
                     if (response.errors && response.errors.length > 0) {
                        console.error('🔍 Errores específicos:', response.errors);
                     }
                     if (response.error_detail) {
                        console.error('🔍 Detalle del error:', response.error_detail);
                     }
                  }

               } else {
                  // Respuesta no es un objeto JSON válido
                  console.error('❌ Respuesta inválida del servidor:', response);
                  if (typeof toastr !== 'undefined') {
                     toastr.error('Respuesta inválida del servidor');
                  } else {
                     alert('Error: Respuesta inválida del servidor');
                  }
               }
            },
            error: function(xhr, status, error) {
               console.error('💥 Error AJAX completo:', {
                  status: status,
                  error: error,
                  responseText: xhr.responseText,
                  statusCode: xhr.status,
                  readyState: xhr.readyState
               });

               var errorMessage = 'Error de comunicación con el servidor';

               // Mensajes específicos según el tipo de error
               switch (status) {
                  case 'timeout':
                     errorMessage = '⏱️ Tiempo de espera agotado. Intenta de nuevo.';
                     break;
                  case 'parsererror':
                     errorMessage = '📄 Error al procesar respuesta del servidor';
                     console.error('Respuesta que causó parsererror:', xhr.responseText);
                     break;
                  case 'abort':
                     errorMessage = '🚫 Operación cancelada';
                     break;
                  default:
                     if (xhr.status === 500) {
                        errorMessage = '🔧 Error interno del servidor (500)';
                     } else if (xhr.status === 404) {
                        errorMessage = '🔍 Servicio no encontrado (404)';
                     } else if (xhr.status === 403) {
                        errorMessage = '🔒 Acceso denegado (403)';
                     } else if (xhr.status === 0) {
                        errorMessage = '🌐 Sin conexión al servidor';
                     }
               }

               if (typeof toastr !== 'undefined') {
                  toastr.error(errorMessage);
               } else {
                  alert('❌ ' + errorMessage);
               }

               // Si hay respuesta del servidor, mostrarla en consola
               if (xhr.responseText) {
                  console.log('📄 Respuesta completa del servidor:', xhr.responseText);
               }
            },
            complete: function() {
               // Siempre ocultar overlay y restaurar botón
               $('.overlay').hide();
               $("#SavePrecios").prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');
               console.log('🏁 Operación completada');
            }
         });
      });

      // Manejadores de teclado para cost_2 (sin cambios)
      $("#cost_2 input[type=text]").keyup(function(event) {
         var id = event.currentTarget.id;
         var vl = event.currentTarget.value;
         var cl = id.split("_");
         var v1 = cl[1].substring(0, 3);
         var v2 = cl[1].substring(3, 4);

         if (v1 == 'POR') {
            var pre = $("#ARM_COSPRO_" + cl[2]).val() * (1 + (vl / 100));
            $("#PRE_PRE" + v2 + '_2').val(pre);
            $("#PRE_PRE" + v2 + '_1').val(pre / $("#PRE_EQUIV_2").val()).keyup();
         } else if (v1 == 'PRE') {
            var por = (vl * 100) / ($("#ARM_COSPRO_" + cl[2]).val()) - 100;
            $("#PRE_POR" + v2 + '_2').val(por);
            $("#PRE_POR" + v2 + '_1').val(por).keyup();
         }
      }).keydown(function(event) {
         if (event.which == 13) {
            event.preventDefault();
         }
      });

      // Manejadores de teclado para cost_1 (sin cambios)
      $("#cost_1 input[type=text]").keyup(function(event) {
         var id = event.currentTarget.id;
         var vl = event.currentTarget.value;
         var cl = id.split("_");
         var v1 = cl[1].substring(0, 3);
         var v2 = cl[1].substring(3, 4);

         if (v1 == 'POR') {
            var pre = $("#ARM_COSPRO_" + cl[2]).val() * (1 + (vl / 100));
            $("#PRE_PRE" + v2 + '_1').val(pre);
         } else if (v1 == 'PRE') {
            var por = (vl * 100) / ($("#ARM_COSPRO_" + cl[2]).val()) - 100;
            $("#PRE_POR" + v2 + '_1').val(por);
         }
      }).keydown(function(event) {
         if (event.which == 13) {
            event.preventDefault();
         }
      });

      // Mostrar modal para aplicar precios
        $('#btn-aplicar-precios').click(function() {
            $('#inputPorcentaje').val('');
            modal.show();
        });
        
        // Enviar formulario de porcentaje
        $('#formPorcentaje').submit(function(e) {
            e.preventDefault();
            const porcentaje = parseFloat($('#inputPorcentaje').val());
            
            if (isNaN(porcentaje) || porcentaje <= 0) {
                alert('Por favor ingrese un porcentaje válido mayor que cero');
                return;
            }
            
            $.ajax({
                url: '<?= site_url('operaciones/aplicarPrecios') ?>',
                type: 'POST',
                data: { porcentaje: porcentaje },
                dataType: 'json',
                beforeSend: function() {
                    resultado.html('<div class="alert alert-info">Procesando...</div>');
                },
                success: function(response) {
                    if (response.success) {
                        resultado.html(`<div class="alert alert-primary">${response.message}</div>`);
                        modal.hide();
                    } else {
                        resultado.html(`<div class="alert alert-danger">${response.message}</div>`);
                    }
                },
                error: function() {
                    resultado.html('<div class="alert alert-danger">Error en la comunicación con el servidor</div>');
                }
            });
        });
        
        // Eliminar precios temporales
        $('#btn-eliminar-precios').click(function() {
            if (!confirm('¿Está seguro que desea eliminar todos los precios temporales?')) {
                return;
            }
            
            $.ajax({
                url: '<?= site_url('operaciones/eliminarPrecios') ?>',
                type: 'POST',
                dataType: 'json',
                beforeSend: function() {
                    resultado.html('<div class="alert alert-info">Procesando...</div>');
                },
                success: function(response) {
                    resultado.html(`<div class="alert ${response.success ? 'alert-primary' : 'alert-danger'}">${response.message}</div>`);
                },
                error: function() {
                    resultado.html('<div class="alert alert-danger">Error en la comunicación con el servidor</div>');
                }
            });
        });

   });

   // Función modificaCosto (sin cambios)
   function modificaCosto() {
      var costo = $("#ARM_COSPRO_1").val();
      var ncost = prompt("Ingrese Nuevo Costo", costo);
      if (ncost != null) {
         $.ajax({
            type: "POST",
            url: "<?= site_url('operaciones/set_costo') ?>",
            data: {
               key: $("#PRE_CODART").val(),
               costo: ncost
            },
            dataType: 'json',
            success: function(datos) {},
            complete: function() {
               $("#ARM_COSPRO_1").val(ncost);
               $("#PRE_EQUIV_2").val() > 0 ? $("#PRE_EQUIV_2").val() * ncost : '';
            }
         });
      }
   }


</script>
<?= $this->endSection(); ?>