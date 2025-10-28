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
                    <div class="card-body">
                        <div class="row">
                            <!-- /.col -->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Producto</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="busqueda" placeholder="Producto">
                                    </div>
                                </div>
                                <!-- /.form-group -->
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="buscar" name="buscar" class="btn btn-primary btn-block"><i class="fa fa-search"></i> Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-7">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Centro</h3>
                    </div>
                    <div class="card-body p-0">
                        <table id="productos_centro" class="table table-bordered" style="margin-top: 0px !important;">
                            <thead>
                                <tr>
                                    <th>Codigo</th>
                                    <th>Articulo</th>
                                    <th>Pq/Und</th>
                                    <th>P.U.</th>
                                    <th>P.C</th>
                                    <th>LAB</th>
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
            <div class="col-lg-5">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Stock Otros Locales</h3>
                    </div>
                    <div class="card-body p-0">
                        <table id="productos_medina3" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Local</th>
                                    <th>Articulo</th>
                                    <th>Stock Pq/Und</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- /.card -->
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Equivalentes</h3>
                    </div>
                    <div class="card-body p-0">
                        <table id="productos_equival" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Local</th>
                                    <th>Articulo</th>
                                    <th>Stock Pq/Und</th>
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
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->

    <div class="modal fade" id="modal-overlay">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!--div class="overlay d-flex justify-content-center align-items-center">
                <i class="fas fa-2x fa-sync fa-spin"></i>
            </div-->
                <div class="modal-header">
                    <h4 class="modal-title">Ficha del Producto</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <app-modal-producto-detalle _nghost-c4="">
                        <!---->
                        <div class="modal-body pb-0">
                            <div class="card ">
                                <div class=" row">
                                    <div class="col-sm-8"><label>Nombre:</label><input class="form-control" disabled="" id="nombreProducto" type="text"></div>
                                    <div class="col-sm-4"><label>Precio unitario S/.:</label><input class="form-control" disabled="" name="precio2" type="text"></div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-8"><label>Presentación:</label><input class="form-control" disabled="" id="presentacion" type="text"></div>
                                    <!---->
                                    <div class="col-sm-4 ng-star-inserted"><label>Precio empaque S/.:</label><input class="form-control" disabled="" name="precioV" type="text"></div>
                                </div>
                                <div class=" row">
                                    <div class="col-sm-4"><label>Registro Sanitario:</label><input class="form-control" disabled="" id="registroSanitario" type="text"></div>
                                    <div class="col-sm-4"><label>Concentracion:</label><input class="form-control" disabled="" id="Concent" type="text"></div>
                                    <div class="col-sm-4"><label>Presentacion:</label><input class="form-control" disabled="" id="Presentac" type="text"></div>
                                </div>
                                <div class=" row pb-2">
                                    <div class="col-sm-4"><label>Nombre del Titular:</label><input class="form-control" disabled="" id="nombreTitular" type="text"></div>
                                    <div class="col-sm-4"><label>Nombre del Fabricante:</label><input class="form-control" disabled="" name="nombreFabricante" type="text"></div>
                                    <div class="col-sm-4"><label>País de fabricación:</label><input class="form-control" disabled="" name="paisFabricacion" type="text"></div>
                                </div>
                            </div>
                        </div>
                    </app-modal-producto-detalle>

                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
    <div class="modal fade" id="modal-componentes">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Large Modal</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>One fine body…</p>
                    <select class="js-data-example-ajax"  data-placeholder="Buscar Proveedor" data-allow-clear="1"></select>
                    
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content -->

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
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
    $(document).on('keydown', function(event) {
        if (event.key == "Escape") {
            $("#busqueda").val('');
        }
    });

    $(document).ready(function() {
        var dtable = $('#productos_centro').DataTable({
            ajax: {
                url: "<?= site_url('productos/get_productos') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    busqueda: function() {
                    var busqueda = $("input#busqueda").val().length>0?$("input#busqueda").val():'A';
                        return busqueda;
                    }
                }
            },
            columns: [{
                    data: 'ARM_CODART',
                    orderable: false
                },
                {
                    data: 'ART_NOMBRE',
                    render: function(data, type, row, meta) {
                        return (row.CNTLD == 'C' ? "© " : "") + data;
                    },
                    orderable: false
                },
                {
                    data: 'STOCK'
                },
                {
                    data: 'PRE_UND',
                    render: function(data, type, row, meta) {
                        return "<small class='text-primary'>S/.  </small> " + (Math.round(data * 1000) / 1000).toFixed(2);
                    },
                    orderable: false
                },
                {
                    data: 'PRE_CAJA',
                    render: function(data, type, row, meta) {
                        return "<small class='text-primary'>S/.  </small> " + (Math.round(data * 1000) / 1000).toFixed(2);
                    },
                    orderable: false
                },
                {
                    data: 'TAB_NOMLARGO'
                },
                {
                    data: 'ARM_CODART',
                    render: function(data, type, row, meta) {
                        return '<a href="#" class="text-muted" data-toggle="modal" data-target="#modal-componentes"> <i class="fa fa-flask"></i></a>';
                    }
                }
            ],
            fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                if (aData.StockGen > 0) {
                    $(nRow).addClass('bg-success');
                    if (aData.CNTLD == 'C') {
                        $(nRow).addClass('bg-danger');
                    }
                }
                return nRow;
            },
            order: [
                [1, 'asc']
            ],
            searching: false,
            paging: true,
            orderable: false,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            select: true
        });
        $('#productos_centro').on('click', 'tbody tr', function(event) {
            var data = dtable.row($(this)).data();
            var opci = $("input#RadioStock").val();
            $("#productos_medina2 > tbody").html('');
            $("#productos_medina3 > tbody").html('');
            $("#productos_equival > tbody").html('');
            $.post("<?= site_url('productos/get_stock') ?>", {
                artkey: data['ARM_CODART'],
                artsbg: '',
                local: 3
            }, function(htmlexterno) {
                $("#productos_medina3 > tbody").append(htmlexterno);
            }); /**/
            $.post("<?= site_url('productos/get_stock') ?>", {
                artkey: data['ARM_CODART'],
                artsbg: '',
                local: 2
            }, function(htmlexterno) {
                $("#productos_medina3 > tbody").append(htmlexterno);
            });
            $.post("<?= site_url('productos/get_stock') ?>", {
                artkey: '',
                artsbg: data['ART_SUBGRU'],
                local: 1
            }, function(htmlexterno) {
                $("#productos_equival > tbody").append(htmlexterno);
            });
            dtable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');

            var $bridge = $("<input>")
            $("body").append($bridge);
            $bridge.val(data['ARM_CODART']).select();
            document.execCommand("copy");
            $bridge.remove();

        });



        $('#busqueda').keydown(function(event) {
            var keyCode = (event.keyCode ? event.keyCode : event.which);
            if (keyCode == 13) {
                $('#buscar').trigger('click');
            }
        });

        $("#buscar").click(function() {
            dtable.ajax.reload();
        });



        $('#productos_medina3').on('click', '#addRequer', function(event) {
            $.post(
                "<?= site_url('requerimiento/agregar') ?>", {
                    codart: $(this).attr('data-id'),
                    local: $(this).attr('data-local')
                },
                function(rpta) {
                    const myArray = rpta.split("|");
                    color = myArray[0] == 0 ? 'bg-danger' : 'bg-success';
                    $(document).Toasts('create', {
                        class: color,
                        title: 'Producto',
                        body: myArray[1],
                        position: 'bottomRight',
                        icon: 'far fa-check-circle fa-lg',
                        animation: true,
                        autohide: true,
                        delay: 2500
                    });
                }
            );
        });

        $('#modal-overlay').on('shown.bs.modal', function(e) {
            var id = $(e.relatedTarget).data().id;
            $.post("<?= site_url('productos/get_precios_digemid') ?>", {
                artkey: id
            }, function(htmlexterno) {
                $.each(eval(htmlexterno), function(index, value) {
                    $("#nombreProducto:text").val(value.Nom_Prod);
                    $("#presentacion:text").val(value.Presentac);
                    $("#registroSanitario:text").val(value.Num_RegSan);
                    $("#nombreTitular:text").val(value.Nom_Titular);
                    $("#Concent:text").val(value.Concent);
                    $("#Presentac:text").val(value.Presentac);
                    console.log(value);
                });
            });
            $('#nombreProducto').trigger('focus');
        })

        $('#modal-componentes').on('shown.bs.modal', function(e) {

            var id = $(e.relatedTarget).data().id;
            $('.js-data-example-ajax').select2({
                theme: 'bootstrap4',
        
        placeholder: "Buscar Proveedor",
                ajax: {
                    url: "<?= site_url('productos/get_pa') ?>",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchTerm: params.term // search term
                        };
                    },
                    processResults: function(response) {
                        return {
                            results: response.results
                        };
                    },
                    cache: true
                }
            });
        })


    });
</script>



<?= $this->endSection(); ?>