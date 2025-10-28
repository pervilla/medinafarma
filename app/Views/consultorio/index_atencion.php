<?= $this->extend('templates/admin_consultorio'); ?>
<?= $this->section('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <!-- Profile Image -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle" src="<?= site_url('../../dist/img/user4-128x128.jpg') ?>" alt="User profile picture">
                        </div>
                        <h3 class="profile-username text-center"><?= $clientes->CLI_NOMBRE ?></h3>
                        <p class="text-muted text-center">
                            <?php
                            $date = new DateTime();
                            $string_date = $date->format('Y-m-d');
                            $date1 = date_create($clientes->CLI_FECHA_NAC);
                            $date2 = date_create($string_date);
                            $diff = date_diff($date1, $date2);
                            echo $diff->format('%y Años %m Meses %d Día');
                            ?>
                        </p>
                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <a href="#"><i class="fas fa-mobile-alt"></i></a>
                                <b>Teléfono:</b> <a class="float-right"><?= $clientes->CLI_TELEF1 ?></a>
                            </li>
                            <li class="list-group-item">
                                <a href="#"><i class="far fa-address-card"></i></a>
                                <b>D.N.I:</b> <a class="float-right"><?= $clientes->CLI_RUC_ESPOSA ?></a>
                            </li>
                            <li class="list-group-item">
                                <a href="#"><i class="fas fa-transgender"></i></a>
                                <b>Sexo:</b> <a class="float-right"></a>
                            </li>
                        </ul>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
                <!-- About Me Box -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Ultimas Atenciones</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <!-- Listado de Atenciones -->
                        <?php foreach ($historias as $hst) {
                            if ($historia->HIS_CODHIS != $hst->HIS_CODHIS) { ?>
                                <p class="text-muted">
                                    <?php if ($triaje == 'no') { ?>
                                        <a class="openPopup" data-href="<?= site_url('consultorio/historia') . '/' . $hst->HIS_CODCAMP . '/' . $hst->HIS_CODCIT . '/' . $hst->HIS_CODCLI ?>/" class="openPopup"> <i class="fas fa-book-open"></i> <?= $hst->CAM_FEC_INI ?> <strong><?= $hst->CAM_DESCRIP ?></strong> </i></a>
                                    <?php } else {
                                        echo $hst->CAM_FEC_INI . ' <strong>' . $hst->CAM_DESCRIP . '</strong>';
                                    }
                                    ?>
                                </p>
                                <hr>
                        <?php }
                        } ?>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <?php if ($triaje == 'no') { ?>
                                <li class="nav-item"><a class="nav-link active" href="#timeline" data-toggle="tab">Atención
                                        Médica</a></li>
                                <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Receta</a></li>
                                <li class="nav-item"><a class="nav-link" href="<?= site_url('consultorio/historia') . '/' . $historia->HIS_CODCAMP . '/' . $historia->HIS_CODCIT . '/' . $historia->HIS_CODCLI ?>/" target="_blank"><i class="fas fa-print"></i> Imprimir Historia</a></li>
                            <?php } else { ?>
                                <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Examen
                                        Clínico</a></li>
                            <?php } ?>
                            <li class="nav-item ml-auto">
                                <span class="time">
                                    <div class="row">
                                        <div class="col-10 btn-group">
                                            <button type="button" id="guardar_cita" class="btn btn-xs btn-success"><i class="fas fa-save"></i> Guardar</button>
                                            <?php if ($triaje == 'no') { ?>
                                                <button type="button" id="finalizar_cita" class="btn btn-xs btn-secondary"><i class="fas fa-door-closed"></i> Finalizar</button>
                                                <button type="button" id="pendiente_cita" class="btn btn-xs btn-warning"><i class="fas fa-door-open"></i> Pendiente</button>
                                                <a href="<?php echo site_url('consultorio/confirmados/') . $historia->HIS_CODCAMP ?>" class="btn btn-xs btn-primary " role="button" aria-disabled="true"><i class="fas fa-address-book"></i> Regresar</a>
                                            <?php } else { ?>
                                                <a href="<?php echo site_url('consultorio/pacientes/') . $historia->HIS_CODCAMP ?>" class="btn btn-xs btn-primary " role="button" aria-disabled="true"><i class="fas fa-address-book"></i> Regresar</a>
                                            <?php } ?>
                                        </div>
                                        <div class="col-2">
                                            <i id='update_his' class="fas fa-circle-notch fa-spin text-warning"></i>
                                            <i id='ok_his' class="far fa-check-circle text-success"></i>
                                        </div>
                                    </div>
                                </span>
                            </li>
                        </ul>
                    </div><!-- /.card-header -->

                    <div class="card-body">
                        <div class="tab-content">
                            <div class="<?= ($triaje == 'no') ? 'tab-pane' : 'active tab-pane' ?>" id="activity">
                                <!-- Post -->
                                <div class="post">
                                    <input type="hidden" id="HIS_CODHIS" name="HIS_CODHIS" value="<?= $historia->HIS_CODHIS ?>">
                                    <input type="hidden" id="HIS_CODCIT" name="HIS_CODCIT" value="<?= $historia->HIS_CODCIT ?>">
                                    <input type="hidden" id="HIS_CODCLI" name="HIS_CODCLI" value="<?= $historia->HIS_CODCLI ?>">
                                    <input type="hidden" id="HIS_CODCAMP" name="HIS_CODCAMP" value="<?= $historia->HIS_CODCAMP ?>">
                                    <input type="hidden" id="TRIAJE" name="TRIAJE" value="<?= $triaje ?>">
                                    <form class="form-horizontal">
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 col-form-label">
                                                <a href="#"><i class="fas fa-person-pregnant"></i></a> Gestante</label>
                                            <div class="col-sm-1">
                                                <select id="HIS_GESTANTE" name="HIS_GESTANTE" class="form-control">
                                                    <option value="0" <?= $historia->HIS_GESTANTE == 0 ? 'selected=selected' : '' ?>>NO</option>
                                                    <option value="1" <?= $historia->HIS_GESTANTE == 1 ? 'selected=selected' : '' ?>>SI</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 col-form-label">
                                                <a href="#"><i class="fas fa-heartbeat"></i></a>
                                                Presión Arterial (mm Hg)</label>
                                            <div class="col-sm-1">
                                                <input type="number" class="form-control" id="HIS_PREART_MM" placeholder="mm" value="<?= $historia->HIS_PREART_MM ?>">
                                            </div>
                                            <div class="col-sm-1">
                                                <input type="number" class="form-control" id="HIS_PREART_HG" placeholder="Hg" value="<?= $historia->HIS_PREART_HG ?>">
                                            </div>
                                            <div class="col-sm-1"></div>
                                            <label for="inputName" class="col-sm-3 col-form-label">Frecuencia Cardiaca
                                                (Latidos x Min)</label>
                                            <div class="col-sm-3">
                                                <input type="number" class="form-control" id="HIS_FRE_CARD" placeholder="Latidos" value="<?= $historia->HIS_FRE_CARD ?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-3 col-form-label">
                                                <a href="#"><i class="fab fa-cloudsmith"></i></a>
                                                Saturación</label>
                                            <div class="col-sm-1">
                                                <input type="number" class="form-control" id="HIS_SATURACION" placeholder="Hg" value="<?= $historia->HIS_SATURACION ?>">
                                            </div>
                                            <label for="inputName" class="offset-sm-2 col-sm-3 col-form-label">Frecuencia
                                                Respiratoria x Min</label>
                                            <div class="col-sm-3">
                                                <input type="number" class="form-control" id="HIS_FRE_RESP" placeholder="" value="<?= $historia->HIS_FRE_RESP ?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-3 col-form-label">
                                                <a href="#"><i class="fas fa-temperature-high"></i></a>
                                                Temperatura Corporal (ºC)</label>
                                            <div class="col-sm-1">
                                                <input type="number" class="form-control" id="HIS_TEMPERATURA" placeholder="0.00" value="<?= $historia->HIS_TEMPERATURA ?>">
                                            </div>
                                            <div class="col-sm-8"></div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-3 col-form-label">
                                                <a href="#"><i class="fas fa-weight"></i></a>
                                                Peso (Kg)</label>
                                            <div class="col-sm-1">
                                                <input type="number" class="form-control" id="HIS_PESO" placeholder="0.00" value="<?= $historia->HIS_PESO ?>">
                                            </div>
                                            <label for="inputEmail" class="col-sm-1 col-form-label">Talla (M)</label>
                                            <div class="col-sm-1">
                                                <input type="number" class="form-control" id="HIS_TALLA" placeholder="0.00" value="<?= $historia->HIS_TALLA ?>">
                                            </div>
                                            <label for="inputEmail" class="col-sm-1 col-form-label">IMC</label>
                                            <div class="col-sm-1">
                                                <input type="number" class="form-control" id="HIS_IMC" placeholder="">
                                            </div>
                                            <div class="col-sm-4"></div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-3 col-form-label">Anamnesis</label>
                                            <div class="col-sm-9">
                                                <textarea id="HIS_EXA_CLI" name="HIS_EXA_CLI" class="form-control" rows="4" maxlength="500"><?= $historia->HIS_EXA_CLI ?></textarea>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.post -->
                            </div>
                            <!-- /.tab-pane -->
                            <div class="<?= ($triaje == 'no') ? 'active tab-pane' : 'tab-pane' ?>" id="timeline">
                                <!-- The timeline -->
                                <div class="timeline timeline-inverse">
                                    <!-- timeline time label -->
                                    <div class="time-label">
                                        <span class="bg-success">Atencion</span>
                                    </div>
                                    <!-- /.timeline-label -->
                                    <!-- timeline item -->
                                    <div>
                                        <i class="fas fa-user-md bg-purple"></i>
                                        <div class="timeline-item">
                                            <div class="timeline-body">
                                                <div class="form-group row">
                                                    <label for="inputName" class="col-sm-2 col-form-label">
                                                        <a href="#"><i class="fas fa-person-pregnant"></i></a> Gestante</label>
                                                    <div class="col-sm-2">
                                                        <select id="HIS_GESTANTE_2" name="HIS_GESTANTE_2" class="form-control">
                                                            <option value="0" <?= $historia->HIS_GESTANTE == 0 ? 'selected=selected' : '' ?>>NO</option>
                                                            <option value="1" <?= $historia->HIS_GESTANTE == 1 ? 'selected=selected' : '' ?>>SI</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputEmail" class="col-sm-2 col-form-label">Anamnesis</label>
                                                    <div class="col-sm-10">
                                                        <textarea id="HIS_EXA_CLI_2" name="HIS_EXA_CLI_2" class="form-control" rows="4" maxlength="500"><?= $historia->HIS_EXA_CLI ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </br>
                                        <div class="timeline-item">
                                            <span class="time"><i class="far fa-clock"></i></span>
                                            <h3 class="timeline-header"><a href="#">Signos</a> Vitales </h3>
                                            <div class="timeline-body">
                                                <div class="form-group row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">
                                                        <a href="#"><i class="fas fa-heartbeat"></i></a>
                                                        Presión Arterial (mm Hg)</label>
                                                    <div class="col-sm-1">
                                                        <input type="number" class="form-control" id="HIS_PREART_MM_2" placeholder="mm" value="<?= $historia->HIS_PREART_MM ?>">
                                                    </div>
                                                    <div class="col-sm-1">
                                                        <input type="number" class="form-control" id="HIS_PREART_HG_2" placeholder="Hg" value="<?= $historia->HIS_PREART_HG ?>">
                                                    </div>
                                                    <div class="col-sm-1"></div>
                                                    <label for="inputName" class="col-sm-3 col-form-label">Frecuencia Cardiaca
                                                        (Latidos x Min)</label>
                                                    <div class="col-sm-3">
                                                        <input type="number" class="form-control" id="HIS_FRE_CARD_2" placeholder="Latidos" value="<?= $historia->HIS_FRE_CARD ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputEmail" class="col-sm-3 col-form-label">
                                                        <a href="#"><i class="fab fa-cloudsmith"></i></a>
                                                        Saturación</label>
                                                    <div class="col-sm-1">
                                                        <input type="number" class="form-control" id="HIS_SATURACION_2" placeholder="Hg" value="<?= $historia->HIS_SATURACION ?>">
                                                    </div>
                                                    <label for="inputName" class="offset-sm-2 col-sm-3 col-form-label">Frecuencia
                                                        Respiratoria x Min</label>
                                                    <div class="col-sm-3">
                                                        <input type="number" class="form-control" id="HIS_FRE_RESP_2" placeholder="" value="<?= $historia->HIS_FRE_RESP ?>">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputEmail" class="col-sm-3 col-form-label">
                                                        <a href="#"><i class="fas fa-temperature-high"></i></a>
                                                        Temperatura Corporal (ºC)</label>
                                                    <div class="col-sm-1">
                                                        <input type="number" class="form-control" id="HIS_TEMPERATURA_2" placeholder="0.00" value="<?= $historia->HIS_TEMPERATURA ?>">
                                                    </div>
                                                    <div class="col-sm-8"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END timeline item -->
                                    <!-- timeline time label -->
                                    <div class="time-label">
                                        <span class="bg-indigo">
                                            Antropemetria
                                        </span>
                                    </div>
                                    <!-- /.timeline-label -->
                                    <!-- timeline item -->
                                    <div>
                                        <i class="fas fa-comments bg-info"></i>
                                        <div class="timeline-item">
                                            <div class="timeline-body">
                                                <div class="form-group row">
                                                    <label for="inputEmail" class="col-sm-2 col-form-label">
                                                        <a href="#"><i class="fas fa-weight"></i></a>
                                                        Peso (Kg)</label>
                                                    <div class="col-sm-1">
                                                        <input type="number" class="form-control" id="HIS_PESO_2" placeholder="0.00" value="<?= $historia->HIS_PESO ?>">
                                                    </div>
                                                    <label for="inputEmail" class="col-sm-2 col-form-label text-right">Talla (M)</label>
                                                    <div class="col-sm-1">
                                                        <input type="number" class="form-control" id="HIS_TALLA_2" placeholder="0.00" value="<?= $historia->HIS_TALLA ?>">
                                                    </div>
                                                    <label for="inputEmail" class="col-sm-1 col-form-label text-right">IMC</label>
                                                    <div class="col-sm-2">
                                                        <input type="number" class="form-control" id="HIS_IMC" placeholder="">
                                                    </div>
                                                    <div class="col-sm-4"></div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputEmail" class="col-sm-2 col-form-label">Examen Físico</label>
                                                    <div class="col-sm-10">
                                                        <textarea id="HIS_INDICACIONES" name="HIS_INDICACIONES" class="form-control" rows="4" maxlength="500"><?= $historia->HIS_INDICACIONES ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="inputEmail" class="col-sm-2 col-form-label">Plan de Trabajo y/o Observaciones</label>
                                                    <div class="col-sm-10">
                                                        <textarea id="HIS_PLAN_TRAB" name="HIS_PLAN_TRAB" class="form-control" rows="4" maxlength="500"><?= $historia->HIS_PLAN_TRAB ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END timeline item -->
                                    <!-- timeline time label -->
                                    <div class="time-label">
                                        <span class="bg-danger">Diagnóstico</span>
                                    </div>
                                    <!-- /.timeline-label -->
                                    <!-- timeline item -->
                                    <div>
                                        <i class="fas fa-medkit bg-primary"></i>
                                        <div class="timeline-item">
                                            <span class="time"><i class="far fa-clock"></i></span>
                                            <div class="timeline-header form-group row">
                                                <label for="inputEmail" class="col-sm-2 col-form-label">Diagnóstico</label>
                                                <div class="col-sm-1">
                                                    <input type="text" class="form-control" id="HISC_CIE10_CODIGO" placeholder="A00.0">
                                                </div>
                                                <div class="col-sm-7">
                                                    <select id="HISC_CIE10_DESCRIPCION" name="HISC_CIE10_DESCRIPCION" class="form-control" style="width: 100%;" placeholder="Diagnóstico ICD 10"></select>
                                                </div>
                                                <div class="col-sm-2"></div>
                                            </div>
                                            <div class="timeline-body table-responsive">
                                                <table id="table_diag" class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Código</th>
                                                            <th>Descripción Diagnóstico</th>
                                                            <th>Tipo Diagnóstico</th>
                                                            <th>Caso Diagnóstico</th>
                                                            <th>Alta al Diagnóstico?</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                            <div class="timeline-footer"></div>
                                        </div>
                                    </div>
                                    <!-- END timeline item -->
                                    <div>
                                        <i class="far fa-clock bg-gray"></i>
                                    </div>
                                </div>
                            </div>
                            <!-- /.tab-pane -->
                            <div class="tab-pane" id="settings">
                                <!-- The timeline -->
                                <div class="timeline timeline-inverse">
                                    <!-- timeline time label -->
                                    <div class="time-label">
                                        <span class="bg-danger">Medicamento</span>
                                    </div>
                                    <!-- /.timeline-label -->
                                    <!-- timeline item -->
                                    <div>
                                        <i class="fas fa-pills bg-primary"></i>
                                        <div class="timeline-item bg-gradient-secondary">
                                            <span class="time"><i class="far fa-clock"></i></span>
                                            <div class="timeline-header form-group row">
                                                <div class="col-sm-10 input-group">
                                                    <input type="text" class="form-control w-10" id="HISR_CODART" placeholder="Código" readonly>
                                                    <input type="text" class="form-control w-50" id="HISR_NOMART" data-toggle="modal" data-target="#modal-producto" placeholder="Descripción del producto" readonly>
                                                    <span class="input-group-append">
                                                        <button type="button" data-toggle="modal" data-target="#modal-producto" class="btn btn-success"><i class="fa fa-search"> </i> Buscar</button>
                                                    </span>
                                                </div>
                                                <div class="col-sm-2"></div>
                                            </div>
                                            <div class="timeline-header form-group row">
                                                <div class="col-sm-6 input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-pills"></i>&nbsp; Cantidad:</span></div>
                                                    <input type="number" class="form-control" placeholder="Cantidad" name="HISR_CANT" id="HISR_CANT">
                                                    <input type="text" class="form-control" value="Días:" readonly>
                                                    <input type="number" class="form-control" placeholder="Días" name="HISR_DIAS" id="HISR_DIAS">
                                                </div>
                                            </div>
                                            <div class="timeline-header form-group row">
                                                <div class="col-sm-10 input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-file-signature"></i>&nbsp; Indicaciones:</span></div>
                                                    <input type="text" class="form-control" id="HISR_INDICACIONES" placeholder="Indicaciones">                                                    
                                                </div>
                                                <div class="col-sm-2">
                                                <span class="input-group-append">
                                                        <button type="button" id="guardar_medicamento" class="btn btn-primary"><i class="fas fa-save"></i> Agregar a Receta</button></span>
                                                    <input type="hidden" class="form-control" id="HISR_UPD">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <i class="fas fa-pills bg-primary"></i>
                                        <div class="timeline-item">
                                            <span class="time"><i class="far fa-clock"></i></span>
                                            <div class="timeline-header form-group row">
                                            <div class="col-sm-10">
                                                <label for="" class="col-sm-2 col-form-label">Receta</label>
                                            </div>
                                                <div class="col-sm-2">
                                                    <a href="<?= site_url('consultorio/receta') . '/' . $historia->HIS_CODCAMP . '/' . $historia->HIS_CODCIT . '/' . $historia->HIS_CODCLI ?>/" target="_blank" class="btn btn-danger" role="button"><i class="fas fa-print"></i> Imprimir Receta</a>
                                                </div>
                                            </div>

                                            <div class="timeline-body table-responsive">
                                                <table id="table_receta" class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Código</th>
                                                            <th>Descripción</th>
                                                            <th>Cant.</th>
                                                            <th>Días</th>
                                                            <th>Indicaciones</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>                                            
                                        </div>
                                    </div>
                                    <!-- END timeline item -->
                                    <div>
                                        <i class="far fa-clock bg-gray"></i>
                                    </div>
                                </div>
                            </div>
                            <!-- /.tab-pane -->
                        </div>
                        <!-- /.tab-content -->
                    </div><!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->

    <div class="modal fade" id="modal-producto" data-keyboard="false" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="input-group input-group-sm mb-12">
                        <div class="input-group-prepend">
                            <button type="button" class="btn btn-danger">Producto </button>
                        </div>
                        <input type="text" class="form-control" id="busqueda" autocomplete="off" placeholder="Producto (Use '%' para caracter o palabra desconocida)">
                        <span class="input-group-append">
                            <button type="button" id="buscar" name="buscar" class="btn btn-success"><i class="fa fa-search"> </i> Buscar</button>
                        </span>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <!-- /.col -->
                        <div class="col-md-12">
                            <table id="productos_centro" class="display compact nowrap dataTable no-footer dtr-inline collapsed table-bordered" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Productos</th>
                                        <th>Pqt</th>
                                        <th>Und</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <!-- /.col -->
                        <div class="col-md-12">
                            <table id="productos_equival" class="display compact nowrap dataTable no-footer dtr-inline collapsed table-bordered" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <td>Local</td>
                                        <th>Poductos Equivalentes</th>
                                        <th>Pqt</th>
                                        <th>Und</th>
                                        <th>Pre</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Historia -->
    <div class="modal fade" id="ModalHistoria" role="dialog">
        <div class="modal-dialog modal-xl">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Historia</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="loader"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

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
<script>
    function act_diag(a, b, c) {
        $.post("<?= site_url('consultorio/actualiza_diagnostico') ?>", {
            his: $("#HIS_CODHIS").val(),
            a: a,
            b: b,
            c: c
        }, function(r) {});
    }

    function OpenBuscador() {
        $('#modal-producto').show();
    }
    $(document).on('keydown', function(event) {
        if (event.key == "Escape" && $('#modal-producto').is(':visible')) {
            $("#busqueda").val('');
            $("#busqueda").focus();
        }
    });
    $(document).ready(function() {
        $('#update_his').hide();
        $('#ok_his').hide();
        $('#modal-producto').on('shown.bs.modal', function() {

            $('#busqueda').trigger('focus')
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
            columns: [{
                    data: 'ARM_CODART',
                    orderable: false
                },
                {
                    data: 'ART_NOMBRE'
                },
                {
                    data: 'ART_PQT',
                    render: function(data, type, row, meta) {
                        return (Math.round(data * 1000) / 1000);
                    },
                    orderable: false
                },
                {
                    data: 'ART_UNID',
                    render: function(data, type, row, meta) {
                        return (Math.round(data * 1000) / 1000);
                    },
                    orderable: false
                },
                
                {
                    data: 'ARM_CODART',
                    render: function(data, type, row, meta) {
                        return "<div class='btn-group btn-group-sm' role='group' aria-label='Small button group'><button type='button' id='selequi' class='btn btn-block bg-gradient-danger btn-xs'><i class='fas fa-equals'></i> Equivalente</button><button type='button' id='selprod' class='btn btn-primary'><i class='fas fa-pills'></i> Seleccionar</button></div>"
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
            ordering: false,
            orderable: false,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            select: true,
            keys: {
                blurable: false
            }
        });

        dtable.on('key', function(e, datatable, key, cell, originalEvent) {
            if (key == 13) {
                var rowData = datatable.row(cell.index().row).data();
                $('#modal-producto').modal('hide');
                $("#HISR_CODART").val(rowData['ARM_CODART']);
                $("#HISR_NOMART").val(rowData['ART_NOMBRE']);
                $("#HISR_UPD").val('0');
                $("#HISR_CANT").focus();
            }
        });

        $('#productos_centro tbody').on('click', '#selprod', function(event) {
            var data = dtable.row($(this).parents('tr')).data();
            $('#modal-producto').modal('hide');
            $("#HISR_CODART").val(data.ARM_CODART);
            $("#HISR_NOMART").val(data.ART_NOMBRE);
            $("#HISR_UPD").val('0');
            $("#HISR_CANT").focus();
        });
        $('#productos_centro tbody').on('click', '#selequi', function(event) {
            var data = dtable.row($(this).parents('tr')).data();
            console.log(data);
            $("#productos_equival > tbody").html('');
            $.post("<?= site_url('productos/get_stock') ?>", {
                artkey: '',
                artsbg: data['ART_SUBGRU'],
                local: 1,
                btn: true
            }, function(htmlexterno) {
                $("#productos_equival > tbody").append(htmlexterno);
            });
            dtable.$('tr.selected').removeClass('selected');
            $(this).parents('tr').addClass('selected');
        });

        $('#productos_equival').on('click', 'tbody tr', function(event) {
            id = $(this).data("id");
            var valores = "";
            $(this).find(".descrip").each(function() {
                valores += $(this).text() + "\n";
            });
            descrip = $.trim(valores);
            $('#modal-producto').modal('hide');
            $("#HISR_CODART").val(id);
            $("#HISR_NOMART").val(descrip);
            $("#HISR_UPD").val('0');
            $("#HISR_CANT").focus();
        });

        $('#busqueda').keydown(function(event) {
            var keyCode = (event.keyCode ? event.keyCode : event.which);
            if (keyCode == 13) {
                $('#buscar').trigger('click');
            } else if (keyCode == 40) {
                dtable.cell().focus();
                $('#busqueda').blur();
            }
        });

        $("#buscar").click(function() {
            dtable.ajax.reload();
        });

        $("#HISC_CIE10_CODIGO").bind('keyup', function(e) {
            if (e.keyCode === 13 || e.keyCode === 10)
                $.post(
                    "<?= site_url('consultorio/get_cie_nombre') ?>", {
                        cie: $("#HISC_CIE10_CODIGO").val()
                    },
                    function(htmlexterno) {
                        data = htmlexterno[0];
                        $('#update_his').hide();
                        $('#ok_his').show();
                        $("#HISC_CIE10_CODIGO").val(data.id);
                        $('#HISC_CIE10_DESCRIPCION').empty();
                        var newOption = new Option(data.text, data.id, false, false);
                        $('#HISC_CIE10_DESCRIPCION').append(newOption).trigger('change');
                        if (data.id.length > 0) {
                            doAjaxdiagnostico();
                        }
                    }
                );
        });

        $('#HISC_CIE10_DESCRIPCION').on('select2:select', function(e) {
            $("#HISC_CIE10_CODIGO").val(e.params.data.id);
            doAjaxdiagnostico();
        });

        $("#HISC_CIE10_DESCRIPCION").select2({
            ajax: {
                url: "<?= site_url('consultorio/get_cie_nombre') ?>",
                dataType: "json",
                processResults: function(data) {
                    $('#update_his').hide();
                    $('#ok_his').show();
                    $('#HISC_CIE10_DESCRIPCION').empty();
                    return {
                        results: data,
                    };
                },
            },
            width: "100%",
            placeholder: "Seleccione Diagnóstico",
            escapeMarkup: function(markup) {
                return markup;
            },
            allowClear: true,
            width: '100%',
        });
        var dtableDiag = $('#table_diag').DataTable({
            ajax: {
                url: "<?= site_url('consultorio/get_diagnostico') ?>",
                type: "POST",
                dataSrc: '',
                data: {
                    his: function() {
                        return $("#HIS_CODHIS").val();
                    }
                }
            },
            processing: true,
            columns: [{
                    data: 'HISD_CIE_CODIGO'
                },
                {
                    data: 'HISD_CIE_DESCRIPCION'
                },
                {
                    data: 'HISD_TIPO',
                    render: function(data, type, row, meta) {
                        asel = data == 1 ? 'selected' : '';
                        bsel = data == 2 ? 'selected' : '';
                        return "<select class='form-control cs_cursor' onchange=\"act_diag(this.value,\'" +
                            row.HISD_CIE_CODIGO +
                            "\',1)\"><option value='0'>-SELECCIONE-</option><option value='1' " +
                            asel + ">DEFINITIVO</option><option value='2' " + bsel +
                            ">PRESUNTIVO</option></select>";
                    }
                },
                {
                    data: 'HISD_CASO',
                    render: function(data, type, row, meta) {
                        asel = data == 1 ? 'selected' : '';
                        bsel = data == 2 ? 'selected' : '';
                        return "<select class='form-control cs_cursor' onchange=\"act_diag(this.value,\'" +
                            row.HISD_CIE_CODIGO +
                            "\',2)\"><option value='0'>-SELECCIONE-</option><option value='1' " +
                            asel + ">NUEVO</option><option value='2' " + bsel +
                            ">REPETIDO</option></select>";
                    }
                },
                {
                    data: 'HISD_ALTA',
                    render: function(data, type, row, meta) {
                        asel = data == 1 ? 'selected' : '';
                        bsel = data == 2 ? 'selected' : '';
                        return "<select class='form-control cs_cursor' onchange=\"act_diag(this.value,\'" +
                            row.HISD_CIE_CODIGO +
                            "\',3)\"><option value='0'>-SELECCIONE-</option><option value='1' " +
                            asel + ">SI</option><option value='2' " + bsel +
                            ">NO</option></select>";
                    }
                },
                {
                    data: 'HISD_CODHIS',
                    render: function() {
                        return "<button type='button' id='delcie' class='btn btn-outline-primary btn-block'><i class='fa fa-trash'></i></button>"
                    }
                },
            ],
            searching: false,
            paging: false,
            ordering: false,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
        });

        var dtableReceta = $('#table_receta').DataTable({
            ajax: {
                url: "<?= site_url('consultorio/get_receta') ?>",
                type: "POST",
                dataSrc: '',
                data: {his:function(){return $("#HIS_CODHIS").val();}}
            },
            processing: true,
            columns: [
                {data: 'HISR_CODART'},
                {data: 'HISR_NOMART'},
                {data: 'HISR_CANT'},
                {data: 'HISR_DIAS'},
                {data: 'HISR_INDICACIONES'},
                {data: 'HISR_CODART',
                    render: function() {
                        return "<div class='btn-group' role='group' aria-label='Small button group'><button type='button' id='selreceta' class='btn btn-primary'><i class='fa fa-edit'></i> Editar</button><button type='button' id='delreceta' class='btn btn-danger'><i class='fa fa-trash'></i> Eliminar</button></div>"
                    }
                },
               
            ],
            searching: false,
            paging: false,
            ordering: false,
            responsive: true,
            lengthChange: false,
            autoWidth: false,
        });

        $('#guardar_medicamento').click(function() {
            $.post(
                "<?= site_url('consultorio/guarda_receta') ?>", {
                    his: $("#HIS_CODHIS").val(),
                    car: $("#HISR_CODART").val(),
                    mar: $("#HISR_NOMART").val(),
                    cnt: $("#HISR_CANT").val(),
                    dia: $("#HISR_DIAS").val(),
                    ind: $("#HISR_INDICACIONES").val(),
                    upd: $("#HISR_UPD").val(),
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    dtableReceta.ajax.reload();                    
                    $("#HISR_CODART").val('');
                    $("#HISR_NOMART").val('');
                    $("#HISR_CANT").val('');
                    $("#HISR_DIAS").val('');
                    $("#HISR_INDICACIONES").val('');
                    $("#HISR_UPD").val('');
                }
            );
        });
        $('#finalizar_cita').click(function() {
            $.post(
                "<?= site_url('consultorio/confirma_cita') ?>", {
                    cit: $("#HIS_CODCIT").val(),
                    est: 2
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    history.back();
                }
            );
        });
        $('#pendiente_cita').click(function() {
            $.post(
                "<?= site_url('consultorio/confirma_cita') ?>", {
                    cit: $("#HIS_CODCIT").val(),
                    est: 3
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    history.back();
                }
            );
        });
        $('#table_receta tbody').on('click', '#selreceta', function(event) {
            var data = dtableReceta.row($(this).parents('tr')).data();
            $("#HISR_CODART").val(data.HISR_CODART);
            $("#HISR_NOMART").val(data.HISR_NOMART);
            $("#HISR_CANT").val(data.HISR_CANT);
            $("#HISR_DIAS").val(data.HISR_DIAS);
            $("#HISR_INDICACIONES").val(data.HISR_INDICACIONES);
            $("#HISR_UPD").val('1');
            $("#HISR_CANT").focus();
        });
        $('#table_receta tbody').on('click', '#delreceta', function(event) {
            var data = dtableReceta.row($(this).parents('tr')).data();
            $.post(
                "<?= site_url('consultorio/delete_receta') ?>", {
                    his: $("#HIS_CODHIS").val(),
                    car: data.HISR_CODART,
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    dtableReceta.ajax.reload();
                }
            );
        });
        $('#table_diag tbody').on('click', '#delcie', function(event) {
            var data = dtableDiag.row($(this).parents('tr')).data();
            $.post(
                "<?= site_url('consultorio/delete_cie') ?>", {
                    his: data.HISD_CODHIS,
                    cie: data.HISD_CIE_CODIGO,
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    dtableDiag.ajax.reload();
                }
            );
        });
        $('.openPopup').on('click', function() {
            var dataURL = $(this).attr('data-href');
            $('#ModalHistoria').find('.modal-body').load(dataURL, function() {
                $('#ModalHistoria').modal({
                    show: true
                }).on('hidden.bs.modal', function(e) {
                    $('#ModalHistoria').find('.modal-body').html('');
                });
            });
        });

        $('#CIE_TIPO').click(function() {
            doAjaxestado();
        });
        $("#HIS_PREART_MM_2,#HIS_PREART_HG_2,#HIS_TEMPERATURA_2,#HIS_PESO_2,#HIS_TALLA_2,#HIS_SATURACION_2,#HIS_FRE_CARD_2,#HIS_FRE_RESP_2,#HIS_EXA_CLI_2")
            .blur(function() {
                $("#HIS_PREART_MM").val($("#HIS_PREART_MM_2").val());
                $("#HIS_PREART_HG").val($("#HIS_PREART_HG_2").val());
                $("#HIS_TEMPERATURA").val($("#HIS_TEMPERATURA_2").val());
                $("#HIS_PESO").val($("#HIS_PESO_2").val());
                $("#HIS_TALLA").val($("#HIS_TALLA_2").val());
                $("#HIS_SATURACION").val($("#HIS_SATURACION_2").val());
                $("#HIS_FRE_CARD").val($("#HIS_FRE_CARD_2").val());
                $("#HIS_FRE_RESP").val($("#HIS_FRE_RESP_2").val());
                $("#HIS_EXA_CLI").val($("#HIS_EXA_CLI_2").val());
                doAjaxprocess();
            });

        $("#HIS_PREART_MM,#HIS_PREART_HG,#HIS_TEMPERATURA,#HIS_PESO,#HIS_TALLA,#HIS_SATURACION,#HIS_FRE_CARD,#HIS_FRE_RESP,#HIS_EXA_CLI,#HIS_PLAN_TRAB,#HIS_INDICACIONES")
            .blur(function() {
                doAjaxprocess();
            });
        $('#guardar_cita').click(function() {
            doAjaxprocess();
        });

        function doAjaxprocess() {
            $('#update_his').show();
            $('#ok_his').hide();
            $.post(
                "<?= site_url('consultorio/actualiza_historia') ?>", {
                    CODHIS: $("#HIS_CODHIS").val(),
                    CODCAM: $("#HIS_CODCAMP").val(),
                    CODCIT: $("#HIS_CODCIT").val(),
                    PREART_MM: $("#HIS_PREART_MM").val(),
                    PREART_HG: $("#HIS_PREART_HG").val(),
                    TEMPER: $("#HIS_TEMPERATURA").val(),
                    PESO: $("#HIS_PESO").val(),
                    TALLA: $("#HIS_TALLA").val(),
                    SATUR: $("#HIS_SATURACION").val(),
                    FRE_CARD: $("#HIS_FRE_CARD").val(),
                    FRE_RESP: $("#HIS_FRE_RESP").val(),
                    EXA_CLI: $("#HIS_EXA_CLI").val(),
                    PLAN_TRAB: $("#HIS_PLAN_TRAB").val(),
                    INDIC: $("#HIS_INDICACIONES").val(),
                    TRIAJE: $("#TRIAJE").val()
                },
                function(htmlexterno) {
                    var datos = eval(htmlexterno);
                    $('#update_his').hide();
                    $('#ok_his').show();
                    $(document).Toasts('create', {
                        class: 'bg-success',
                        title: 'Historia',
                        body: 'Se guardo correctamente',
                        position: 'bottomRight',
                        icon: 'far fa-check-circle fa-lg',
                        animation: true,
                        autohide: true,
                        delay: 2500
                    });
                }
            );
        }
        function doAjaxdiagnostico() {
            $('#update_his').show();
            $('#ok_his').hide();
            $.post(
                "<?= site_url('consultorio/guarda_diagnostico') ?>", {
                    his: $("#HIS_CODHIS").val(),
                    cie: $("#HISC_CIE10_CODIGO").val(),
                    dsc: $("#HISC_CIE10_DESCRIPCION option:selected").text()
                },
                function(htmlexterno) {
                    $('#update_his').hide();
                    $('#ok_his').show();
                    dtableDiag.ajax.reload();
                }
            );
        }
    });
</script>
<?= $this->endSection(); ?>