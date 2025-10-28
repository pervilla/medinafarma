<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->
<!-- Main content -->
<?php
//echo $CIT_CODCAMP;
?>
<!-- Main content -->
<section class="content">
    <div class="row">
    <div class="col-md-3">

<div class="card card-widget widget-user-2 bg-success shadow">
    <div class="widget-user-header">
        <div class="widget-user-image">
            <img class="img-circle elevation-2" src="../dist/img/logomedina.png" alt="User Avatar">
        </div>
        <h3 class="widget-user-username">CENTRO</h3>
        <h5 class="widget-user-desc">Jr. Huallaga 601</h5>
    </div>
    <div class="card-footer">
        <ul class="nav flex-column">
            <li class="nav-item">
                <div class="form-group">
                    <div class="row">
                        <div class="col-12 btn-group">
                            <button type="button" id="inscribir_cita" name="inscribir_cita" data-id='1' data-toggle="modal" data-target="#modal-producto" class="btn btn-primary" style="color: var(--white); --fa-animation-duration:2s;"><i class="fas fa-marker"></i>Registrar</button>
                            <button type="button" data-id='1' class="btn btn-secondary ver_lista" style="color: var(--white); --fa-animation-duration:2s;"><i class="fas fa-address-book"></i> Ver Lista</button>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>

<div class="card card-widget widget-user-2 bg-info shadow">
    <div class="widget-user-header">
        <div class="widget-user-image">
            <img class="img-circle elevation-2" src="../dist/img/juanjuicillo.jpg" alt="User Avatar">
        </div>
        <h3 class="widget-user-username">PEÑAMEZA</h3>
        <h5 class="widget-user-desc">Jr. E.Peñameza 436</h5>
    </div>
    <div class="card-footer">
        <ul class="nav flex-column">
            <li class="nav-item">
                <div class="form-group">
                    <div class="row">
                        <div class="col-12 btn-group">
                            <button type="button" id="inscribir_cita" name="inscribir_cita" data-id='3' data-toggle="modal" data-target="#modal-producto" class="btn btn-primary" style="color: var(--white); --fa-animation-duration:2s;"><i class="fas fa-marker"></i>Registrar</button>
                            <button type="button" data-id='3' class="btn btn-secondary ver_lista" style="color: var(--white); --fa-animation-duration:2s;"><i class="fas fa-address-book"></i> Ver Lista</button>                                                                            
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>

<div class="card card-widget widget-user-2 bg-danger shadow">
    <div class="widget-user-header">
        <div class="widget-user-image">
            <img class="img-circle elevation-2" src="../dist/img/juanjuicillo.jpg" alt="User Avatar">
        </div>
        <h3 class="widget-user-username">JUANJUICILLO</h3>
        <h5 class="widget-user-desc">Jr. Miguel Grau 248</h5>
    </div>
    <div class="card-footer">
        <ul class="nav flex-column">
            <li class="nav-item">
                <div class="form-group">
                    <div class="row">
                        <div class="col-12 btn-group">
                            <button type="button" id="inscribir_cita" name="inscribir_cita" data-id='2' data-toggle="modal" data-target="#modal-producto" class="btn btn-primary" style="color: var(--white); --fa-animation-duration:2s;"><i class="fas fa-marker"></i>Registrar</button>
                            <button type="button" data-id='2' class="btn btn-secondary ver_lista" style="color: var(--white); --fa-animation-duration:2s;"><i class="fas fa-address-book"></i> Ver Lista</button>                                                                            
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>
       








    
        

    

    </div>
    <!-- /.col -->
    <div class="col-md-9">
        <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Inbox</h3>

            <div class="card-tools">
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" placeholder="Search Mail">
                <div class="input-group-append">
                <div class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </div>
                </div>
            </div>
            </div>
            <!-- /.card-tools -->
        </div>
        <!-- /.card-header -->
        <div class="card-body p-0">
            <div class="mailbox-controls">
            <!-- Check all button 
            <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i>
            </button>
            <div class="btn-group">
                <button type="button" class="btn btn-default btn-sm">
                <i class="far fa-trash-alt"></i>
                </button>
                <button type="button" class="btn btn-default btn-sm">
                <i class="fas fa-reply"></i>
                </button>
                <button type="button" class="btn btn-default btn-sm">
                <i class="fas fa-share"></i>
                </button>
            </div>-->
            <!-- /.btn-group -->
            <button type="button" class="btn btn-default btn-sm actualizar">
                <i class="fas fa-sync-alt"></i>
            </button>
            <div class="float-right">
                <!--1-50/200
                <div class="btn-group">
                <button type="button" class="btn btn-default btn-sm">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-default btn-sm">
                    <i class="fas fa-chevron-right"></i>
                </button>
                </div>
                 /.btn-group -->
            </div>
            <!-- /.float-right -->
            </div>
            <div class="table-responsive mailbox-messages">
            <table id="tabla_requerimientos" class="table table-hover table-striped">
            <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Productos</th>
                            <th>Stock Centro</th>
                            <th>Req UND</th>
                            <th>Req CANT</th>
                        </tr>
                    </thead>
                <tbody></tbody>
            </table>
            <!-- /.table -->
            </div>
            <!-- /.mail-box-messages -->
        </div>
        <!-- /.card-body -->
        <div class="card-footer p-0">
            <div class="mailbox-controls">
            <!-- Check all button 
            <button type="button" class="btn btn-default btn-sm checkbox-toggle">
                <i class="far fa-square"></i>
            </button>
            <div class="btn-group">
                <button type="button" class="btn btn-default btn-sm">
                <i class="far fa-trash-alt"></i>
                </button>
                <button type="button" class="btn btn-default btn-sm">
                <i class="fas fa-reply"></i>
                </button>
                <button type="button" class="btn btn-default btn-sm">
                <i class="fas fa-share"></i>
                </button>
            </div>-->
            <!-- /.btn-group -->
            <button type="button" class="btn btn-default btn-sm actualizar">
                <i class="fas fa-sync-alt"></i>
            </button>
            <?php $session = session(); if($session->get('user_id')=='ADMIN'){?>  
            <button type="button" id="generar_guia" class="btn btn-dark btn-sm">
                <i class="fas fa-cogs"></i> Generar Guia de Salida
            </button>
            <button type="button" id="generar_ingreso" class="btn btn-dark btn-sm">
                <i class="fas fa-cogs"></i> Generar Guia de ingreso
            </button>
                <?php } ?>
            <div class="float-right">
                <!-- 1-50/200
                <div class="btn-group">
                <button type="button" class="btn btn-default btn-sm">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-default btn-sm">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                

                </div>
                /.btn-group -->
            </div>
            <!-- /.float-right -->
            </div>
        </div>
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
    </div>
    <!-- /.row -->
</section>
<!-- /.content -->



<!-- Modal -->
<div class="modal fade" id="modal-producto" data-keyboard="false" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="input-group input-group-sm mb-12">
                    <select id="cmb_local" name="cmb_local" class="form-control">
                        <option value="0" selected="selected">SELECCIONE</option>
                        <option value="1">CENTRO</option>
                        <option value="2">JUANJUICILLO</option>
                        <option value="3">PEÑAMEZA</option>                        
                    </select>
                    <div class="input-group-prepend">
                        <button type="button" class="btn btn-primary">Producto </button>
                    </div>
                    <input type="text" class="form-control" id="busqueda" autocomplete="off" placeholder="Producto (Use '%' para caracter o palabra desconocida)">
                    
                    <span class="input-group-append">
                        <button type="button" id="buscar" name="buscar" class="btn btn-primary"><i class="fa fa-search"> </i> Buscar</button>
                    </span>
                </div>
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
<link rel="stylesheet" href="../../plugins/select2/css/select2.min.css" />
<link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css" />
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css" />
<link rel="stylesheet" href="<?= site_url('../../plugins/datatables-keytable/css/keyTable.dataTables.min.css') ?>">
<link rel="stylesheet" href="../../plugins/jquery-confirm/css/jquery-confirm.css">
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
<script src="<?= site_url('../../plugins/datatables-keytable/js/dataTables.keyTable.min.js') ?>"></script>
<script src="../../plugins/jquery-confirm/js/jquery-confirm.js"></script>

<!-- Select2 -->
<script src="../../plugins/select2/js/select2.full.min.js"></script>
<script src="../../plugins/bootstrap-switch/js/bootstrap-switch.js"></script>
<!-- InputMask -->
<script src="../../plugins/inputmask/jquery.inputmask.min.js"></script>

<script>
$(document).on('keydown', function(event) {
   if (event.key == "Escape" && $('#modal-producto').is(':visible')) {    
        $( "#busqueda" ).val('');
        $( "#busqueda" ).focus();
   }
});
$(document).ready(function() {
    var local = 0;
    $('#modal-producto').on('shown.bs.modal', function(e) {
        var local = $(e.relatedTarget).data('id'); 

        if(local=='1'){
            $('.modal-header,.modal-footer').removeClass('bg-danger').removeClass('bg-info').addClass('bg-success'); 
        }else if(local=='2'){
            $('.modal-header,.modal-footer').removeClass('bg-success').removeClass('bg-info').addClass('bg-danger'); 
        }else if(local=='3'){
            $('.modal-header,.modal-footer').removeClass('bg-success').removeClass('bg-danger').addClass('bg-info'); 
        }
        $('#cmb_local').val(local).change();
        $('#busqueda').trigger('focus');
    });

    var dtable = $('#productos_centro').DataTable({
        ajax: {
            url: "<?= site_url('productos/get_productos') ?>",
            type: "POST",
            dataSrc: '',
            data: {
                busqueda: function() {
                    return $("input#busqueda").val();
                }
            }
        },
        columns: [
            {data: 'ARM_CODART',orderable: false},
            {data: 'ART_NOMBRE'},            
            {data: 'ART_PQT',
                render: function(data, type, row, meta) {
                    return (Math.round(data * 1000) / 1000);
                },
                orderable: false
            },
            {data: 'ART_UNID',
                render: function(data, type, row, meta) {
                    return (Math.round(data * 1000) / 1000);
                },
                orderable: false
            },
            {data: 'ARM_CODART',
                render: function(data, type, row, meta) {
                    return "<button id='selprod' class='btn btn-block bg-gradient-primary btn-xs'><i class='fas fa-pills'></i> Seleccionar</button>"
                }
            }

        ],
        fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            if (aData.StockGen > 0) {
                $(nRow).addClass('bg-success');
            }
            return nRow;
        },
        searching: false,
        paging: true,
        orderable: false,
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        select: true,
        ordering: false,
        oLanguage: {"sInfo" : " "},
        keys: true,
        //dom: 'Bfrt<"col-md-6 inline"i> <"col-md-6 inline"p>',

    });


    dtable.on( 'key', function ( e, datatable, key, cell, originalEvent ) {        
        if(key==13){
            var rowData = datatable.row( cell.index().row ).data();    
            $.post(
            "<?= site_url('requerimiento/agregar') ?>", {
                codart: rowData['ARM_CODART'],
                local: $( "select#cmb_local option:checked" ).val()
            },
            function(rpta) {
                const myArray = rpta.split("|");
                color = myArray[0]==0?'bg-danger':'bg-success';
                $(document).Toasts('create', {
                    class: color,
                    title: 'Producto',
                    body: myArray[1],
                    position: 'bottomRight',
                    icon: 'far fa-check-circle fa-lg',
                    animation:	true,
                    autohide: true,
                    delay:	2500
                });
                dtableReque.ajax.reload();
            }
            );
        }
        if(key==27){
            $( "#busqueda" ).focus();
        }
    } );
    
    var dtableReque = $('#tabla_requerimientos').DataTable({
        rowId: "REQ_CODART",
        ajax: {
            url: "<?= site_url('requerimiento/listaReq') ?>",
            type: "POST",
            dataSrc: '',
            data: {
                local: function() {
                    return local;
                }
            }
        },
        processing: true,
        columns: [
            {data: 'REQ_CODART'},
            {data: 'ART_NOMBRE'},
            {data: 'ARM_STOCK',
                render: function(data, type, row, meta) {
                    var valores = row.REQ_UNIDADES.split(',');
                    var nva = 0;
                    var cja = 0;
                    var und = 0;
                    $.each(valores, function( index, value ) {
                        val = value.split('|');
                        if(val[1]>nva){
                            nva = val[1]; 
                            dta = val[0];
                        }                                             
                    });
                    col = data>0?'bg-success':'bg-secondary';
                    cja = Math.floor(data/nva);
                    und = Math.floor(data%nva);  
                    return cja+"/"+und+" <span class='badge "+col+" float-right'>"+dta+"</span>"
                }
            },
            {data: 'REQ_UNIDADES',
                render: function(data, type, row, meta) {
                    var valores = data.split(',');
                    var rpta = "<select id='CMB_"+row.REQ_CODART+"' data-id='"+row.REQ_CODART+"' data-local='"+row.REQ_LOCAL+"' class='form-control form-control-sm selUnd' style='width:80px; float:right;'>";
                    $.each(valores, function( index, value ) {
                        val = value.split('|');
                        selected = val[1]==row.REQ_EQUIV?'selected':'';
                        rpta+='<option value="'+val[1]+'" '+selected+'>'+val[0]+'</option>';
                    });
                    rpta+='</select>';

                    return rpta;
                }
            },
            {data: 'REQ_CANT',
                render: function(data, type, row, meta) {
                    val = data/row.REQ_EQUIV;
                    return "<input type='text' id='INP_"+row.REQ_CODART+"' data-id='"+row.REQ_CODART+"' data-local='"+row.REQ_LOCAL+"' class='form-control form-control-sm addField' style='width:60px;' value='"+val+"'>"
                }
            },
          			
        ],
        columnDefs: [{ targets: [3], className: 'text-right' }],
        searching: false,
        paging: false,
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        ordering: false,
        language: {processing: "Actualizando.</br>Espere por favor..."},
    });


    $('#tabla_requerimientos tbody').on('change', '.addField', function () { /* input de cantidad */
        var data = dtableReque.row($(this).parents('tr')).data();
        vl = $(this).val();
        id = $(this).data('id');
        lc = $(this).data('local');
        un = $('#CMB_'+id).val();
        if(data.ARM_STOCK>=(vl*un)){
             $.post("<?= site_url('requerimiento/actualizaReq') ?>", {tram:un+'-'+vl+'-'+id+'-'+lc}, /* und-val-idprod-local */
                function(rpta) {
                    const myArray = rpta.split("|");
                    color = myArray[0]==0?'bg-danger':'bg-success';
                    $(document).Toasts('create', {
                        class: color,
                        title: 'Requerimiento',
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
                content: '<i class="fa-solid fa-face-sad-tear"></i> Cantidad mayor al Stock existente.',
                type: 'red',
            });
            $(this).val('0');
        }       
    });
    $('#tabla_requerimientos tbody').on('change', '.selUnd', function () { /* select de unidad */
        var data = dtableReque.row($(this).parents('tr')).data();
        un = $(this).val();
        vl = $('#INP_'+id).val();
        id = $(this).data('id');
        lc = $(this).data('local');        
        if(data.ARM_STOCK>=(vl*un)){
            $.post("<?= site_url('requerimiento/actualizaReq') ?>", {tram:un+'-'+vl+'-'+id+'-'+lc}, /* und-val-idprod-local */
                function(rpta) {
                    const myArray = rpta.split("|");
                    color = myArray[0]==0?'bg-danger':'bg-success';
                    $(document).Toasts('create', {
                        class: color,
                        title: 'Requerimiento',
                        body: myArray[1],
                        position: 'bottomRight',
                        icon: 'far fa-check-circle fa-lg',
                        animation:	true,
                        autohide: true,
                        delay:	3500
                    });
                }
            );
        }else{
            $('#INP_'+id).val(0);
        }
    });

    
    $('#productos_centro tbody').on('click', '#selprod', function(event) { // 10
        var data = dtable.row($(this).parents('tr')).data();
        $.post(
            "<?= site_url('requerimiento/agregar') ?>", {
                codart: data.ARM_CODART,
                local: $( "select#cmb_local option:checked" ).val()
            },
            function(rpta) {
                const myArray = rpta.split("|");
                color = myArray[0]==0?'bg-danger':'bg-success';
                $(document).Toasts('create', {
                    class: color,
                    title: 'Producto',
                    body: myArray[1],
                    position: 'bottomRight',
                    icon: 'far fa-check-circle fa-lg',
                    animation:	true,
                    autohide: true,
                    delay:	2500
                });
                dtableReque.ajax.reload();
                //$('.selUnd').trigger('change');
            }
        );
    });
    $('#busqueda').keydown(function(event) {
        var keyCode = (event.keyCode ? event.keyCode : event.which);
        console.log(keyCode);
        if (keyCode == 13) {
            $('#buscar').trigger('click');
        }
        else if (keyCode == 40){            
            dtable.cell().focus();
            $('#busqueda').blur();
        }
    });
    $("#buscar").click(function() {
        dtable.ajax.reload();
    });
    $(".actualizar").click(function() {
        dtableReque.ajax.reload();
    });
    $(".ver_lista").click(function(){
        local = $(this).data('id');
        dtableReque.ajax.reload();
    });

    $("#generar_guia").click(function() {
        if (!dtableReque.data().count()){
            $.alert({
                        title: 'Alerta',
                        content: 'No se ha encontrado ningun requerimiento.',
                        type: 'red',
                    });
        }else{
            $.confirm({
            title: 'GENERAR GUIA DE SALIDA',
            icon: 'fa fa-warning',
            content: '<b>¿Vas a generar una Guía de Salida.?</b></br>',
            type: 'green',
            buttons: {   
                ok: {
                    text: "Si quiero generar la guía",
                    btnClass: 'btn-primary',
                    keys: ['enter'],
                    action: function(){
                        $.post("<?= site_url('requerimiento/crea_guia_requerimiento') ?>", {
                                loc: local,
                            },
                            function(data) {
                                const myArray = data.mensaje.split("|");                                
                                $.alert({
                                    title: 'Resultado',
                                    content: '<blockquote class="quote-success"><h5>Resultado!</h5><p>Se ha generado el</br>'+myArray[0]+':</br>GUIA '+myArray[1]+'-'+myArray[2]+'</br></p></blockquote>',
                                    type: 'green',
                                });
                                dtableReque.ajax.reload();
                            }
                        );
                    }
                },
                cancel: function(){
                    $.alert({
                        title: 'Gracias',
                        content: 'No se ha generado ninguna guía.',
                        type: 'green',
                        });
                    }
                }
            });
        }
    });
    
    $("#generar_ingreso").click(function() {
        if (!dtableReque.data().count()){
            $.alert({
                        title: 'Alerta',
                        content: 'No se ha encontrado ningun requerimiento.',
                        type: 'red',
                    });
        }else{
            $.confirm({
            title: 'GENERAR GUIA DE INGRESO',
            icon: 'fa fa-warning',
            content: '<b>¿Vas a generar una Guía de INGRESO.?</b></br>',
            type: 'green',
            buttons: {   
                ok: {
                    text: "Si quiero generar la guía",
                    btnClass: 'btn-primary',
                    keys: ['enter'],
                    action: function(){
                        $.post("<?= site_url('requerimiento/crea_guia_requerimiento_ING') ?>", {
                                loc: local,
                            },
                            function(data) {
                                const myArray = data.mensaje.split("|");                                
                                $.alert({
                                    title: 'Resultado',
                                    content: '<blockquote class="quote-success"><h5>Resultado!</h5><p>Se ha generado el</br>'+myArray[0]+':</br>GUIA '+myArray[1]+'-'+myArray[2]+'</br></p></blockquote>',
                                    type: 'green',
                                });
                                dtableReque.ajax.reload();
                            }
                        );
                    }
                },
                cancel: function(){
                    $.alert({
                        title: 'Gracias',
                        content: 'No se ha generado ninguna guía.',
                        type: 'green',
                        });
                    }
                }
            });
        }
    });
});
</script>
<?= $this->endSection(); ?>