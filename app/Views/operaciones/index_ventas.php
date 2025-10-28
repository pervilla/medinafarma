<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<?php $session = session(); ?>
<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ventas de : </h3>
                        <button type="button" id="caja_cnt" class="btn btn-<?= $color == 'success' ? $color : 'default'; ?> btn-sm"><i class="fa fa-inbox"> </i> Centro</button>
                        <button type="button" id="caja_pmz" class="btn btn-<?= $color == 'info' ? $color : 'default'; ?> btn-sm"><i class="fa fa-inbox"> </i> PMeza</button>
                        <button type="button" id="caja_jjc" class="btn btn-<?= $color == 'danger' ? $color : 'default'; ?> btn-sm"><i class="fa fa-inbox"> </i> Juanjuicillo</button>
                        <input type="hidden" id="LOCAL" name="LOCAL" value="<?= $session->get('caja') ?>">
                        <div class="card-tools">

                            <div class="input-group input-group-sm mb-12">
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
                    <div class="card-body table-responsive">
                        <table id="operaciones" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Num OPer</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Numero</th>
                                    <th>Ven</th>
                                    <th>Concepto</th>
                                    <th>Importe</th>
                                    <th>Total</th>
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
    </div>
    </div>
    <!-- Modal cAJA -->
    <div class="modal fade" id="popComp" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Reporte de Comprobante</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="pdf"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="btnPrint">Imprimir</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal cAJA -->
</section>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/pure.js/2.82/pure.min.js"></script>

<script>
    $(document).ready(function() {
        $('#reservation').daterangepicker();
        var dtable = $('#operaciones').DataTable({
            ajax: {
                url: "<?= site_url('operaciones/get_guias') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    fecha: function() {
                        return $("input#reservation").val();
                    },
                    factura: function() {
                        return $("input#factura").val();
                    },
                    operacion: 10
                }
            },
            processing: true,
            columns: [{
                    data: 'ALL_NUMOPER'
                },
                {
                    data: 'ALL_FECHA_PRO'
                },
                {
                    data: 'ALL_HORA',
                    render: function(data, type, row, meta) {
                        if (row.ALL_HORA != '') {
                            var birthDate = new Date(row.ALL_HORA);
                            var hora = ("0" + birthDate.getHours()).slice(-2);
                            var minuto = ("0" + birthDate.getMinutes()).slice(-2);
                            var segundo = ("0" + birthDate.getSeconds()).slice(-2);

                            var age = hora + ":" + minuto + ":" + segundo;
                        } else {
                            var age = "Sin Hora";
                        }
                        return age
                    }
                },
                {
                    data: 'ALL_NUMSER',
                    render: function(data, type, row, meta) {
                        switch (row.ALL_FBG) {
                            case "B":
                                return "BO" + row.ALL_NUMSER + "-" + row.ALL_NUMFAC;
                                break;
                            case "F":
                                return "FA" + row.ALL_NUMSER + "-" + row.ALL_NUMFAC
                                break;
                            default:
                                return "GI" + row.ALL_NUMSER + "-" + row.ALL_NUMFAC
                        }
                    }
                },
                {
                    data: 'ALL_CODVEN'
                },
                {
                    data: 'ALL_CONCEPTO'
                },
                {
                    data: "ALL_IMPORTE_AMORT",
                    render: $.fn.dataTable.render.number(',', '.', 2, '')
                },
                {
                    data: "ALL_IMPORTE_AMORT",
                    render: $.fn.dataTable.render.number(',', '.', 2, 'S/. ')
                },
                {
                    data: 'ALL_NUMFAC',
                    render: function(data, type, row, meta) {
                        var rpt = '<div class="btn-group"><a href="<?= site_url('comprobante/doc') ?>/' + <?= $local ?> + '/10/' + row.ALL_NUMSER + '/' + row.ALL_NUMFAC + '/' + row.ALL_FECHA_PRO + '" class="btn bg-primary btn-sm"> <i class="fas fa-print"></i> Imprimir</a>';
                        rpt = rpt + '<a id="printcomp" class="btn bg-danger btn-sm" href="<?= site_url('comprobante/createpdf') ?>/' + <?= $local ?> + '/10/' + row.ALL_NUMSER + '/' + row.ALL_NUMFAC + '/' + row.ALL_FECHA_PRO + '" data-comprobante="COMP_' + row.ALL_NUMSER + '-' + row.ALL_NUMFAC + '"><i class="fas fa-file-pdf"></i> Pdf</a>';
                        rpt = rpt + '<a id="vercomp" class="btn bg-warning btn-sm" data-href="<?= site_url('comprobante/verdoc') ?>/' + <?= $local ?> + '/10/' + row.ALL_NUMSER + '/' + row.ALL_NUMFAC + '/' + row.ALL_FECHA_PRO + '" data-comprobante="COMP_' + row.ALL_NUMSER + '-' + row.ALL_NUMFAC + '"><i class="fas fa-eye"></i> Ver</a>';
                        if (row.ALL_NUMFAC == row.FAR_NUMFAC) {
                            rpt = rpt + '<a href="<?= site_url('operaciones/receta') ?>/' + <?= $local ?> + '/10/' + row.ALL_NUMSER + '/' + row.ALL_NUMFAC + '/' + row.ALL_FECHA_PRO + '" class="btn bg-success btn-sm"> <i class="fas fa-photo-video"></i> Receta</a>';

                        }
                        rpt = rpt + "</div>";
                        return rpt
                    }
                },

            ],
            fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                switch (aData.ALL_FBG) {
                    case "G":
                        $(nRow).addClass('bg-secondary');
                        break;
                    case "F":
                        $(nRow).addClass('bg-success');
                        break;
                    default:
                }
                return nRow;
            },
            searching: false,
            order: [
                [0, 'desc']
            ],
            paging: true,
            lengthChange: false,
            autoWidth: false,
            dom: 'Bfrtip',
            responsive: true,
            columnDefs: [{
                    responsivePriority: 1,
                    targets: 0
                },
                {
                    responsivePriority: 10001,
                    targets: 4
                },
                {
                    responsivePriority: 2,
                    targets: -2
                }
            ]
        });
        $("#buscar").click(function() {
            dtable.ajax.reload();
        });

        ///// VER COMPROBANTE /////
        $('#operaciones tbody').on('click', '#vercomp', function(event) {
            var dataURL = $(this).attr('data-href');
            var boleta = $(this).attr('data-comprobante');
            $(document).prop('title', 'Medinafarma ' + boleta);
            $('#popComp').find('.modal-body').load(dataURL, function() {
                $('#popComp').modal({
                    show: true
                });
            });
        });
        ///// IMPRIMIR COMPROBANTE /////
        $("#btnPrint").click(function() {


            var contents = $("#pdf").html();
            var frame1 = $('<iframe />');
            frame1[0].name = "frame1";
            frame1.css({
                "position": "absolute",
                "top": "-1000000px"
            });
            $("body").append(frame1);
            var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
            frameDoc.document.open();
            //Create a new HTML document.

            frameDoc.document.write('<html><head><title>DIV Contents</title>');
            frameDoc.document.write('</head><body>');
            //Append the external CSS file.
            //frameDoc.document.write('<link href="style.css" rel="stylesheet" type="text/css" />');
            //Append the DIV contents.
            frameDoc.document.write(contents);
            frameDoc.document.write('</body></html>');
            frameDoc.document.close();
            setTimeout(function() {

                window.frames["frame1"].focus();
                window.frames["frame1"].print();
                frame1.remove();
            }, 500);


        });

    });


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
                alert('error');
            }
        });
    }
</script>
<?= $this->endSection(); ?>