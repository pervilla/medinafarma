<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header">

</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid"> 
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pacientes</h3>
                            <div class="card-tools">                        
                            <div class="row">                            
                                <div class="input-group input-group-sm col-lg-12">
                                    <div class="input-group-prepend">
                                    <button type="button" class="btn btn-danger">Paciente</button>
                                    </div>
                                    <input type="text" class="form-control" id="parametro">	                               
                                    <input id="CodCli" name="CodCli" type="hidden" value='0'>
                                    <span class="input-group-append">
                                    <button type="button" id="buscar" name="buscar" class="btn btn-success"><i class="fa fa-search"> </i> Ver</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>                    
                    <div class="card-body table-responsive">
                        <table id="table_pacientes" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>CODIGO</th>
                                    <th>PACIENTE</th>
                                    <th>TELEFONO</th>
                                    <th>DNI</th>
                                    <th>EDAD</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
                <!-- /.card -->
            </div>            
            <!-- /.col-md-6 -->       
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Atenciones</h3>
                        <div class="card-tools"></div>
                    </div>                    
                    <div class="card-body table-responsive">
                        <table id="table_atenciones" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>COD.CAMP</th>
                                    <th>FECHA</th>
                                    <th>CAMPAÑA</th>
                                    <th>ESTADO</th>
                                    <th>HISTORIA</th>
                                    <th>RECETA</th>
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
</div>
<!-- /.content -->

<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<!-- DataTables -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {
    $('#reservation').daterangepicker();
        var dtable = $('#table_pacientes').DataTable({
            ajax: {
                url: "<?= site_url('consultorio/get_pacientes') ?>",
                type: "POST",
                dataSrc: '',
                data: { 
                    param: function () { return $("input#parametro").val(); }
                }
            },
            columns: [
                {data: 'CLI_CODCLIE'},
                {data: 'CLI_NOMBRE'},                
                {data: 'CLI_TELEF1'},
                {data: 'CLI_RUC_ESPOSA'},
                {data: "EDAD",
                render: function(data, type, row, meta) {
                    if (row.CLI_FECHA_NAC!='') {
                        var today = new Date();
                        var birthDate = new Date(row.CLI_FECHA_NAC);
                        var age = today.getFullYear() - birthDate.getFullYear();
                        var m = today.getMonth() - birthDate.getMonth();
                        var da = today.getDate() - birthDate.getDate();
                        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        if(m<0){
                            m +=12;
                        }
                        if(da<0){
                            da +=30;
                        }
                        var age = age+" años "+ Math.abs(m) + " meses ";  
                    } else {
                        var age = "Sin Fecha";
                    }
                    return age
                }            
            },
            ],
            ordering: false,
            searching: false,
            paging: true,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            oLanguage: {"sInfo" : " "}
        });
        var dtableAtenciones = $('#table_atenciones').DataTable({
        ajax: {
            url: "<?= site_url('consultorio/get_atenciones') ?>",
            type: "POST",
            dataSrc: '',
            data: {
                codcli: function() {
                    return $("#CodCli").val();
                }
            }
        },
        processing: true,
        serverSide: true,
        columns: [
            {data: 'HIS_CODCAMP'},
            {data: 'CAM_FEC_INI'},            
            {data: 'CAM_DESCRIP'},
            {data: 'CIT_ESTADO',
                render: function(data, type, row, meta) {
                    if (row.CIT_ESTADO==0) {
                        var rpt = "No Asistio";   
                    } else if( row.CIT_ESTADO==1) {
                        var rpt = "Confirmado";
                    }else {
                        var rpt = row.CIT_ESTADO==2?"Atendido":"En Espera";
                    }
                    return rpt
                }
            },
            {data: 'HIS_CODHIS',
                render: function(data, type, row, meta) {
                    if(row.HIS_CODCAMP>0){
                        return "<a class='btn btn-block bg-gradient-primary btn-sm cs_pointer' href='<?=site_url('consultorio/historia')?>/"+row.HIS_CODCAMP+'/'+row.HIS_CODCIT+'/'+row.HIS_CODCLI+"' target='_blank'><i class='fas fa-print'></i> Historia</a>";
                    }else{
                        return '';
                    }                    
                }
            },
            {data: 'HISR_CODHIS',
                render: function(data, type, row, meta) {
                    if(row.HIS_CODCAMP>0){
                        return "<a class='btn btn-block bg-gradient-primary btn-sm cs_pointer' href='<?=site_url('consultorio/receta')?>/"+row.HIS_CODCAMP+'/'+row.HIS_CODCIT+'/'+row.HIS_CODCLI+"' target='_blank'><i class='fas fa-print'></i> Receta</a>";                
                    }else{
                        return '';
                    }
                }
            },
        ],
        fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {  
            if (aData.CIT_ESTADO == 1) {
                    $(nRow).addClass('bg-success');
                } 
                if (aData.CIT_ESTADO == 2){
                    $(nRow).addClass('bg-secondary');
                }
                if (aData.CIT_ESTADO == 3){
                    $(nRow).addClass('bg-warning');
                }  
            return nRow;
            },
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        paging: false,
        ordering: false,
        info: false,
        bFilter: false,
        searching: false,

    });

    $('#table_pacientes').on('click', 'tbody tr', function (event) {
            var data = dtable.row($(this)).data();
            $("input#CodCli").val(data['CLI_CODCLIE']);
            dtableAtenciones.ajax.reload();  
        });

    $("#buscar").click(function () {
        dtable.ajax.reload();           
    }); 
    $('#parametro').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
		dtable.ajax.reload();
    }
});


});




</script>



<?= $this->endSection(); ?>