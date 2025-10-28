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
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
   }
   
   .table-operaciones tbody tr.selected {
      background-color: #007bff !important;
      color: white !important;
      box-shadow: 0 4px 8px rgba(0,123,255,0.3);
      transform: translateX(4px);
   }
   
   .table-operaciones tbody tr.selected td {
      border-color: #0056b3 !important;
   }
   
   /* Header del comprobante activo */
   .comprobante-activo {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border-radius: 8px;
      margin-bottom: 10px;
      box-shadow: 0 4px 8px rgba(40,167,69,0.2);
      animation: slideIn 0.3s ease;
   }
   
   @keyframes slideIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
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
      border-left: 4px solid #28a745;
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
   
   .btn-eliminar {
      padding: 2px 6px;
      font-size: 0.7rem;
      border-radius: 3px;
      transition: all 0.2s ease;
   }
   
   .btn-eliminar:hover {
      transform: scale(1.1);
      box-shadow: 0 2px 4px rgba(220,53,69,0.3);
   }
   
   /* Card header con información */
   .card-header-info {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border-radius: 8px 8px 0 0;
   }
   
   .card-header-export {
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
      color: white;
      border-radius: 8px 8px 0 0;
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
      background: #28a745;
      animation: pulse 2s infinite;
   }
   
   @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
   }
   
   /* Contador de elementos */
   .contador-elementos {
      background: #20c997;
      border-radius: 15px;
      padding: 2px 8px;
      font-size: 0.8em;
      font-weight: bold;
   }
   
   /* Botones de servidor */
   .btn-servidor {
      margin: 2px;
      transition: all 0.3s ease;
   }
   
   .btn-servidor:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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
   .pagination{
      padding: .85em;
   }
   
   /* Estilos para confirmación de eliminación */
   .confirm-delete {
      background-color: #fff3cd;
      border: 1px solid #ffeaa7;
      border-radius: 4px;
      padding: 10px;
      margin: 10px 0;
   }
</style>

<!-- Content Header (Page header) -->
<div class="content-header">
   <div class="container-fluid">
      <div class="breadcrumb-custom">
         <i class="fas fa-shipping-fast"></i>
         <strong>Exportación de Guías</strong>
         <span class="ml-2 text-muted">
            | Gestiona y exporta guías entre servidores
         </span>
      </div>
   </div>
</div>

<!-- Main content -->
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <!-- Panel Izquierdo - Lista de Guías -->
         <div class="col-lg-7">
            <div class="card">
               <div class="card-header card-header-export">
                  <h5 class="mb-0">
                     <i class="fas fa-truck"></i> Lista de Guías para Exportar
                     <span id="contador-guias" class="contador-elementos float-right">0 registros</span>
                  </h5>
                  <div class="card-tools mt-3">
                     <!-- Botones de servidor -->
                     <div class="row mb-2">
                        <div class="col-12">
                           <div class="btn-group btn-group-sm" role="group">
                              <button type="button" id="caja_cnt" class="btn btn-servidor btn-<?=$color=='success'?$color:'outline-success';?>">
                                 <i class="fa fa-server"></i> Centro
                              </button>
                              <button type="button" id="caja_pmz" class="btn btn-servidor btn-<?=$color=='info'?$color:'outline-info';?>">
                                 <i class="fa fa-warehouse"></i> Almacén
                              </button>
                              <button type="button" id="caja_jjc" class="btn btn-servidor btn-<?=$color=='danger'?$color:'outline-danger';?>">
                                 <i class="fa fa-store"></i> Juanjuicillo
                              </button>
                           </div>
                        </div>
                     </div>
                     <!-- Filtros de búsqueda -->
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
                           <button type="button" id="buscar" class="btn btn-success">
                              <i class="fa fa-search"></i> Buscar
                           </button>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="card-body table-responsive p-0">
                  <table id="operaciones" class="table table-operaciones table-hover table-sm">
                     <thead class="thead-light">
                        <tr>
                           <th><i class="fas fa-calendar"></i> Fecha</th>
                           <th><i class="fas fa-hashtag"></i> Número</th>
                           <th><i class="fas fa-file-alt"></i> Concepto</th>
                           <th><i class="fas fa-dollar-sign"></i> Importe</th>
                           <th><i class="fas fa-truck"></i> Guía</th>
                           <th><i class="fas fa-cog"></i> Acciones</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                  </table>
               </div>
            </div>
         </div>
         
         <!-- Panel Derecho - Detalle de la Guía -->
         <div class="col-lg-5">
            <!-- Header de la Guía Activa -->
            <div id="guia-activa-header" class="no-seleccion">
               <i class="fas fa-info-circle"></i>
               <h6 class="mb-0">Selecciona una guía de la lista para ver su detalle</h6>
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
                           <th><i class="fas fa-sort-numeric-up"></i> CANT</th>
                           <th><i class="fas fa-tag"></i> PRECIO</th>
                           <th><i class="fas fa-calculator"></i> TOTAL</th>
                           <th><i class="fas fa-trash"></i> Eliminar</th>
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

<!-- Modal para migración optimizada -->
<div class="modal fade" id="modal-migracion" style="display: none;" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="overlay">
            <i class="fas fa-2x fa-sync fa-spin"></i>
         </div>
         <div class="modal-header bg-success text-white">
            <h4 class="modal-title">
               <i class="fas fa-shipping-fast"></i> Migración Optimizada de Guía
               <small id="modal-guia-info" class="ml-2"></small>
            </h4>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">×</span>
            </button>
         </div>
         <div class="modal-body">
            <!-- Información de la guía origen -->
            <div class="row">
               <div class="col-md-12">
                  <div class="alert alert-info">
                     <h6><i class="fas fa-info-circle"></i> Información de la Guía Origen</h6>
                     <div class="row">
                        <div class="col-md-4">
                           <strong>Serie:</strong> <span id="info-serie"></span>
                        </div>
                        <div class="col-md-4">
                           <strong>Número:</strong> <span id="info-numero"></span>
                        </div>
                        <div class="col-md-4">
                           <strong>Fecha:</strong> <span id="info-fecha"></span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            
            <!-- Configuración de migración -->
            <div class="row">
               <div class="col-md-6">
                  <div class="form-group">
                     <label for="servidor_origen">
                        <i class="fas fa-server"></i> Servidor Origen
                     </label>
                     <select class="form-control" id="servidor_origen" name="servidor_origen">
                        <option value="1">Principal</option>
                        <option value="2">Server02</option>
                        <option value="3">Server03</option>
                     </select>
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group">
                     <label for="servidor_destino">
                        <i class="fas fa-server"></i> Servidor Destino
                     </label>
                     <select class="form-control" id="servidor_destino" name="servidor_destino">
                        <option value="0" selected>- SELECCIONE SERVIDOR DESTINO -</option>
                        <option value="1">Principal</option>
                        <option value="2">Server02 (Juanjuicillo)</option>
                        <option value="3">Server03 (Peñameza)</option>
                     </select>
                  </div>
               </div>
            </div>
            
            <!-- Campos ocultos para datos de la guía -->
            <input type="hidden" id="fecha_origen" name="fecha_origen">
            <input type="hidden" id="serie_origen" name="serie_origen">
            <input type="hidden" id="factura_origen" name="factura_origen">
            
            <!-- Respuesta de la migración -->
            <div id="respuesta-migracion" class="alert alert-success" style="display: none;">
               <h6><i class="fas fa-check-circle"></i> Resultado de la Migración</h6>
               <div id="mensaje-respuesta"></div>
            </div>
         </div>
         <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
               <i class="fas fa-times"></i> Cerrar
            </button>
            <button type="button" id="ejecutar-migracion" class="btn btn-success">
               <i class="fas fa-shipping-fast"></i> Ejecutar Migración
            </button>
         </div>
      </div>
   </div>
</div>

<!-- Modal de confirmación para eliminar item -->
<div class="modal fade" id="modal-confirmar-eliminar" style="display: none;" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header bg-danger text-white">
            <h4 class="modal-title">
               <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
            </h4>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">×</span>
            </button>
         </div>
         <div class="modal-body">
            <div class="confirm-delete">
               <p><strong>¿Está seguro de que desea eliminar este producto?</strong></p>
               <div id="producto-eliminar-info"></div>
               <p class="text-muted mt-2">
                  <i class="fas fa-info-circle"></i> Esta acción no se puede deshacer.
               </p>
            </div>
            
            <!-- Campos para el stored procedure -->
            <input type="hidden" id="eliminar_fecha" name="eliminar_fecha">
            <input type="hidden" id="eliminar_numfac" name="eliminar_numfac">
            <input type="hidden" id="eliminar_codart" name="eliminar_codart">
         </div>
         <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
               <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="button" id="confirmar-eliminar" class="btn btn-danger">
               <i class="fas fa-trash"></i> Eliminar Producto
            </button>
         </div>
      </div>
   </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<!-- DataTables CSS Core -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">

<!-- DataTables JavaScript Core -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<!-- DateRangePicker -->
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>

<!-- Toast -->
<link rel="stylesheet" href="../../plugins/toastr/toastr.min.css">
<script src="../../plugins/toastr/toastr.min.js"></script>

<script>
   var serie = 0;
   var factura = 0;
   var fecha = moment().format('YYYY-MM-DD');
   var guiaActiva = null; // Variable para almacenar datos de la guía activa
   
   // Función para actualizar el header de la guía activa
   function actualizarGuiaActiva(data) {
      guiaActiva = data;
      
      var headerHtml = `
         <div class="comprobante-activo">
            <div class="p-3">
               <div class="row align-items-center">
                  <div class="col-md-3">
                     <h6 class="mb-1">
                        <i class="fas fa-truck"></i> Guía
                     </h6>
                     <span class="badge badge-light">${data.ALL_NUMSER}-${data.ALL_NUMFAC}</span>
                  </div>
                  <div class="col-md-3">
                     <h6 class="mb-1">
                        <i class="fas fa-calendar"></i> Fecha
                     </h6>
                     <span class="badge badge-light">${moment(data.ALL_FECHA_PRO).format('DD/MM/YYYY')}</span>
                  </div>
                  <div class="col-md-4">
                     <h6 class="mb-1">
                        <i class="fas fa-file-alt"></i> Concepto
                     </h6>
                     <span class="badge badge-light">${data.ALL_CONCEPTO.substring(0, 20)}...</span>
                  </div>
                  <div class="col-md-2">
                     <h6 class="mb-1">
                        <i class="fas fa-dollar-sign"></i> Total
                     </h6>
                     <span class="badge badge-warning">S/. ${data.ALL_IMPORTE_AMORT}</span>
                  </div>
               </div>
               <div class="row mt-2">
                  <div class="col-12">
                     <small>
                        <i class="fas fa-info-circle"></i>
                        Guía seleccionada - Estado: ${data.ALL_CTAG1 > 0 ? 'Exportada' : 'Pendiente'}
                     </small>
                  </div>
               </div>
            </div>
         </div>
      `;
      
      $('#guia-activa-header').html(headerHtml);
   }
   
   // Función para limpiar la selección
   function limpiarGuiaActiva() {
      guiaActiva = null;
      $('#guia-activa-header').html(`
         <div class="no-seleccion">
            <i class="fas fa-info-circle"></i>
            <h6 class="mb-0">Selecciona una guía de la lista para ver su detalle</h6>
         </div>
      `);
   }
   
   // Función para actualizar contadores
   function actualizarContadores() {
      var totalGuias = $('#operaciones').DataTable().data().length;
      $('#contador-guias').text(totalGuias + ' registros');
      
      var totalProductos = $('#table_factura').DataTable().data().length;
      $('#contador-productos').text(totalProductos + ' productos');
   }

   $(document).ready(function() {
      $('#reservation').daterangepicker();

      // Configuración de la tabla de operaciones (guías)
      var dtable = $('#operaciones').DataTable({
         ajax: {
            url: "<?= site_url('operaciones/get_guias') ?>",
            type: "POST",
            dataSrc: '',
            data: {
               fecha: function () { return $("#reservation").val(); },
               factura: function () { return $("#factura").val(); },
               operacion: 5
            },
            complete: function() {
               actualizarContadores();
            }
         },
         columns: [
            { 
               data: 'ALL_FECHA_PRO',
               render: function(data, type, row) {
                  return moment(data).format('DD/MM/YYYY');
               }
            },
            { 
               data: 'ALL_NUMSER',
               render: function(data, type, row) {
                  return `<span class="badge badge-primary">${data}-${row.ALL_NUMFAC}</span>`;
               }
            },
            { 
               data: 'ALL_CONCEPTO',
               render: function(data, type, row) {
                  return data.length > 30 ? data.substring(0, 30) + '...' : data;
               }
            },
            { 
               data: 'ALL_IMPORTE_AMORT',
               render: function(data, type, row) {
                  return '<span class="badge badge-success">S/. ' + parseFloat(data).toFixed(2) + '</span>';
               }
            },
            { 
               data: 'ALL_CTAG1',
               render: function(data, type, row) {
                  if (data > 0) {
                     return '<span class="badge badge-success"><i class="fas fa-check"></i> Exportada</span>';
                  } else {
                     return '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>';
                  }
               }
            },
            { 
               defaultContent: `
                  <div class='btn-group btn-group-sm' role='group'>
                     <button type='button' class='btn btn-primary btn-exportar' title='Exportar Guía'>
                        <i class='fa fa-shipping-fast'></i>
                     </button>
                     <button type='button' class='btn btn-info btn-ver-detalle' title='Ver Detalle'>
                        <i class='fas fa-eye'></i>
                     </button>
                  </div>
               `
            }
         ],
         order: [[0, 'desc']],
         searching: false,
         paging: true,
         responsive: true,
         lengthChange: false,
         autoWidth: false,
      });

      // Manejador de clic en botón Exportar
      $('#operaciones tbody').on('click', '.btn-exportar', function (event) {
         event.stopPropagation();
         
         var data = dtable.row($(this).parents('tr')).data();
         abrirModalMigracion(data);
      });

      // Manejador de clic en botón Ver Detalle
      $('#operaciones tbody').on('click', '.btn-ver-detalle', function (event) {
         event.stopPropagation();
         
         var data = dtable.row($(this).parents('tr')).data();
         seleccionarGuia(data, $(this).parents('tr'));
      });

      // Manejador de clic en fila
      $('#operaciones tbody').on('click', 'tr', function () {
         var data = dtable.row($(this)).data();
         if (data) {
            seleccionarGuia(data, $(this));
         }
      });

      // Función para seleccionar guía
      function seleccionarGuia(data, $row) {
         serie = data['ALL_NUMSER'];
         factura = data['ALL_NUMFAC'];
         fecha = data['ALL_FECHA_PRO'];
         
         actualizarGuiaActiva(data);
         dtablefac.ajax.reload(function() {
            // Forzar redibujado para actualizar los botones
            dtablefac.draw();
         });
         
         dtable.$('tr.selected').removeClass('selected');
         $row.addClass('selected');
         
         if ($(window).width() < 768) {
            $('html, body').animate({
               scrollTop: $('.col-lg-5').offset().top - 20
            }, 500);
         }
      }

      // Función para abrir modal de migración
      function abrirModalMigracion(data) {
         // Verificar si ya está exportada
         if (data['ALL_CTAG1'] > 0) {
            $("#ejecutar-migracion").hide();
            toastr.warning('Esta guía ya ha sido exportada');
         } else {
            $("#ejecutar-migracion").show();
         }
         
         // Llenar información de la guía
         $('#info-serie').text(data['ALL_NUMSER']);
         $('#info-numero').text(data['ALL_NUMFAC']);
         $('#info-fecha').text(moment(data['ALL_FECHA_PRO']).format('DD/MM/YYYY'));
         $('#modal-guia-info').text(`- ${data['ALL_NUMSER']}-${data['ALL_NUMFAC']}`);
         
         // Llenar campos ocultos
         $('#fecha_origen').val(moment(data['ALL_FECHA_PRO']).format('DD/MM/YYYY'));
         $('#serie_origen').val(data['ALL_NUMSER']);
         $('#factura_origen').val(data['ALL_NUMFAC']);
         
         // Resetear selecciones
         $('#servidor_destino').val("0");
         $('#respuesta-migracion').hide();
         
         $('#modal-migracion').modal('show');
         $('.overlay').hide();
      }

      // Manejador del botón buscar
      $("#buscar").click(function () {
         dtable.ajax.reload();
         limpiarGuiaActiva();
      });

      // Configuración de la tabla de factura con botón eliminar
      var dtablefac = $('#table_factura').DataTable({
         ajax: {
            url: "<?= site_url('operaciones/get_operacion') ?>",
            type: "POST",
            dataSrc: '',
            data: { 
               fecha: function () { return moment(fecha).format('DD/MM/YYYY'); },
               serie: function () { return serie; },
               factura: function () { return factura; },
               operacion: 5
            },
            complete: function() {
               actualizarContadores();
            }
         },
         columns: [
            { data: 'FAR_CODART' },
            { 
               data: 'ART_NOMBRE',
               render: function(data, type, row) {
                  return data.length > 25 ? data.substring(0, 25) + '...' : data;
               }
            },
            { data: 'FAR_DESCRI' },
            { 
               data: 'FAR_CANTIDAD',
               render: function(data, type, row) {
                  return parseFloat(data).toFixed(0);
               },
               className: "text-right"
            },
            { 
               data: 'FAR_PRECIO',
               render: function(data, type, row) {
                  return 'S/. ' + parseFloat(data).toFixed(2);
               },
               className: "text-right"
            },
            { 
               data: 'FAR_SUBTOTAL',
               render: function(data, type, row) {
                  return 'S/. ' + parseFloat(data).toFixed(2);
               },
               className: "text-right"
            },
            { 
               data: null,
               render: function(data, type, row) {
                     // Accede a la variable global guiaActiva
                     const isExported = guiaActiva && guiaActiva.ALL_CTAG1 > 0;
                     const disabledAttr = isExported ? 'disabled' : '';
                     return `
                        <button class='btn btn-danger btn-eliminar' ${disabledAttr} title='Eliminar Producto'>
                           <i class='fas fa-trash'></i>
                        </button>
                     `;
               }
            }
         ],
         searching: false,
         paging: false,
         responsive: true,
         lengthChange: false,
         autoWidth: false,
      });

      // Manejador para eliminar producto
      $('#table_factura tbody').on('click', '.btn-eliminar', function () {
         var data = dtablefac.row($(this).parents('tr')).data();
         abrirModalEliminar(data);
      });

      // Función para abrir modal de eliminación
      function abrirModalEliminar(data) {
         var infoHtml = `
            <div class="row">
               <div class="col-md-6"><strong>Código:</strong> ${data.FAR_CODART}</div>
               <div class="col-md-6"><strong>Cantidad:</strong> ${data.FAR_CANTIDAD}</div>
            </div>
            <div class="row mt-2">
               <div class="col-12"><strong>Producto:</strong> ${data.ART_NOMBRE}</div>
            </div>
         `;
         
         $('#producto-eliminar-info').html(infoHtml);
         
         // Llenar campos para el stored procedure
         $('#eliminar_fecha').val(moment(fecha).format('YYYY-MM-DD'));
         $('#eliminar_numfac').val(factura);
         $('#eliminar_codart').val(data.FAR_CODART);
         
         $('#modal-confirmar-eliminar').modal('show');
      }

      // Ejecutar migración optimizada
      $("#ejecutar-migracion").click(function () {
         var servidorDestino = $("#servidor_destino").val();
         
         if (servidorDestino == "0") {
            toastr.error('Debe seleccionar un servidor destino');
            return;
         }
         
         $("#ejecutar-migracion").prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Migrando...');
         
         $.ajax({
            type: "POST",
            url: "<?= site_url('operaciones/migrar_guia_optimizado') ?>",
            data: { 
               servidor_origen: $("#servidor_origen").val(),
               servidor_destino: servidorDestino,
               fecha_origen: $("#fecha_origen").val(),
               serie_origen: $("#serie_origen").val(),
               factura_origen: $("#factura_origen").val()
            },
            dataType: 'json',
            beforeSend: function () {
               $('.overlay').show();
               $('#respuesta-migracion').hide();
            },
            success: function (response) {
               if (response.success) {
                  $('#mensaje-respuesta').html(`
                     <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> ${response.message}
                     </div>
                  `);
                  $('#respuesta-migracion').show();
                  dtable.ajax.reload();
                  toastr.success('Migración completada exitosamente');
               } else {
                  $('#mensaje-respuesta').html(`
                     <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${response.message}
                     </div>
                  `);
                  $('#respuesta-migracion').show();
                  toastr.error('Error en la migración: ' + response.message);
               }
            },
            error: function (xhr, status, error) {
               console.error('Error en migración:', error);
               toastr.error('Error de comunicación con el servidor');
            },
            complete: function () {
               $('.overlay').hide();
               $("#ejecutar-migracion").prop('disabled', false).html('<i class="fas fa-shipping-fast"></i> Ejecutar Migración');
            }
         });
      });

      // Confirmar eliminación de producto
      $("#confirmar-eliminar").click(function () {
    $("#confirmar-eliminar").prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Eliminando...');
    
    // Obtener el servidor (necesitas definir de dónde viene este valor)
    var server = <?=$local?>; 
    
    $.ajax({
        type: "POST",
        url: "<?= site_url('operaciones/eliminar_item') ?>", // URL ajustada al controlador
        data: { 
            fecha: $("#eliminar_fecha").val(),
            numero_factura: $("#eliminar_numfac").val(),
            codigo_articulo: $("#eliminar_codart").val(),
            server: server // Nuevo parámetro requerido
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                $('#modal-confirmar-eliminar').modal('hide');
                dtablefac.ajax.reload();
                toastr.success('Producto eliminado correctamente');
            } else {
                toastr.error('Error al eliminar: ' + response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al eliminar:', error);
            toastr.error('Error de comunicación con el servidor');
        },
        complete: function () {
            $("#confirmar-eliminar").prop('disabled', false).html('<i class="fas fa-trash"></i> Eliminar Producto');
        }
    });
});

      // Manejadores de botones de servidor
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
               toastr.error('Error al cambiar servidor');
            }
         });
      }
   });
</script>
<?= $this->endSection(); ?>