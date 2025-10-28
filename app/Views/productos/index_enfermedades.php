<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
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
                        <h3 class="card-title">Enfermedades</h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm" style="width: 150px;">
                            <button type="button" id="nuevo_enf" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Agregar</button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body table-responsive p-0">
                        <table id="table_enfermedades" class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Enfermedad</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
            <div class="col-lg-7">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">Detalle</h3>
                        
                    </div>

                    <div class="card-body">
                        <div class="form-group row">
                            <label for="id_enfermedad" class="col-sm-3 col-form-label">Id</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="id_enfermedad" name="id_enfermedad"
                                    value=0 disabled>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="idsistema" class="col-sm-3 col-form-label">Sistema</label>
                            <div id="div_idsistema" class="col-sm-9">
                                <select id="idsistema" name="idsistema" class="form-control">
                                    <option value="" selected="selected">SELECCIONE</option>
                                    <?php foreach($sistemas as $sistema){
                                    echo " <option value='$sistema->ID_SISTEMA'>$sistema->DESCRIPCION</option>";
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="descripcion" class="col-sm-3 col-form-label">Enfermedad</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="descripcion" name="descripcion">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="cuadro" class="col-sm-3 col-form-label">Cuadro</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="cuadro" name="cuadro" rows="4" cols="50"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="nuevo" class="col-sm-3 col-form-label">Medicamentos</label>
                            <div class="col-sm-9">
                                <button type="button" id="nuevo" class="btn btn-primary" style="display: none;"><i class="nav-icon fas fa-pills"></i> Nuevo P.A/F.F</button>
                                <button type="button" id="guardar" class="btn btn-primary" style="display: none;"><i class="nav-icon fas fa-pills"></i> Guardar</button>
                            </div>
                        </div>
                        <div class="form-group row card-body table-responsive">                            
                            <table id="table_medicamentos" class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>CODIGO</th>
                                        <th>PRODUCTO</th>
                                        <th>P.A/F.F</th>
                                        <th>POSOLOGIA</th>
                                        <th> </th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer">
                        Visit <a href="https://github.com/summernote/summernote/">Summernote</a> documentation for more
                        examples and information about the plugin.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-enfermedad">
    <div class="modal-dialog">
        <div class="modal-content bg-success">
            <form role="form" action="form_enfermedad" id="form_enfermedad" method="post">
                <div class="modal-header">
                    <h4 class="modal-title">Nueva P.A/F.F</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                <div class="card-body">
                        <div class="form-group row">
                            <label for="idpa" class="col-sm-3 col-form-label">P.A/F.F</label>
                            <div id="div_idpa" class="col-sm-9">
                                <select id="idpa" name="idpa" class="form-control">
                                    <option value="" selected="selected">SELECCIONE</option>
                                    <?php foreach($pa as $d){
                                    echo " <option value='$d->TAB_NUMTAB'>$d->TAB_NOMLARGO</option>";
                                    } ?>
                                </select>
                            </div>
                        </div>
                    
                    </div>
                        
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
                    <button type="button" id="agregar_pa" value="button"
                        class="btn btn-outline-light">Agregar</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
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
<script>
$(document).ready(function() {

    var dtable = $('#table_enfermedades').DataTable({
        ajax: {
            url: "<?= site_url('productos/get_enfermedades') ?>",
            type: "POST",
            dataSrc: '',
            data: {}
        },
        columns: [
            {data: 'ID_ENFERMEDAD'},
            {data: 'DESCRIPCION'},            
            {data: 'ID_ENFERMEDAD',
                render: function(data, type, row, meta) {
                    var rpt = "<button id='editEnfermedad' data-href='" + row.ID_ENFERMEDAD +
                        "' class='btn bg-primary btn-sm'><i class='fas fa-eye'></i> Ver</button>";
                    return rpt
                }
            }
        ],
        searching: false,
        paging: true,
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        dom: 'frtip'
    });
    var dtableM = $('#table_medicamentos').DataTable({
        ajax: {
            url: "<?= site_url('productos/get_medicamentos') ?>",
            type: "POST",
            dataSrc: '',
            data: {
                id_enfermedad:function () { return $("input#id_enfermedad").val(); }
            }
        },
        columns: [
            {data: 'CODART'},
            {data: 'ART_NOMBRE'},
            {data: 'TAB_NOMLARGO'},
            {data: 'DESCRIPCION'},
            {data: 'DESCRIPCION',
                render: function(data, type, row, meta) {
                    var rpt = "<button id='editEnfermedad' data-href='" + row.CODART +
                        "' class='btn btn-block bg-primary btn-sm'><i class='fas fa-check-square'></i> Ver</button>";
                    return rpt
                }
            }
        ],
        searching: false,
        paging: false,
        orderable: false,
        responsive: false,
        lengthChange: false,
        autoWidth: true,
        select: false,
        keys: {blurable: false}
    });

    $("#nuevo").click(function() {
        $("#modal-enfermedad").modal("show");
    });
    $("#nuevo_enf").click(function() {
        $("input#id_enfermedad").val('');
        $("input#descripcion").val('');
        $("textarea#cuadro").val('');
        $("#idsistema option[value=0]").removeAttr('selected').attr('selected','selected'); 
        $('#guardar').show();
        $('#nuevo').hide();        
        dtableM.clear().draw();
    });
    

    $("#agregar_pa").click(function() {
        $.post('/productos/set_pa_medicamentos', { 
            id_enfermedad:$("input#id_enfermedad").val(),
            id_pa:$("#idpa").val()
        }, function(result){
            dtableM.ajax.reload();
            $("#modal-enfermedad").modal('hide');
            //$("span").html(result);
        });
    });
    $("#guardar").click(function() {
        
        $.post('/productos/set_enfermedad', { 
            id_enfermedad: $("input#id_enfermedad").val(),
            descripcion: $("input#descripcion").val(),
            cuadro: $("textarea#cuadro").val(),
            idsistema: $("#idsistema").val()
        }, function(result){
            dtable.ajax.reload();
            //$("span").html(result);
        });

    });

    

    ///// EDITAR ENFERMEDAD /////
    $('#table_enfermedades tbody').on('click', '#editEnfermedad', function(event) {
        var data = dtable.row($(this).parents('tr')).data();
        $("input#id_enfermedad").val(data['ID_ENFERMEDAD']);
        $("input#descripcion").val($.trim(data['DESCRIPCION']));
        $("textarea#cuadro").val($.trim(data['CUADRO']));
        $("#idsistema option[value="+data['ID_SISTEMA']+"]").removeAttr('selected').attr('selected','selected'); 
        $('#guardar').hide();
        $('#nuevo').show();        
        dtableM.ajax.reload();
    });

});
</script>

<?= $this->endSection(); ?>