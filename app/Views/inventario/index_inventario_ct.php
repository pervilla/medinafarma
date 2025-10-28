<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="content-header">
    </div>
    <div class="row">
        <div class="col-6">
            <div id="caja_inventarios" class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">INVENTARIOS</h3>                    
                </div>
                <div class="card-body table-responsive p-0">
                    <input id="inv_id" type="hidden" value="0">
                    <input id="inv_lc" type="hidden" value="<?=$id_local?>">
                    <table id="table_inventarios" class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>DESCRIPCION</th>
                                <th>ESTADO</th>
                                <th style="width: 10px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventarios as $inv) : ?>
                                <tr>
                                    <td><?= $inv->inv_id ?></td>
                                    <td><?= $inv->inv_descripcion ?></td>
                                    <td><?= $inv->inv_estado ?></td>
                                    <td><button type="button" class="btn btn-default binventario" data-idinv="<?=$inv->inv_id?>" data-idloc="<?=$inv->inv_local?>" data-itloc="<?=$inv->inv_total_items?>">ver</button></td>
                                </tr>
                            <?php endforeach ?>

                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#nuevoInventario">Nuevo </button>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div id="caja_responsables" class="card shadow">
                <div class="card-header">
                    <h3 class="card-title">RESPONSABLES</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm mb-0">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="nav-icon fas fa-pills"></i> Total de Items</span></div>
                            <input type="number" class="form-control" name="inv_total_items" id="inv_total_items">                            
                            <input type="text" class="form-control" value="Asignados" readonly="">
                            <input type="number" class="form-control" name="inv_asign_items" id="inv_asign_items">
                            <input type="text" class="form-control" value="Partes" readonly="">
                            <input type="number" class="form-control" name="inv_prop_items" id="inv_prop_items">
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="table_responsables" class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>RESPONSABLE</th>
                                <th>PROPORCION</th>
                                <th>CANTIDAD</th>
                                <th style="width: 10px"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#nuevoResponsable">Agregar Responsables</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <?php foreach ($responsables as $user) : 
            $avance = $user->tavance*100 / $user->tregistros;
            $pagina = $user->tavance+1;
            ?>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $user->VEM_CODVEN ?></h3>
                        <p><?= $user->VEM_NOMBRE ?></p>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" style="width: <?=$avance<=5 ? 5 : $avance ?>%;" aria-valuenow="<?= $avance ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $avance; ?>%</div>
                        </div>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="<?= site_url('inventario/lista/' . $id_local . '/' . $user->inv_id . '/' . $user->VEM_CODVEN . '/' . $user->tregistros . '/' . $pagina ) ?>" class="btn btn-primary btn-block small-box-footer">
                    <i class="fas fa-arrow-circle-right"></i> Seleccionar</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="nuevoInventario" tabindex="-1" role="dialog" aria-labelledby="nuevoInventarioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-success">
            <form role="form" action="<?= site_url('inventario/crear_inventario') ?>" id="set_inventario" method="post">
                <div class="modal-header">
                    <h4 class="modal-title">Nueva Inventario</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="idlocal" class="col-sm-3 col-form-label">Local</label>
                            <div class="col-sm-9">
                                <select id="idlocal" name="idlocal" class="form-control">
                                    <option value="" selected="selected">SELECCIONE</option>
                                    <option value="1">LOCAL CENTRAL</option>
                                    <option value="2">JUANJUICILLO</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inv_descripcion" class="col-sm-3 col-form-label">Descripción</label>
                            <div class="col-sm-9">
                                <input type="text" id="inv_descripcion" name="inv_descripcion" class="text">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-outline-light">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /.modal -->
<!-- Modal -->
<div class="modal fade" id="nuevoResponsable" tabindex="-1" role="dialog" aria-labelledby="nuevoResponsableLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-success">
            <form role="form" action="<?= site_url('inventario/crear_responsable') ?>" id="set_responsable" method="post">
                <div class="modal-header">
                    <h4 class="modal-title">Nuevo Responsable</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card-body">
                        <div class="form-group row" id="dv_vem_codven">
                            <label for="vem_codven" class="col-sm-2 col-form-label">Trabaj</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="vem_codven" name="vem_codven">
                                    <?php foreach ($empleados as $empleado) { ?>
                                        <option value="<?= $empleado->VEM_CODVEN; ?>"><?= $empleado->VEM_CODVEN . ' - ' . trim($empleado->VEM_NOMBRE); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-outline-light" id="bset_responsable">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /.modal -->

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
    $(document).ready(function() {
        var dtableResponsables = $('#table_responsables').DataTable({
            ajax: {
                url: "<?= site_url('inventario/listar_responsables') ?>",
                type: "POST",
                dataSrc: '',
                data: {inv_id: function() {return $("#inv_id").val();}}
            },
            processing: true,
            columns: [
                {data: 'VEM_NOMBRE'},
                {data: 'inr_proporcion',render: function(data, type, row, meta) {
                    return "<input type='text' id='PROP_"+row.inr_id+"' data-id='"+row.inr_id+"' class='form-control form-control-sm addProp' style='width:60px;' value='"+data+"'>"
                }},
                {data: 'inr_cantidad',render: function(data, type, row, meta) {
                    return "<input type='text' id='CANT_"+row.inr_id+"' data-id='"+row.inr_id+"' class='form-control form-control-sm addCant' style='width:60px;' value='"+data+"'>"
                }},
                {data: 'inr_id',render: function() {return "<button type='button' id='delmov' class='btn btn-outline-primary btn-block'><i class='fa fa-trash'></i></button>"}},
            ],
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            paging: false,
            ordering: false,
            info: false,
            bFilter: false,
            searching: false,
            language: {
                processing: "Actualizando.</br>Espere por favor..."
            },
            footerCallback: function(row, data, start, end, display) {
                var total = this.api().column(2).data().reduce(function(a, b) {
                    return parseFloat(a) + parseFloat(b);
                }, 0);
                $("#inv_asign_items").val(total ? total : 0);
                var partes = this.api().column(1).data().reduce(function(a, b) {
                    return parseFloat(a) + parseFloat(b);
                }, 0);
                $("#inv_prop_items").val(partes ? partes : 0);
                
            }

        });
        $('#table_responsables tbody').on('change', '.addProp', function () { /* input de cantidad */
            var data = dtableResponsables.row($(this).parents('tr')).data();
            vl = $(this).val();
            if(vl>0){
                $.post("<?= site_url('inventario/actualizaDistribucion') ?>", {
                    inv_id: data.inv_id,
                    inv_cv: data.vem_codven,
                    inv_vl: vl,
                    inv_ti: $("#inv_total_items").val(),
                    inv_lc: $("#inv_lc").val()
                }, 
                    function(rpta) {
                        const myArray = rpta.split("|");
                        color = myArray[0]==0?'bg-danger':'bg-success';
                        $(document).Toasts('create', {
                            class: color,
                            title: 'Inventario',
                            body: myArray[1],
                            position: 'bottomRight',
                            icon: 'far fa-check-circle fa-lg',
                            animation:	true,
                            autohide: true,
                            delay:	2500
                        });
                    }
                );
            }else{            
                $.alert({
                    title: 'Error',
                    content: '<i class="fa-solid fa-face-sad-tear"></i> La proporcion debe ser mayor a 1.',
                    type: 'red',
                });
                $(this).val('0');
            }       
        });
        $(".binventario").click(function() {
            $("#inv_id").val($(this).data('idinv'));
            $("#inv_lc").val($(this).data('idloc'));
            $("#inv_total_items").val($(this).data('itloc'));
            dtableResponsables.ajax.reload();
        });
        $("#bset_responsable").click(function() {
            $('#nuevoResponsable').modal('hide')
            $.post("<?= site_url('inventario/agregar_responsables') ?>", {
                invlc: $("#inv_lc").val(),
                idinv:  $("#inv_id").val(),
                vem_codven: $("#vem_codven").val()
            }, function (htmlexterno) {
                dtableResponsables.ajax.reload();
            });
        });
    });
</script>
<?= $this->endSection(); ?>