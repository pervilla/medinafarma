<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<section class="content-header"></section>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title" id="producto_seleccionado">CLIENTE A MANTENER</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                                        </div>
                                        <input type="numeric" class="form-control" id="CLI_CODCLIE" name="CLI_CODCLIE"
                                            placeholder="CODIGO CLIENTE" />
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
                                        <input type="numeric" class="form-control" id="CLI_RUC_ESPOSO"
                                            name="CLI_RUC_ESPOSO" placeholder="R.U.C. o D.N.I." />
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
                                    placeholder="RAZON SOCIAL" />
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
                                    placeholder="TELEFONO" />
                                <input type="hidden" id="estado" name="estado" />
                            </div>
                            <!-- /.input group -->
                        </div>
                        <div class="card-footer">
                            <button type="button" id="unircliente" class="btn btn-primary">Unir Cliente</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title" id="producto_seleccionado">CLIENTE A ELIMINAR</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                                        </div>
                                        <input type="numeric" class="form-control" id="CLI_CODCLIE_02" name="CLI_CODCLIE_02"
                                            placeholder="CODIGO CLIENTE"  />
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
                                        <input type="numeric" class="form-control" id="CLI_RUC_ESPOSO_02"
                                            name="CLI_RUC_ESPOSO_02" placeholder="R.U.C. o D.N.I." />
                                    </div>
                                </div>
                                <div class="col-6">
                                    <button type="button" id="importarp_02" name="importarp_02" class="btn btn-primary"><i
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
                                <input type="text" class="form-control" id="CLI_NOMBRE_02" name="CLI_NOMBRE_02"
                                    placeholder="RAZON SOCIAL" />
                                    <div class="input-group-append">
                                        <button class="btn btn-primary btn-nombre" type="button">Mantener</button>
                                    </div>
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
                                <input type="text" id='CLI_FECHA_NAC_02' name='CLI_FECHA_NAC_02' class="form-control"
                                    data-inputmask-alias="datetime" data-inputmask-inputformat="dd/mm/yyyy" data-mask=""
                                    inputmode="numeric" placeholder="FECHA NACIMIENTO">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary btn-fecha" type="button">Mantener</button>
                                    </div>
                            </div>
                        </div>
                        <!-- /.form group -->

                        <!-- phone mask -->
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-home"></i></span>
                                </div>
                                <input type="text" class="form-control" id="CLI_CASA_DIREC_02" name="CLI_CASA_DIREC_02"
                                    placeholder="DIRECCIÓN" />
                                    <div class="input-group-append">
                                        <button class="btn btn-primary btn-casa type="button">Mantener</button>
                                    </div>
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
                                <input type="text" class="form-control" id="CLI_TELEF1_02" name="CLI_TELEF1_02"
                                    placeholder="TELEFONO" />
                                    <div class="input-group-append">
                                        <button class="btn btn-primary btn-telef" type="button">Mantener</button>
                                    </div>
                                
                            </div><input type="hidden" id="estado" name="estado" />
                            <!-- /.input group -->
                        </div>
                        
                    </div>
                </div>
            </div>
            <div class="col-lg-4">


            </div>
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->

</section>




<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<!-- Select2 -->
<link rel="stylesheet" href="<?= site_url('../../plugins/select2/css/select2.min.css') ?>">
<link rel="stylesheet" href="<?= site_url('../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">

<script>
$(document).ready(function() {
    $('#CLI_RUC_ESPOSO').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            $.post(
                "<?= site_url('personas/get_persona_sunat') ?>", {
                    ruc: $("input#CLI_RUC_ESPOSO").val(),
                    tipo: 2,
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    if (datos[0] == null) {
                        alert("DNI o RUC no válido o no registrado");
                        $("input#CLI_RUC_ESPOSO").focus();
                    } else if (datos[0]=='nada') {
                        alert('DNI o RUC no se encuentra, registrelo manualmente.');
                        $("input#CLI_RUC_ESPOSO").focus();
                    }else {
                        $("input#CLI_RUC_ESPOSO").val(datos[0]);
                        $("input#CLI_NOMBRE").val(datos[1]);
                        $("input#CLI_CASA_DIREC").val(datos[2]);
                        $("input#CLI_TELEF1").val(datos[3]);
                        $("input#CLI_CODCLIE").val(datos[4]);
                        $("input#CLI_FECHA_NAC").val(datos[6]);  
                        if (datos[5] == "nuevo") {
                            alert("El DNI:" + datos[0] + " es nuevo no se puede unir!");
                            /* boton unir desabilitar */
                        } 
                    }
                }
            );
        }
    });

$('#CLI_RUC_ESPOSO_02').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
        $.post(
        "<?= site_url('personas/get_persona_sunat') ?>", {
            ruc: $("input#CLI_RUC_ESPOSO_02").val(),
            tipo: 2,
        },
        function(htmlexterno) {
            var datos = eval(htmlexterno);
            if (datos[0] == null) {
                alert("DNI o RUC no válido o no registrado");
                $("input#CLI_RUC_ESPOSO_02").focus();
            } else if (datos[0]=='nada') {
                alert('DNI o RUC no se encuentra, registrelo manualmente.');
                $("input#CLI_RUC_ESPOSO_02").focus();
            }else {
                $("input#CLI_RUC_ESPOSO_02").val(datos[0]);
                $("input#CLI_NOMBRE_02").val(datos[1]);
                $("input#CLI_CASA_DIREC_02").val(datos[2]);
                $("input#CLI_TELEF1_02").val(datos[3]);
                $("input#CLI_CODCLIE_02").val(datos[4]);
                $("input#CLI_FECHA_NAC_02").val(datos[6]);  
                if (datos[5] == "nuevo") {
                    alert("El DNI:" + datos[0] + " es nuevo no se puede unir!");
                    /* boton unir desabilitar */
                }
            }
        }
        );

    }
});
$('#CLI_CODCLIE').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            $.post(
                "<?= site_url('personas/get_persona_id') ?>", {
                    codcli: $("input#CLI_CODCLIE").val()
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    if (datos[0] == null) {
                        alert("Codigo de Cliente no válido o no registrado");
                        $("input#CLI_RUC_ESPOSO").val('');
                        $("input#CLI_NOMBRE").val('');
                        $("input#CLI_CASA_DIREC").val('');
                        $("input#CLI_TELEF1").val('');
                        $("input#CLI_CODCLIE").val('');
                        $("input#CLI_CODCLIE").focus();
                    } else {
                        $("input#CLI_RUC_ESPOSO").val(datos[0]);
                        $("input#CLI_NOMBRE").val(datos[1]);
                        $("input#CLI_CASA_DIREC").val(datos[2]);
                        $("input#CLI_TELEF1").val(datos[3]);
                        $("input#CLI_CODCLIE").val(datos[4]);
                        $("input#CLI_FECHA_NAC").val(datos[6]);  
                        if (datos[5] == "nuevo") {
                            alert("El DNI:" + datos[0] + " es nuevo no se puede unir!");
                            /* boton unir desabilitar */
                        } 
                    }
                }
            );
        }
    });
    $('#CLI_CODCLIE_02').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            $.post(
                "<?= site_url('personas/get_persona_id') ?>", {
                    codcli: $("input#CLI_CODCLIE_02").val()
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    if (datos[0] == null) {
                        alert("Codigo de Cliente no válido o no registrado");
                        $("input#CLI_RUC_ESPOSO_02").val('');
                        $("input#CLI_NOMBRE_02").val('');
                        $("input#CLI_CASA_DIREC_02").val('');
                        $("input#CLI_TELEF1_02").val('');
                        $("input#CLI_CODCLIE_02").val('');
                        $("input#CLI_FECHA_NAC_02").val('');                        
                        $("input#CLI_CODCLIE_02").focus();
                    } else {
                        $("input#CLI_RUC_ESPOSO_02").val(datos[0]);
                        $("input#CLI_NOMBRE_02").val(datos[1]);
                        $("input#CLI_CASA_DIREC_02").val(datos[2]);
                        $("input#CLI_TELEF1_02").val(datos[3]);
                        $("input#CLI_CODCLIE_02").val(datos[4]);
                        $("input#CLI_FECHA_NAC_02").val(datos[6]);  

                        if (datos[5] == "nuevo") {
                            alert("El DNI:" + datos[0] + " es nuevo no se puede unir!");
                            /* boton unir desabilitar */
                        } 
                    }
                }
            );
        }
    });

$('.btn-nombre').click(function(event){
    $("input#CLI_NOMBRE").val($("input#CLI_NOMBRE_02").val());
});
$('.btn-casa').click(function(event){
    $("input#CLI_CASA_DIREC").val($("input#CLI_CASA_DIREC_02").val());
});
$('.btn-telef').click(function(event){
    $("input#CLI_TELEF1").val($("input#CLI_TELEF1_02").val());
});
$('.btn-fecha').click(function(event){
    $("input#CLI_FECHA_NAC").val($("input#CLI_FECHA_NAC_02").val());
});

    ///// UNIR CLIENTES /////
    $("#unircliente").click(function() {
        $.post(
            "<?= site_url('personas/unir_personas') ?>", {
                cod: $("input#CLI_CODCLIE").val(),
                cod2: $("input#CLI_CODCLIE_02").val(),
                ruc: $("input#CLI_RUC_ESPOSO").val(),
                nom: $("input#CLI_NOMBRE").val(),
                dir: $("input#CLI_CASA_DIREC").val(),
                tel: $("input#CLI_TELEF1").val(),
                nac: $("input#CLI_FECHA_NAC").val(),
                his: $("input[name=historia]:checked").val(),
                est: $("input#estado").val(),
                tps: 'C'
            },
            function(htmlexterno) {
                if(htmlexterno){
                    $("input#CLI_RUC_ESPOSO").val('');
                    $("input#CLI_NOMBRE").val('');
                    $("input#CLI_CASA_DIREC").val('');
                    $("input#CLI_TELEF1").val('');
                    $("input#CLI_CODCLIE").val('');
                    $("input#CLI_FECHA_NAC").val('');                        
                    $("input#CLI_CODCLIE").focus();
                    $("input#CLI_RUC_ESPOSO_02").val('');
                    $("input#CLI_NOMBRE_02").val('');
                    $("input#CLI_CASA_DIREC_02").val('');
                    $("input#CLI_TELEF1_02").val('');
                    $("input#CLI_CODCLIE_02").val('');
                    $("input#CLI_FECHA_NAC_02").val('');                        
                    $("input#CLI_CODCLIE_02").focus();
                }

            }
        );
    });


});
</script>
<?= $this->endSection(); ?>