<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content">
    <div class="content-fluid">
        <div style="height: 5px;"></div>
        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Items</h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <div class="input-group-prepend">
                                    <button class="btn btn-default">
                                        <b>S/.</b>
                                    </button>
                                </div>
                                <input type="text" id="table_total" name="table_total"
                                    class="form-control float-right" placeholder="Total">
                                <div class="input-group-append">
                                    <button class="btn btn-default">
                                        <i class="fa fa-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-tools -->
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body p-0">
                        <div class="table-responsive mailbox-messages">
                            <table id="tabla_items" class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Producto/Servicio</th>
                                        <th>Cantidad</th>
                                        <th>P.UND</th>
                                        <th>IGV</th>
                                        <th>P.TOT</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <!-- /.table -->
                        </div>
                        <!-- /.mail-box-messages -->
                    </div>
                    <!-- /.card-body -->

                </div>
                <!-- /.card -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary card-outline">
                            <div id="headingOne" class="card-header">
                                <h3 class="card-title">Productos</h3>
                                <div class="card-tools">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="search" class="form-control float-right"
                                            placeholder="Buscar..." data-search>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="d-block d-sm-none btn btn-default"
                                                data-toggle="collapse" data-target="#collapseOne" aria-expanded="true"
                                                aria-controls="collapseOne">
                                                <i class="fa fa-chevron-down pull-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne">
                                <div class="card-body p-1">
                                    <div class="w-100 p-2 row">
                                        <button type="button"
                                            class="filtr-item col-xl-2 col-md-2 col-sm-3 col-4 btn btn-app bg-info active m-0 btn-xs d-none d-sm-block"
                                            data-filter="all">
                                            Todos
                                        </button>
                                        <!--
                                            @foreach ($categorias as $categoria)
                                                <button type="button"
                                                    class="filtr-item col-xl-2 col-md-2 col-sm-3 col-4 btn btn-app bg-info m-0 btn-xs d-none d-sm-block"
                                                    data-filter="<?= 1 //$categoria->cat_id 
                                                                    ?>">
                                                    <?= 1 //$categoria->cat_nombre 
                                                    ?>
                                                </button>
                                            @endforeach -->
                                    </div>
                                    <div class="filter-container p-0 row">
                                        <!--
                                            @foreach ($productos as $producto)
                                                <button type="button"
                                                    class="filtr-item col-xl-2 col-md-2 col-sm-3 col-4 btn btn-app"
                                                    data-category="<?= 1 //$producto->prod_categoria_id 
                                                                    ?>"
                                                    data-sort="<?= 1 //$producto->prod_nombre 
                                                                ?>" data-toggle="tooltip"
                                                    data-placement="top" data-id="<?= 1 //$producto->prod_id 
                                                                                    ?>"
                                                    data-precio="<?= 1 //$producto->prod_precio_publico 
                                                                    ?>"
                                                    data-unidad="<?= 1 //$producto->prod_medida_id 
                                                                    ?>"
                                                    title="S/. <?= 1 //$producto->prod_precio_publico 
                                                                ?> ">

                                                    <?= 1 //$producto->prod_nombre 
                                                    ?>
                                                    <i
                                                        class="btn-sm text-success text-center">S/.<?= 1 //$producto->prod_precio_publico 
                                                                                                    ?></i>
                                                </button>
                                            @endforeach
-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.col -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body">
                        <input id="comprobante_id" name="comprobante_id" type="hidden" value="<?= $comprobante_id ?>">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                                        </div>
                                        <select class="form-control" name="tipo_documento" id="tipo_documento"
                                            required="">
                                            <option data-tclie="">Seleccionar</option>
                                            <option value="1" data-serie="BC01" data-tclie="1">BOLETA </option>
                                            <option value="2" data-serie="FC01" data-tclie="1">FACTURA </option>
                                            <option value="3" data-serie="GC01" data-tclie="1">TICKET </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <div class="input-group ">
                                            <input type="text" class="form-control">
                                            <input type="text" class="form-control" readonly>
                                            <div class="input-group-append">
                                                <div class="input-group-text"><i class="fa fa-user"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.form group -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-10">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-id-badge"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="serie" name="serie"
                                            placeholder="Serie" />
                                        <input type="numeric" class="form-control" id="numero" name="numero"
                                            placeholder="Número" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Date mm/dd/yyyy -->
                        <div class="row">
                            <label for="cmvvend" class="col-sm-2 col-form-label">Paciente</label>
                            <div class="col-sm-10">
                                <select id="b_cliente" name="b_cliente" class="form-control"
                                    style="width: 100%;"></select>
                            </div>
                            <button type="button" id="importar" name="importar" class="btn btn-primary btn-block"><i class="fas fa-cloud-download-alt"></i> Agregar</button>

                        </div>
                        <!-- /.form group -->
                        <!-- Date mm/dd/yyyy -->
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-share-square"></i></span>
                                </div>
                                <input type="text" id='ruc' name='ruc' class="form-control"
                                    placeholder="RUC/DNI">
                            </div>
                        </div>
                        <!-- /.form group -->

                        <!-- phone mask -->
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-home"></i></span>
                                </div>
                                <input type="text" class="form-control" id="domicilio1" name="domicilio1"
                                    placeholder="DIRECCIÓN" />
                            </div>
                            <!-- /.input group -->
                        </div>
                        <!-- /.form group -->
                        <!-- phone mask -->
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                </div>
                                <input type="text" class="form-control" id="telefono_movil_1"
                                    name="telefono_movil_1" placeholder="TELEFONO" />
                                <input type="hidden" id="estado" name="estado" />
                            </div>
                            <!-- /.input group -->
                        </div>
                        <!-- phone mask -->
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-car"></i></span>
                                </div>
                                <input type="text" class="form-control" id="observaciones" name="observaciones"
                                    placeholder="Observaciones" />
                                <input type="hidden" id="estado" name="estado" />
                            </div>
                            <!-- /.input group -->
                        </div>

                    </div>
                    <div class="card-footer">
                        <div class="btn-group" role="group" aria-label="Facturacion">
                            <button type="button" class="btn btn-primary" id="cpguarda_print"><i
                                    class="fa fa-print"></i> Guardar e Imprimir</button>
                            <button type="button" class="btn btn-secondary" id="cpguarda_pdf"><i
                                    class="fa fa-file-pdf"></i> Guardar y Descargar</button>
                            <button type="button" class="btn btn-success" id="cpcancela">Cancelar</button>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.col -->
        </div>
        <!-- Fin Row Principal -->

        <!-- /.modal -->
        <div class="modal fade" id="modal-paciente" style="display: none;" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">DATOS DEL PACIENTE</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                                        </div>
                                        <input type="numeric" class="form-control" id="CLI_CODCLIE" name="CLI_CODCLIE"
                                            placeholder="CODIGO CLIENTE" disabled />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.form group -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-id-badge"></i></span>
                                        </div>
                                        <input type="number" class="form-control" id="CLI_RUC_ESPOSO" name="CLI_RUC_ESPOSO"
                                            placeholder="R.U.C. o D.N.I." autocomplete="off" />
                                    </div>
                                </div>
                                <div class="col-6">
                                    <button type="button" id="importarp" name="importarp" class="btn btn-primary"><i
                                            class="fas fa-cloud-download-alt"></i> Importar</button>
                                </div>
                            </div>
                        </div>
                        <!-- Date mm/dd/yyyy -->
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-user"></i></span>
                                </div>
                                <input type="text" class="form-control" id="CLI_NOMBRE" name="CLI_NOMBRE"
                                    placeholder="NOMBRE Y APELLIDOS O RAZON SOCIAL" />
                            </div>
                            <!-- /.input group -->
                        </div>
                        <!-- /.form group -->
                        <!-- Date mm/dd/yyyy -->
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" id='CLI_FECHA_NAC' name='CLI_FECHA_NAC' class="form-control"
                                    data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask=""
                                    inputmode="numeric" placeholder="FECHA NACIMIENTO">
                            </div>
                        </div>
                        <!-- /.form group -->

                        <!-- phone mask -->
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-home"></i></span>
                                </div>
                                <input type="text" class="form-control" id="CLI_CASA_DIREC" name="CLI_CASA_DIREC"
                                    placeholder="DIRECCIÓN" />
                            </div>
                            <!-- /.input group -->
                        </div>
                        <!-- /.form group -->
                        <!-- phone mask -->
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                </div>
                                <input type="text" class="form-control" id="CLI_TELEF1" name="CLI_TELEF1"
                                    placeholder="TELEFONO" autocomplete="off" />
                                <input type="hidden" id="estado" name="estado" />
                            </div>
                            <!-- /.input group -->
                        </div>
                        <!-- /.form group -->
                        <div class="form-group">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                <input type="checkbox" class="custom-control-input" id="historia" name="historia">
                                <label class="custom-control-label" for="historia">¿Tiene Historia?</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-12 btn-group">

                                    <button type="button" id="seleccionarp" name="seleccionarp" class="btn btn-info"
                                        style="color: var(--white); --fa-animation-duration:2s;"><i class="fas fa-plus"></i>
                                        Seleccionar</button>
                                    <button type="button" id="nuevo" name="nuevo" class="btn btn-primary"><i
                                            class="fas fa-plus"></i> Nuevo</button>
                                    <button type="button" id="editar" name="editar" class="btn btn-warning"><i
                                            class="fas fa-edit"></i> Editar</button>
                                    <button type="button" id="cancelar" name="cancelar" class="btn btn-danger"
                                        data-dismiss="modal"><i class="fas fa-times-circle"></i> Cancelar</button>
                                    <button type="button" id="guardar" name="guardar" class="btn btn-success"><i
                                            class="fas fa-save"></i> Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modal-producto" data-keyboard="false" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="input-group input-group-sm mb-12">
                            <div class="input-group-prepend">
                                <button type="button" class="btn btn-primary">Producto </button>
                            </div>
                            <input type="text" class="form-control" id="busqueda" autocomplete="off" placeholder="Producto (Use '%' para caracter o palabra desconocida)">

                            <span class="input-group-append">
                                <button type="button" id="buscar" name="buscar" class="btn btn-primary"><i class="fa fa-search"> </i> Buscar</button>
                            </span>
                        </div>
                        <input type="hidden" id="imp_ruc_f" name="imp_ruc_f">
                        <input type="hidden" id="imp_cod" name="imp_cod">

                    </div>
                    <div style="margin-left:5px;margin-right:5px;" class="card-body p-0">
                        <table id="productos_centro" class="display compact nowrap dataTable no-footer dtr-inline collapsed table-bordered table-striped" style="width: 100%; background-color:white;">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Productos</th>
                                    <th>Und</th>
                                    <th>Pqt</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>


        <?= $this->endSection(); ?>

        <?= $this->section('footer'); ?>
        <!-- Select2 -->
        <link rel="stylesheet" href="<?= site_url('../../plugins/select2/css/select2.min.css') ?>">
        <link rel="stylesheet" href="<?= site_url('../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">
        <!-- DataTables -->
        <link rel="stylesheet" href="<?= site_url('../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
        <link rel="stylesheet" href="<?= site_url('../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">
        <!-- DataTables -->
        <link rel="stylesheet" href="<?= site_url('../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
        <link rel="stylesheet" href="<?= site_url('../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">
        <link rel="stylesheet" href="<?= site_url('../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css') ?>">
        <link rel="stylesheet" href="<?= site_url('../../plugins/datatables-keytable/css/keyTable.bootstrap4.min.css') ?>">
        <!-- DataTables -->
        <script src="<?= site_url('../../plugins/datatables/jquery.dataTables.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/datatables-buttons/js/dataTables.buttons.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/jszip/jszip.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/pdfmake/pdfmake.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/pdfmake/vfs_fonts.js') ?>"></script>
        <script src="<?= site_url('../../plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/datatables-buttons/js/buttons.print.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/datatables-buttons/js/buttons.colVis.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/datatables-keytable/js/dataTables.keyTable.min.js') ?>"></script>

        <!-- Select2 -->
        <script src="<?= site_url('../../plugins/select2/js/select2.full.min.js') ?>"></script>
        <script src="<?= site_url('../../plugins/bootstrap-switch/js/bootstrap-switch.js') ?>"></script>
        <!-- InputMask -->
        <script src="<?= site_url('../../plugins/inputmask/jquery.inputmask.min.js') ?>"></script>

        <!-- Filterizr -->
        <script src="<?= site_url('../../plugins/filterizr/jquery.filterizr.min.js') ?>"></script>
        <script>
            $(document).ready(function() {
                $(document).on('keydown', function(event) {
                    if (event.key == "Escape" && $('#modal-producto').is(':visible')) {
                        $("#busqueda").val('');
                        $("#busqueda").focus();
                    }
                });
                $(function() {
                    const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                    // Configuración inicial
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        }
                    });
                    $('[data-toggle="tooltip"]').tooltip();

                    // Inicializar Filterizr
                    //const filterizr = new Filterizr('.filter-container', {
                    //    gutterPixels: 3,
                    //    layout: 'sameSize'
                    //});

                    // Funciones auxiliares
                    const showAlert = (title, text, icon, timer = 1500) => {
                        Swal.fire({
                            title,
                            text,
                            icon,
                            showConfirmButton: false,
                            timer
                        });
                    };

                    const calculateItemValues = (quantity, unitPrice) => {
                        const total = quantity * unitPrice;
                        const igv = total - (total / (1 + <?= session('igv') ?>));
                        return {
                            cantidad: quantity,
                            importe: unitPrice,
                            total: total,
                            igv: igv,
                            subtotal: total - igv,
                            precio_base: unitPrice / (1 + <?= session('igv') ?>),
                            descuento: 0
                        };
                    };

                    const updateItem = (data, updatedData) => {
                        $.post("<?= site_url('admin/items/store') ?>", {
                            cod_prod: data.cod_prod,
                            descripcion: data.descripcion,
                            comprobante_id: <?= $comprobante_id ?>,
                            categoria_id: data.categoria_id,
                            unidad_id: data.unidad_id,
                            tipo_igv_id: <?= session('tipo_igv_id') ?>,
                            ...updatedData
                        }, () => dtableReque.ajax.reload());
                    };

                    // Configuración de DataTables
                    const dtableReque = $('#tabla_items').DataTable({
                        rowId: "REQ_CODART",
                        ajax: {
                            url: "<?= site_url('pos/items') ?>",
                            type: "POST",
                            data: {
                                comprobante_id: () => $("#comprobante_id").val()
                            }
                        },
                        columns: [{
                                data: 'cod_prod'
                            },
                            {
                                data: 'ART_NOMBRE'
                            },
                            {
                                data: 'cantidad',
                                render: (data, type, row) =>
                                    `<input type='text' id='CAN_${row.cod_prod}' data-id='${row.cod_prod}' data-local='${row.id_pedido}' class='form-control form-control-sm rowCant' style='width:60px;' value='${row.cantidad}'>`
                            },
                            {
                                data: 'precio',
                                render: (data, type, row) =>
                                    `<input type='text' id='IMP_${row.cod_prod}' data-id='${row.cod_prod}' data-local='${row.id_pedido}' class='form-control form-control-sm rowImporte' style='width:60px;' value='${row.precio}'>`
                            },
                            {
                                data: 'igv',
                                render: (data, type, row) =>
                                    `<input type='text' id='IGV_${row.cod_prod}' data-id='${row.cod_prod}' data-local='${row.id_pedido}' class='form-control form-control-sm rowIgv' style='width:60px;' value='${row.igv}'>`
                            },
                            {
                                data: 'total',
                                render: (data, type, row) =>
                                    `<input type='text' id='TOT_${row.cod_prod}' data-id='${row.cod_prod}' data-local='${row.id_pedido}' class='form-control form-control-sm rowTotal' style='width:60px;' value='${row.total}'>`
                            },
                            {
                                data: 'total',
                                render: () =>
                                    '<a class="item_mas" href="#"><i class="fa fa-plus-circle text-success"></i></a>&nbsp;&nbsp;<a class="item_menos" href="#"><i class="fa fa-minus-circle"></i></a>&nbsp;&nbsp;<a class="item_elim" href="#"><i class="fa fa-trash text-danger"></i></a>'
                            },
                        ],
                        columnDefs: [{
                            targets: [3],
                            className: 'text-left'
                        }],
                        searching: false,
                        paging: false,
                        responsive: true,
                        autoWidth: false,
                        ordering: false,
                        language: {
                            processing: "Actualizando. Espere por favor..."
                        },
                        footerCallback: function(row, data, start, end, display) {
                            const total = this.api().column(5).data().reduce((a, b) => parseFloat(a) +
                                parseFloat(b), 0);
                            $("#table_total").val(total || 0);
                        }
                    });

                    // Event listeners
                    $('.btn[data-filter]').on('click', function(e) {
                        e.preventDefault();
                        $('.btn[data-filter]').removeClass('active');
                        $(this).addClass('active');
                        //const filterValue = $(this).data('filter');
                        //filterizr.filter(filterValue);
                    });

                    $(document).on('keydown', event => {
                        if (event.key === "Escape") {
                            $("[name='search']").val('').focus();
                        }
                    });

                    $('#tipo_documento').change(function() {
                        const tdoc = $(this).val();
                        const newOption = new Option(tdoc != 1 ? 'CLIENTES VARIOS' : 'Seleccione', tdoc != 1 ? 1 :
                            '', false, false);
                        $('#cliente').empty().append(newOption).trigger('change');
                        $('#serie').val($(this).find(':selected').data('serie'));
                        $.post("<?= site_url('admin/series/selecMaximoNumero') ?>", {
                                tipo_documento_id: tdoc
                            },
                            rpta => $('#numero').val(rpta)
                        );
                    });

                    // Configuración de Select2
                    $('#cliente').select2({
                        theme: " form-control",
                        maximumSelectionLength: 10,
                        ajax: {
                            url: "<?= site_url('admin/clientes/select2') ?>",
                            type: 'POST',
                            data: params => ({
                                q: params.term,
                                t: $("#tipo_documento option:selected").attr("data-tclie"),
                            }),
                            processResults: data => ({
                                results: data.data
                            }),
                        },
                        templateSelection: (data, container) => {
                            data.ruc = data.ruc || $("#nruc").val();
                            data.domicilio1 = data.domicilio1 || $("#ndomicilio1").val();
                            data.telefono_movil_1 = data.telefono_movil_1 || $("#ntelefono_movil_1").val();
                            $('#ruc').val(data.ruc);
                            $('#domicilio1').val(data.domicilio1);
                            $('#telefono_movil_1').val(data.telefono_movil_1);
                            return data.text;
                        },
                    });

                    $('.filter-container .filtr-item').on('click', function(e) {
                        e.preventDefault();
                        const productId = $(this).data('id');
                        const productName = $(this).data('sort');
                        const productPrice = $(this).data('precio');
                        const productCategory = $(this).data('category');
                        const productUnit = $(this).data('unidad');

                        addProductToList(productId, productName, productPrice, productCategory, productUnit);
                    });

                    // Función para añadir un producto a la lista
                    function addProductToList(id, name, price, category, unit) {
                        const existingItem = dtableReque.rows().data().filter(row => row.cod_prod == id)[0];
                        const newQuantity = existingItem ? existingItem.cantidad + 1 : 1;
                        const itemData = calculateItemValues(newQuantity, price);

                        $.ajax({
                            url: "<?= site_url('admin/items/store') ?>",
                            type: 'POST',
                            data: {
                                cod_prod: id,
                                descripcion: name,
                                comprobante_id: <?= $comprobante_id ?>,
                                categoria_id: category,
                                unidad_id: unit,
                                tipo_igv_id: <?= session('tipo_igv_id') ?>,
                                ...itemData
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    dtableReque.ajax.reload();
                                } else {
                                    showAlert('Error', 'No se pudo añadir el producto', 'error');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.error('Error al añadir producto:', textStatus, errorThrown);
                                showAlert('Error',
                                    'No se pudo añadir el producto. Por favor, intente de nuevo.', 'error');
                            }
                        });
                    }

                    // Event listeners para la tabla de items
                    $('#tabla_items tbody').on('click', '.item_mas, .item_menos', function() {
                        const data = dtableReque.row($(this).closest('tr')).data();
                        const increment = $(this).hasClass('item_mas') ? 1 : -1;
                        const updatedData = calculateItemValues(Math.max(data.cantidad + increment, 1), data
                            .importe);
                        updateItem(data, updatedData);
                    });

                    $('#tabla_items tbody').on('click', '.item_elim', function() {
                        const data = dtableReque.row($(this).closest('tr')).data();
                        $.post("<?= site_url('admin/items/destroy') ?>", {
                            comprobante_id: <?= $comprobante_id ?>,
                            cod_prod: data.cod_prod
                        }, () => dtableReque.ajax.reload());
                    });

                    $('#tabla_items tbody').on('change', '.rowTotal, .rowImporte, .rowCant', function() {
                        const data = dtableReque.row($(this).closest('tr')).data();
                        const inputValue = parseFloat($(this).val());

                        if (isNaN(inputValue) || inputValue <= 0) {
                            showAlert('Error', 'El valor debe ser un número positivo.', 'error');
                            $(this).val('0');
                            return;
                        }

                        let updatedData;
                        if ($(this).hasClass('rowTotal')) {
                            updatedData = calculateItemValues(data.cantidad, inputValue / data.cantidad);
                        } else if ($(this).hasClass('rowImporte')) {
                            updatedData = calculateItemValues(data.cantidad, inputValue);
                        } else { // rowCant
                            updatedData = calculateItemValues(inputValue, data.importe);
                        }

                        updateItem(data, updatedData);
                    });

                    function handleSuccessResponse(rpta, accion, comp_id) {
                        if (accion === 'print') {
                            $.ajax({
                                url: "http://localhost/printermax/public/api/printer",
                                type: "POST",
                                data: {
                                    datos: JSON.stringify(rpta.datos)
                                },
                                error: function() {
                                    showAlert("Error", "No se pudo conectar con la impresora", "error");
                                }
                            });
                        } else if (accion === 'pdf') {
                            downloadPdf(comp_id);
                        }

                        showAlert("Buenas Noticias", rpta.result.message, "success", 3000);
                        setTimeout(() => location.reload(true), 3000);
                    }

                    function handleErrorResponse(rpta, comp_id) {
                        Swal.fire({
                            title: "Algo salió mal",
                            text: rpta.result.message,
                            icon: "warning",
                            showConfirmButton: true,
                            showCancelButton: true,
                            confirmButtonText: 'Reenviar XML',
                            cancelButtonText: 'Cerrar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                resendXml(comp_id);
                            }
                        });
                    }

                    function handleAjaxError(xhr, status, error, comp_id) {
                        let errorMessage = "Error en la conexión";

                        if (xhr.responseJSON) {
                            errorMessage = xhr.responseJSON.message || "Error del servidor";
                        }

                        if (status === "timeout") {
                            errorMessage = "La operación ha excedido el tiempo de espera";
                        }

                        Swal.fire({
                            title: "Error",
                            text: `${errorMessage}. ¿Desea verificar el estado del comprobante?`,
                            icon: "error",
                            showConfirmButton: true,
                            showCancelButton: true,
                            confirmButtonText: 'Verificar Estado',
                            cancelButtonText: 'Cerrar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                checkInvoiceStatus(comp_id);
                            }
                        });
                    }

                    function resendXml(comp_id) {
                        $.ajax({
                            url: "<?= site_url('admin/comprobantes/resendXml') ?>",
                            type: "POST",
                            data: {
                                comprobante_id: comp_id
                            },
                            success: function(response) {
                                showAlert(
                                    response.success ? "Éxito" : "Error",
                                    response.message,
                                    response.success ? "success" : "error"
                                );
                            },
                            error: function() {
                                showAlert("Error", "No se pudo reenviar el XML", "error");
                            }
                        });
                    }

                    function checkInvoiceStatus(comp_id) {
                        $.ajax({
                            url: "<?= site_url('admin/comprobantes/checkStatus') ?>",
                            type: "POST",
                            data: {
                                comprobante_id: comp_id
                            },
                            success: function(response) {
                                showAlert(
                                    response.success ? "Estado del Comprobante" : "Error",
                                    response.message,
                                    response.success ? "info" : "error"
                                );
                            },
                            error: function() {
                                showAlert("Error", "No se pudo verificar el estado", "error");
                            }
                        });
                    }

                    function downloadPdf(comp_id) {
                        const anchor = document.createElement("a");
                        anchor.href = `<?= site_url('admin/ticket/printpdf') ?>/${comp_id}`;
                        anchor.download = comp_id;
                        document.body.appendChild(anchor);
                        anchor.click();
                        document.body.removeChild(anchor);
                    }
                    // Función para guardar comprobante
                    const saveComprobante = (accion) => {
                        if (parseFloat($("#table_total").val()) == 0) {
                            showAlert('Error', 'El Total tiene que ser mayor a cero!', 'error');
                            return;
                        }
                        if (!$("select#cliente option:checked").val()) {
                            showAlert('Error', 'Seleccionar un cliente!', 'error');
                            return;
                        }
                        if ($("#tipo_documento option:selected").val() == 'Seleccionar') {
                            showAlert('Error', 'Seleccionar un tipo de documento!', 'error');
                            return;
                        }
                        const comp_id = $("#comprobante_id").val();
                        $.ajax({
                            url: "<?= site_url('admin/comprobantes/store') ?>",
                            type: "POST",
                            data: {
                                comprobante_id: comp_id,
                                id_cliente: $("select#cliente option:checked").val(),
                                value_cliente: $("select#cliente option:checked").text(),
                                tipo_documento_id: $("#tipo_documento option:selected").val(),
                                fecha_emision: $("#fecha_emision").data('datepicker').getFormattedDate(
                                    'yyyy-mm-dd'),
                                serie: $("#serie").val(),
                                numero: $("#numero").val(),
                                observaciones: $("#observaciones").val(),
                                accion: accion
                            },
                            dataType: "json",
                            timeout: 30000, // 30 segundos de timeout
                            beforeSend: function() {
                                // Mostrar loading
                                Swal.fire({
                                    title: 'Procesando...',
                                    text: 'Por favor espere mientras se procesa la factura',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    showConfirmButton: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                            },
                            success: function(rpta) {
                                Swal.close();
                                if (rpta.result.success) {
                                    handleSuccessResponse(rpta, accion, comp_id);
                                } else {
                                    handleErrorResponse(rpta, comp_id);
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.close();
                                handleAjaxError(xhr, status, error, comp_id);
                            }
                        });
                    };

                    // Event listeners para guardar comprobante
                    $("#cpguarda_print").click(() => saveComprobante('print'));
                    $("#cpguarda_pdf").click(() => saveComprobante('pdf'));
                    $("#importar").click(async function() {
                        $('.overlay').show();

                        const vruc = $("input#nruc").val().trim();
                        if (!vruc) {
                            Swal.fire('Ingrese un DNI o RUC válido');
                            $('.overlay').hide();
                            return;
                        }
                        try {
                            const response = await $.post("<?= site_url('admin/clientes/getsunat') ?>", {
                                ruc: vruc,
                                tipo: 2
                            });

                            $('.overlay').hide();
                            // Asegúrate de que la respuesta sea un objeto JSON
                            const datos = typeof response === 'string' ? JSON.parse(response) : response;
                            if (datos.error) {
                                Swal.fire('DNI o RUC no válido o no registrado');
                                $("input#nruc").focus();
                            } else {
                                // Actualiza los campos con los datos obtenidos del servidor
                                $("input#nruc").val(datos.ruc);
                                $("input#nrazon_social").val(datos.razon_social);
                                $("input#ndomicilio1").val(datos.direccion);
                                $("input#ntelefono_movil_1").val(datos.telefono);
                                $("input:hidden[name=ncliente_id]").val(datos.id);
                                $("input#nemail").val(datos.email);
                                $('#ntipo_cliente').val(datos.tipo).change();

                                if (datos.estado === 'nuevo') {
                                    Swal.fire('Ingresa los datos faltantes. Luego presione Guardar');
                                } else {
                                    $("input").attr('readonly', true);
                                    Swal.fire(`El Número: ${datos.ruc} ya está registrado!`);
                                }
                            }
                        } catch (error) {
                            $('.overlay').hide();
                            console.error('Error:', error); // Imprime el error para depuración.
                            Swal.fire('Error al consultar la SUNAT. Intente nuevamente.');
                        }
                    });


                    $("#guardarcliente").click(async function() {
                        const razonSocial = $("#nrazon_social").val().trim();
                        const tipoCliente = $("#ntipo_cliente").val();
                        const ruc = $("input#nruc").val().trim();

                        if (!razonSocial) {
                            return Swal.fire("Introduzca su nombre");
                        }
                        if (tipoCliente == 1 && ruc.length !== 8) {
                            return Swal.fire("DNI Incorrecto");
                        }
                        if (tipoCliente == 2 && ruc.length !== 11) {
                            return Swal.fire("RUC Incorrecto");
                        }

                        try {
                            const data = await $.post("<?= site_url('admin/clientes/store') ?>", {
                                "_token": "<?= csrf_token() ?>",
                                cliente_id: $("input:hidden[name=ncliente_id]").val(),
                                tipo_cliente: tipoCliente,
                                value_cliente: $("#ntipo_cliente option:selected").text(),
                                ruc,
                                razon_social: razonSocial,
                                domicilio1: $("input#ndomicilio1").val(),
                                telefono_movil_1: $("input#ntelefono_movil_1").val(),
                                email: $("input#nemail").val()
                            });
                            const newOption = new Option(data.text, data.id, false, false);
                            $('#cliente').empty().append(newOption).trigger('change');
                            $('#modalCliente').modal('hide');
                        } catch (error) {
                            Swal.fire('Error al guardar el cliente. Intente nuevamente.');
                        }
                    });

                    // Configuración del datepicker
                    $('#fecha_emision').datepicker({
                        format: 'dd/mm/yyyy',
                        formatSubmit: 'yyyy-mm-dd hh:mm:ss',
                        startDate: '-3d',
                        autoclose: true
                    }).datepicker("setDate", new Date());

                    // Inicialización de Filterizr
                    //filterizr.filter('all');
                });
            });
        </script>
        <?php $this->endSection(); ?>