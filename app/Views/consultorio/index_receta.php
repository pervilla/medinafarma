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
    <?php $session = session(); ?>
    <table style="border-collapse: collapse; width: 100%;">
        <tbody>
            <tr>
                <td style="width: 50%; border-right: 1px solid #000000;">
                    <table style="border-collapse: collapse; width: 100%;">
                        <tr>
                            <td><img src="<?= site_url('../../dist/img/medinafarma-black.jpg') ?>" width="231"
                                    height="58" hspace="29" vspace="13"></td>
                            <td align="right" style="padding: 10px;font-weight: bold;">Historia:
                                <?=str_pad($historia->HIS_CODHIS,8,"0", STR_PAD_LEFT);?></td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; border-right: 1px solid #000000;">
                    <table style="border-collapse: collapse; width: 100%;">
                        <tr>
                            <td><img src="<?= site_url('../../dist/img/medinafarma-black.jpg') ?>" width="231"
                                    height="58" hspace="29" vspace="13"></td>
                            <td align="right" style="padding: 10px;font-weight: bold;">Historia:
                                <?=str_pad($historia->HIS_CODHIS,8,"0", STR_PAD_LEFT);?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="width: 50%; border-right: 1px solid #000000; border-bottom: 1px solid #000000;">
                    <table style="border-collapse: collapse; width: 100%;" border="0">
                        <tbody>
                            <tr>
                                <td>Paciente</td>
                                <td>:</td>
                                <td style="font-weight: bold;"><?=$clientes->CLI_NOMBRE?></td>
                            </tr>
                            <tr>
                                <td valign="top">Cie 10</td>
                                <td valign="top">:</td>
                                <td>
                                    <table style="border-collapse: collapse; width: 100%;">
                                        <?php foreach ($diagnostico as $val) { ?>
                                        <tr>
                                            <td style="font-weight: bold;"><?=$val->HISD_CIE_CODIGO?></td>
                                            <td><?php /**$val->HISD_CIE_DESCRIPCION*/ ?></td>
                                        </tr>
                                        <?php } ?>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 50%; border-right: 1px solid #000000; border-bottom: 1px solid #000000;">
                    <table style="border-collapse: collapse; width: 100%;" border="0">
                        <tbody>
                            <tr>
                                <td>Paciente</td>
                                <td>:</td>
                                <td style="font-weight: bold;"><?=$clientes->CLI_NOMBRE?></td>
                            </tr>
                            <tr>
                                <td valign="top">Cie 10</td>
                                <td valign="top">:</td>
                                <td>
                                    <table style="border-collapse: collapse; width: 100%;">
                                        <?php foreach ($diagnostico as $val) { ?>
                                        <tr>
                                            <td style="font-weight: bold;"><?=$val->HISD_CIE_CODIGO?></td>
                                            <td><?php /**$val->HISD_CIE_DESCRIPCION*/ ?></td>
                                        </tr>
                                        <?php } ?>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td valign="top" style="width: 50%; border-right: 1px solid #000000; ">
                    <table style="border-collapse: collapse; width: 100%;" border="0">
                        <tbody>
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="width: 10%; height: 18px;font-weight: bold;">N&ordm;</td>
                                <td style="width: 70%; height: 18px;font-weight: bold;">Denominaci&oacute;n</td>
                                <td style="width: 10%; height: 18px;font-weight: bold;">Cant.</td>
                                <td style="width: 10%; height: 18px;font-weight: bold;">D&iacute;as</td>
                                
                            </tr>
                            <?php $i=1;
                            foreach ($receta as $val) { ?>
                            <tr>
                                <td><?=$i++?></td>
                                <td><?=$val->HISR_NOMART ?></td>
                                <td><?=$val->HISR_CANT ?></td>
                                <td><?=$val->HISR_DIAS ?></td>

                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </td>
                <td valign="top" style="width: 50%; border-right: 1px solid #000000; ">
                    <table style="border-collapse: collapse; width: 100%;" border="0">
                        <tbody>
                            <tr style="border-bottom: 1px solid #000000;">
                                <td style="width: 10%; height: 18px;font-weight: bold;">N&ordm;</td>
                                <td style="width: 70%; height: 18px;font-weight: bold;">Denominaci&oacute;n</td>
                                <td style="width: 10%; height: 18px;font-weight: bold;">Cant.</td>
                                <td style="width: 10%; height: 18px;font-weight: bold;">D&iacute;as</td>
                            </tr>
                            <?php $i=1;
                            foreach ($receta as $val) { ?>
                            <tr>
                                <td><?=$i++?></td>
                                <td><?=$val->HISR_NOMART ?></td>
                                <td><?=$val->HISR_CANT ?></td>
                                <td><?=$val->HISR_DIAS ?></td>

                            </tr>
                            <tr>
                                <td></td>
                                <td colspan='3'>Ind: <?=$val->HISR_INDICACIONES ?></td>

                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="width: 50%; border-right: 1px solid #000000;">
                    <table style="border-collapse: collapse; width: 100%;" border="0">
                        <tbody>
                            <tr>
                                <td style="height: 100px;">&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="width: 50%; height: 30px;">&nbsp;</td>
                                <td style="border-top: 1px solid #000000; padding:5px; text-align: center;font-weight: bold;">Firma y Sello del M&eacute;dico
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 50%; border-right: 1px solid #000000;">
                    <table style="border-collapse: collapse; width: 100%;" border="0">
                        <tbody>
                            <tr>
                                <td style="height: 100px;">&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="width: 50%; height: 30px;">&nbsp;</td>
                                <td style="border-top: 1px solid #000000; padding:5px; text-align: center;font-weight: bold;">Firma y Sello del M&eacute;dico
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="width: 50%; border-right: 1px solid #000000; border-top: 1px solid #000000;">
                    <table style="border-collapse: collapse; width: 100%;" border="0">
                        <tbody>
                            <tr>
                                <td style="width: 33.3333%;">Usuario:
                                    <?= $session->get('user_id')?$session->get('user_name'):"Invitado"; ?></td>
                                <td style="width: 33.3333%;">Fecha Impresion: <?=date('d-m-Y')?></td>
                                <td style="width: 33.3333%;">Hora Impresion: <?=date('h:m:s')?></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="width: 50%; border-right: 1px solid #000000; border-top: 1px solid #000000;">
                    <table style="border-collapse: collapse; width: 100%;" border="0">
                        <tbody>
                            <tr>
                                <td style="width: 33.3333%;">Usuario:
                                    <?= $session->get('user_id')?$session->get('user_name'):"Invitado"; ?></td>
                                <td style="width: 33.3333%;">Fecha Impresion: <?=date('d-m-Y')?></td>
                                <td style="width: 33.3333%;">Hora Impresion: <?=date('h:m:s')?></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

        </tbody>
    </table>


</body>

</html>