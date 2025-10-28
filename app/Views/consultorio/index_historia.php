<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AdminLTE 3 | Invoice Print</title>

    <link rel="stylesheet" href="<?= site_url('plugins/fontawesome-free/css/all.min.css') ?>">

    <!-- Theme style -->
    <link rel="stylesheet" href="<?= site_url('dist/css/adminlte.min.css') ?>">
    <style>
        .titulos{
                print-color-adjust:exact;
                -webkit-print-color-adjust:exact;
                font-size: large;
                border-radius: 10px;
            }
            .historia{
                border-radius: 6px 6px 0px 0px;
                -moz-border-radius: 6px 6px 0px 0px;
                -webkit-border-radius: 6px 6px 0px 0px;
                background-color: #004269;
                print-color-adjust:exact;
                -webkit-print-color-adjust:exact;
            }
    </style>
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
                                            <td align="center" class="historia">
                                                <span style="font-size:29px" text-align="center">
                                                    <font color="#FFFFFF">HISTORIA CLINICA</font>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <span style="font-family:Tahoma, Geneva, sans-serif; font-size:20px"
                                                    text-align="center">Nro: <?=$historia->HIS_CODHIS?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <span style="font-family:Tahoma, Geneva, sans-serif; font-size:20px"
                                                    text-align="center">FECHA DE ATENCIÓN</span>
                                                <br>
                                                <span style="font-family:Tahoma, Geneva, sans-serif; font-size:20px"
                                                    text-align="center"><?=date("Y-m-d H:i", strtotime($historia->HIS_FECHA_ATE))?></span>
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
                    <table border=0 cellpadding=0 cellspacing=0 width='100%'>             
                    <tr>
                            <td width=80>Código</td>
                            <td width=11>:</td>
                            <td colspan=7 class=xl70 width=577><?=$clientes->CLI_CODCLIE?></td>
                        </tr>
                        <tr>
                            <td width=80>Paciente</td>
                            <td width=11>:</td>
                            <td colspan=7 class=xl70 width=577><?=$clientes->CLI_NOMBRE?></td>
                        </tr>
                        <tr>
                            <td>Fecha Nac.</td>
                            <td>:</td>
                            <td class=xl67 align=left><?=date("d/m/Y",strtotime($clientes->CLI_FECHA_NAC))?></td>
                            <td align=right>DNI:</td>
                            <td class=xl67 align=center><?=$clientes->CLI_RUC_ESPOSA?></td>
                            <td align=right>EDAD :</td>
                            <td colspan=3 class=xl68 ><?php
                                $date = new DateTime();
                                $string_date = $date->format('Y-m-d');
                                $date1=date_create($clientes->CLI_FECHA_NAC);
                                $date2=date_create( $string_date);
                                $diff=date_diff($date1,$date2);
                                echo $diff->format('%y Años %m Meses %d Día');
                            ?></td>
                        </tr>
                        <tr>
                            <td >Dirección</td>
                            <td>:</td>
                            <td colspan=7 class=xl70><?=$clientes->CLI_CASA_DIREC?></td>
                        </tr>
                        <tr>
                            <td >Celular</td>
                            <td>:</td>
                            <td colspan=7 style='mso-ignore:colspan'><?=$clientes->CLI_TELEF1?></td>
                        </tr>
                        <tr >
                            <td colspan=9 style='height:15.0pt;mso-ignore:colspan'></td>
                        </tr>
                        <tr >
                            <td colspan=9 class="text-center bg-lightblue titulos" >DATOS DE LA ATENCION</td>
                        </tr>
                        <tr >
                            <td >Gestando<span style='mso-spacerun:yes'> </span></td>
                            <td>:</td>
                            <td>No</td>
                            <td colspan=6 style='mso-ignore:colspan'></td>
                        </tr>
                        <tr >
                            <td >Anamnesis</td>
                            <td>:</td>
                            <td colspan=7><?=$historia->HIS_EXA_CLI?></td>
                        </tr>
                        
                        <tr >
                            <td colspan=9 class="text-center bg-lightblue titulos" >SIGNOS VITALES</td>
                        </tr>
                        <tr >
                            <td>Peso</td>
                            <td>:</td>
                            <td align=center><?=$historia->HIS_PESO?></td>
                            <td align=right>Sat.de Oxg :</td>
                            <td align=center><?=$historia->HIS_SATURACION?></td>
                            <td align=right>Pre.Art.Med :</td>
                            <td align=center><?=$historia->HIS_PREART_MM?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr >
                            <td >Fec. Card.</td>
                            <td>:</td>
                            <td align=center><?=$historia->HIS_FRE_CARD?></td>
                            <td align=right>Temp.Corp :</td>
                            <td align=center><?=$historia->HIS_TEMPERATURA?></td>
                            <td align=right>Pr.Ven.Ctrl :</td>
                            <td align=center><?=$historia->HIS_PREART_HG?></td>
                            <td align=right>Frec. Resp. :</td>
                            <td align=center><?=$historia->HIS_FRE_RESP?></td>
                        </tr>
                        <tr >
                            <td colspan=9 style='height:15.0pt;mso-ignore:colspan'></td>
                        </tr>
                        <tr >
                            <td colspan=9 class="text-center bg-lightblue titulos">ANTROPOMETRIA</td>
                        </tr>
                        <tr >
                            <td >Peso</td>
                            <td>:</td>
                            <td align=center><?=$historia->HIS_PESO?></td>
                            <td align=right>Talla :</td>
                            <td align=center><?=$historia->HIS_TALLA?></td>
                            <td colspan=4 style='mso-ignore:colspan'></td>
                        </tr>
                        <tr >
                            <td colspan=9 style='height:15.0pt;mso-ignore:colspan'></td>
                        </tr>
                        <?php if($historia->HIS_INDICACIONES){?>
                        <tr >
                            <td colspan=9 class="text-center bg-lightblue titulos">EXAMEN FISICO</td>
                        </tr>
                        <tr >
                            <td colspan=9 ><textarea id="HIS_INDICACIONES"
                                    name="HIS_INDICACIONES" class="form-control"
                                    rows="4"><?=$historia->HIS_INDICACIONES?></textarea></td>
                        </tr>
                        <tr >
                            <td colspan=9 style='height:15.0pt;mso-ignore:colspan'></td>
                        </tr>
                        <?php } if($historia->HIS_PLAN_TRAB){ ?>
                        <tr >
                            <td colspan=9 class="text-center bg-lightblue titulos">PLAN DE TRABAJO /
                                COMENTARIO Y/O OBSERVACIONES</td>
                        </tr>
                        <tr >
                            <td colspan=9 >
                                <textarea id="HIS_PLAN_TRAB" name="HIS_PLAN_TRAB" class="form-control"
                                    rows="4"><?=$historia->HIS_PLAN_TRAB?></textarea>
                            </td>
                        </tr>
                        <tr >
                            <td colspan=9 style='height:15.0pt;mso-ignore:colspan'></td>
                        </tr>
                        <?php } if(!empty($diagnostico)){ ?>
                        <tr >
                            <td colspan=9 class="text-center bg-lightblue titulos">DIAGNOSTICO</td>
                        </tr>
                        <tr >
                            <td colspan=9 class=xl66 >
                                <table id="table_diag" class="table table-bordered dataTable no-footer dtr-inline"
                                    role="grid" aria-describedby="table_diag_info">
                                    <thead>
                                        <tr role="row">
                                            <th class="sorting sorting_asc" tabindex="0" aria-controls="table_diag"
                                                rowspan="1" colspan="1"
                                                aria-label="Código: activate to sort column descending"
                                                aria-sort="ascending">Código</th>
                                            <th class="sorting" tabindex="0" aria-controls="table_diag" rowspan="1"
                                                colspan="1"
                                                aria-label="Descripción Diagnóstico: activate to sort column ascending">
                                                Descripción
                                                Diagnóstico</th>
                                            <th class="sorting" tabindex="0" aria-controls="table_diag" rowspan="1"
                                                colspan="1"
                                                aria-label="Tipo Diagnóstico: activate to sort column ascending">Tipo
                                                Diagnóstico
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="table_diag" rowspan="1"
                                                colspan="1"
                                                aria-label="Caso Diagnóstico: activate to sort column ascending">Caso
                                                Diagnóstico
                                            </th>
                                            <th class="sorting" tabindex="0" aria-controls="table_diag" rowspan="1"
                                                colspan="1"
                                                aria-label="Alta al Diagnóstico?: activate to sort column ascending">
                                                Alta al
                                                Diagnóstico?</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php                                         
                                        if (!is_null($diagnostico)){
                                            foreach ($diagnostico as $val) { ?>
                                                <tr>
                                                    <td><?=$val->HISD_CIE_CODIGO?></td>
                                                    <td><?=$val->HISD_CIE_DESCRIPCION ?></td>
                                                    <td><?=$tipo_diagnostico[$val->HISD_TIPO] ?></td>
                                                    <td><?=$caso_diagnostico[$val->HISD_CASO] ?></td>
                                                    <td><?=$alta_diagnostico[$val->HISD_ALTA] ?></td>
                                                </tr>
                                                <?php } 

                                        }
                            ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr >
                            <td colspan=9 style='height:15.0pt;mso-ignore:colspan'></td>
                        </tr>
                        <?php } if(!empty($receta)){ ?>
                        <tr >
                            <td colspan=9 class="text-center bg-lightblue titulos">RECETA</td>
                        </tr>
                        <tr >
                            <td colspan=9 class=xl66 >
                                <table id="table_receta" class="table table-bordered dataTable no-footer dtr-inline"
                                    role="grid" aria-describedby="table_receta_info">
                                    <thead>
                                        <tr role="row">
                                            <th>Nro</th>
                                            <th>Código</th>
                                            <th>Días</th>
                                            <th>Cant.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                         if (is_array($receta)){
                                            foreach ($receta as $valr) { ?>
                                            <tr >
                                                <td rowspan=2><?=$valr->HISR_CODART?></td>
                                                <td><strong><?=$valr->HISR_NOMART ?></strong></td>
                                                <td><?=$valr->HISR_DIAS ?></td>
                                                <td><?=$valr->HISR_CANT ?></td>
                                            </tr>
                                            <tr >
                                                <td colspan=3>Ind: <?=$valr->HISR_INDICACIONES ?></td>
                                            </tr>
                                        <?php } }?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <?php } ?>



                    </table>

                </div>


            </div>


        </section>
    </div>
    <!-- Theme style -->
    <script>
    //window.addEventListener("load", window.print());
    </script>
</body>

</html>