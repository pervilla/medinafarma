<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AdminLTE 3 | Invoice Print</title>

    <link rel="stylesheet" href="<?= site_url('plugins/fontawesome-free/css/all.min.css') ?>">

    <!-- Theme style -->
    <link rel="stylesheet" href="<?= site_url('dist/css/adminlte.min.css') ?>">

</head>

<body>
    <div class="wrapper">

        <section class="invoice">

            <div class="row">
                <div class="col-12">
                    <h2 class="page-header">

                        <img src="<?= site_url('../../dist/img/medinafarma-black.jpg') ?>" width="231" height="58"
                            hspace="29" vspace="13">
                        <small class="float-right">
                            <div style="border:2px solid #004269;border-radius:10px">
                                <table width="100%" border="0" height="120" cellpadding="4" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td align="center" bgcolor="#004269"
                                                style="border: 2px solid #004269;border-radius: 5px 5px 0px 0px;-moz-border-radius: 5px 5px 0px 0px;-webkit-border-radius: 5px 5px 0px 0px;">
                                                <span style="font-size:29px" text-align="center">
                                                    <font color="#FFFFFF">HISTORIA CLINICA</font>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <span style="font-family:Tahoma, Geneva, sans-serif; font-size:20px"
                                                    text-align="center">No.: <?=$historia->HIS_CODHIS?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <span style="font-family:Tahoma, Geneva, sans-serif; font-size:20px"
                                                    text-align="center">FECHA DE ATENCIÓN</span>
                                                <br>
                                                <span style="font-family:Tahoma, Geneva, sans-serif; font-size:20px"
                                                    text-align="center">14/05/2022</span>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </small>
                    </h2>
                </div>

            </div>
            </br>
            <div class="row">
                <div class="col-12">
                    <form class="form-horizontal">
                        <div class="form-group row">
                            <label for="inputEmail" class="col-sm-3 col-form-label">Nombre y Apellido</label>
                            <div class="col-sm-9">
                                <input type="numeric" class="form-control" value="<?=$clientes->CLI_NOMBRE?>">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="inputEmail" class="col-sm-3 col-form-label">Fecha de Nacimiento</label>
                            <div class="col-sm-1">
                                <input type="numeric" class="form-control" id="HIS_PESO" placeholder="0.00" value="<?php
                                    $timestamp = strtotime($clientes->CLI_FECHA_NAC); 
                                    $newDate = date("d/m/Y", $timestamp );
                                echo $newDate;
                            ?>">
                            </div>
                            <label for="inputEmail" class="col-sm-1 col-form-label">D.N.I:</label>
                            <div class="col-sm-1">
                                <input type="numeric" class="form-control" id="HIS_TALLA" placeholder="0.00"
                                    value="<?=$clientes->CLI_RUC_ESPOSA?>">
                            </div>
                            <label for="inputEmail" class="col-sm-1 col-form-label">EDAD</label>
                            <div class="col-sm-2">
                                <input type="numeric" class="form-control" value="<?php
                                $date = new DateTime();
                                $string_date = $date->format('Y-m-d');
                                $date1=date_create($clientes->CLI_FECHA_NAC);
                                $date2=date_create( $string_date);
                                $diff=date_diff($date1,$date2);
                                echo $diff->format('%y Años %m Meses %d Día');
                            ?>">
                            </div>
                            <div class="col-sm-3"></div>
                        </div>


                        <div class="form-group row">
                            <label for="inputEmail" class="col-sm-3 col-form-label">Dirección</label>
                            <div class="col-sm-9">
                                <input type="numeric" class="form-control" value="<?=$clientes->CLI_CASA_DIREC?>">
                            </div>
                        </div>
                        <div class="form-group row">

                            <label for="inputEmail" class="col-sm-3 col-form-label">Celular</label>
                            <div class="col-sm-1">
                                <input type="numeric" class="form-control" value="<?=$clientes->CLI_TELEF1?>">
                            </div>
                            <div class="col-sm-8"></div>
                        </div>
                        <hr>

                        <div class="form-group row">
                            <label for="inputName" class="col-sm-3 col-form-label">
                                <a href="#"><i class="fas fa-heartbeat"></i></a>
                                Presión Arterial (mm Hg)</label>
                            <div class="col-sm-1">
                                <input type="numeric" class="form-control" id="HIS_PREART_MM" placeholder="mm"
                                    value="<?=$historia->HIS_PREART_MM?>">
                            </div>
                            <div class="col-sm-1">
                                <input type="numeric" class="form-control" id="HIS_PREART_HG" placeholder="Hg"
                                    value="<?=$historia->HIS_PREART_HG?>">
                            </div>
                            <div class="col-sm-1"></div>
                            <label for="inputName" class="col-sm-3 col-form-label">Frecuencia Cardiaca
                                (Latidos x Min)</label>
                            <div class="col-sm-3">
                                <input type="numeric" class="form-control" id="HIS_FRE_CARD" placeholder="Latidos"
                                    value="<?=$historia->HIS_FRE_CARD?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail" class="col-sm-3 col-form-label">
                                <a href="#"><i class="fab fa-cloudsmith"></i></a>
                                Saturación</label>
                            <div class="col-sm-1">
                                <input type="numeric" class="form-control" id="HIS_SATURACION" placeholder="Hg"
                                    value="<?=$historia->HIS_SATURACION?>">
                            </div>
                            <label for="inputName" class="offset-sm-2 col-sm-3 col-form-label">Frecuencia
                                Respiratoria x Min</label>
                            <div class="col-sm-3">
                                <input type="numeric" class="form-control" id="HIS_FRE_RESP" placeholder=""
                                    value="<?=$historia->HIS_FRE_RESP?>">
                            </div>
                        </div>

                        <div class="form-group row">

                            <label for="inputEmail" class="col-sm-3 col-form-label">
                                <a href="#"><i class="fas fa-temperature-high"></i></a>
                                Temperatura Corporal (ºC)</label>
                            <div class="col-sm-1">
                                <input type="numeric" class="form-control" id="HIS_TEMPERATURA" placeholder="0.00"
                                    value="<?=$historia->HIS_TEMPERATURA?>">
                            </div>
                            <div class="col-sm-8"></div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail" class="col-sm-3 col-form-label">
                                <a href="#"><i class="fas fa-weight"></i></a>
                                Peso (Kg)</label>
                            <div class="col-sm-1">
                                <input type="numeric" class="form-control" id="HIS_PESO" placeholder="0.00"
                                    value="<?=$historia->HIS_PESO?>">
                            </div>
                            <label for="inputEmail" class="col-sm-1 col-form-label">Talla (M)</label>
                            <div class="col-sm-1">
                                <input type="numeric" class="form-control" id="HIS_TALLA" placeholder="0.00"
                                    value="<?=$historia->HIS_TALLA?>">
                            </div>
                            <label for="inputEmail" class="col-sm-1 col-form-label">IMC</label>
                            <div class="col-sm-1">
                                <input type="numeric" class="form-control" id="HIS_IMC" placeholder="">
                            </div>
                            <div class="col-sm-4"></div>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail" class="col-sm-3 col-form-label"> </label>
                            <div class="col-sm-9">
                                <textarea id="HIS_EXA_CLI" name="HIS_EXA_CLI" class="form-control"
                                    rows="4"><?=$historia->HIS_EXA_CLI?></textarea>
                            </div>
                        </div>
                    </form>
                </div>


            </div>
            <div class="callout callout-danger">
                <h5>DIAGNOSTICO</h5>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <table id="table_diag" class="table table-bordered dataTable no-footer dtr-inline" role="grid"
                        aria-describedby="table_diag_info">
                        <thead>
                            <tr role="row">
                                <th class="sorting sorting_asc" tabindex="0" aria-controls="table_diag" rowspan="1"
                                    colspan="1" aria-label="Código: activate to sort column descending"
                                    aria-sort="ascending">Código</th>
                                <th class="sorting" tabindex="0" aria-controls="table_diag" rowspan="1" colspan="1"
                                    aria-label="Descripción Diagnóstico: activate to sort column ascending">Descripción
                                    Diagnóstico</th>
                                <th class="sorting" tabindex="0" aria-controls="table_diag" rowspan="1" colspan="1"
                                    aria-label="Tipo Diagnóstico: activate to sort column ascending">Tipo Diagnóstico
                                </th>
                                <th class="sorting" tabindex="0" aria-controls="table_diag" rowspan="1" colspan="1"
                                    aria-label="Caso Diagnóstico: activate to sort column ascending">Caso Diagnóstico
                                </th>
                                <th class="sorting" tabindex="0" aria-controls="table_diag" rowspan="1" colspan="1"
                                    aria-label="Alta al Diagnóstico?: activate to sort column ascending">Alta al
                                    Diagnóstico?</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($diagnostico as $val) { ?>
                            <tr>
                                <td><?=$val->HISD_CIE_CODIGO?></td>
                                <td><?=$val->HISD_CIE_DESCRIPCION ?></td>
                                <td><?=$tipo_diagnostico[$val->HISD_TIPO] ?></td>
                                <td><?=$caso_diagnostico[$val->HISD_CASO] ?></td>
                                <td><?=$alta_diagnostico[$val->HISD_ALTA] ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="callout callout-danger">
                <h5>PLAN DE TRABAJO</h5>
            </div>

            <div class="timeline-item">
                <div class="timeline-body">
                    <textarea id="HIS_PLAN_TRAB" name="HIS_PLAN_TRAB" class="form-control"
                        rows="4"><?=$historia->HIS_PLAN_TRAB?></textarea>
                </div>
            </div>

            <div class="callout callout-danger">
                <h5>INDICACIONES</h5>
            </div>

            <div class="timeline-item">
                <div class="timeline-body">
                    <textarea id="HIS_INDICACIONES" name="HIS_INDICACIONES" class="form-control"
                        rows="4"><?=$historia->HIS_INDICACIONES?></textarea>
                </div>
            </div>

            <div class="callout callout-danger">
                <h5>RECETA</h5>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <table id="table_receta" class="table table-bordered dataTable no-footer dtr-inline" role="grid"
                        aria-describedby="table_receta_info">
                        <thead>
                            <tr role="row">
                                <th>Nro</th>
                                <th>Código</th>
                                <th>Días</th>
                                <th>Cant.</th>
                                <th>Indicaciones</th>                                
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                            foreach ($receta as $val) { ?>
                            <tr>
                                <td><?=$val->HISR_CODART?></td>
                                <td><?=$val->HISR_NOMART ?></td>
                                <td><?=$val->HISR_CANT ?></td>
                                <td><?=$val->HISR_DIAS ?></td>
                                <td><?=$val->HISR_INDICACIONES ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>                    
                </div>
            </div>
        </section>
    </div>
    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <!-- Theme style -->
<script>
  //window.addEventListener("load", window.print());
</script>
</body>

</html>