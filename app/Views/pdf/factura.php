<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0% !important;
        }

        .bgfactura {
            background-size: cover;
            height: 100%;
            width: 100%;
        }
        
        *,
        ::after,
        ::before {
            font-family: "Source Sans Pro", "Segoe UI", "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            font-weight: 350;
        }

        h5 {
            margin-top: 0;
            margin-bottom: .5rem;
            margin-bottom: .5rem;
            font-family: inherit;
            font-weight: 500;
            line-height: 1.2;
            color: inherit;
            font-size: 1.25rem;
        }

        .row {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: 17.5px;
            margin-left: 17.5px;
        }

        .card {
            position: relative;
            border: 2px solid rgb(240, 200, 0); /* #00aeef en RGBA */
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .card-body {
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            min-height: 1px;
            padding: 1.25rem;
        }

        .card-header {
            padding: .75rem 1.25rem;
            margin-bottom: 0;
            background-color:  rgb(255, 0, 0); /* #214195 en RGBA (opaco) */
            border-bottom: 1px solid rgb(240, 200, 0); /* #00aeef en RGBA (opaco) */
            border-radius: calc(.25rem - 0) calc(.25rem - 0) 0 0;
            position: relative;
            border-top-left-radius: .25rem;
            border-top-right-radius: .25rem;
            color: white; /* Texto blanco para mejor contraste */
        }

        .card-footer {
            padding: .75rem 1.25rem;
            background-color: rgb(255, 0, 0); /* Fondo azul oscuro (sin transparencia) */
            border-top: 1px solid rgb(240, 200, 0); /* Borde superior azul claro */
            border-radius: 0 0 calc(.25rem - 0) calc(.25rem - 0);
            color: white; /* Texto en blanco para contraste (opcional) */
        }

        .bg-secondary {
            background-color: #6c757d !important;
        }

        .mr-4 {
            margin-right: 1.5rem !important;
        }

        .p-2 {
            padding: .5rem !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-bold {
            font-weight: bold;
        }

        .mb-1 {
            margin-bottom: .25rem !important;
        }

        table {
            font-size: 0.75rem;
        }

        .color-gris {
            color: #fff;
            background-color: #6c757d;
        }

        .color-blanco {
            color: #495057;
            background-color: #fff;
        }

        .tr-inicio {
            padding: 0.375rem 0.75rem;
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
            border-color: #6c757d;
            -webkit-print-color-adjust: exact;
            box-shadow: inset 0 0 0 transparent;
        }

        .tr-fin {
            padding: 0.375rem 0.75rem;
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
            border-right: 1px solid #ced4da;
            border-bottom: 1px solid #ced4da;
            border-top: 1px solid #ced4da;
            -webkit-print-color-adjust: exact;
            box-shadow: inset 0 0 0 transparent;
        }

        .tr-medio {
            padding: 0.375rem 0.75rem;
            line-height: 1.5;
            border-bottom: 1px solid #ced4da;
            border-top: 1px solid #ced4da;
            box-shadow: inset 0 0 0 transparent;
        }

        .col1 {
            width: 10%;
        }

        .col2 {
            width: 20%;
        }

        .col3 {
            width: 30%;
        }

        .col4 {
            width: 40%;
        }

        .col5 {
            width: 50%;
        }

        .col6 {
            width: 60%;
        }

        .col7 {
            width: 70%;
        }

        .col8 {
            width: 80%;
        }

        .col9 {
            width: 90%;
        }

        .tablefact {
            display: table;
            border-collapse: separate;
            box-sizing: border-box;
            text-indent: initial;
            line-height: normal;
            font-weight: normal;
            font-size: 12px; /* Tamaño de fuente reducido (antes era 'medium') */
            font-style: normal;
            color: -internal-quirk-inherit;
            text-align: start;
            border-spacing: 2px;
            border-color: gray;
            white-space: normal;
            font-variant: normal;
            border-collapse: collapse !important;
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            background-color: transparent;
            font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
        }

        .theadfact {}

        .thead-dark {
            color: #fff;
            background-color: #212529;
            border-color: #383f45;
        }

        .table td {
            padding: .75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, .05);
        }

        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        p {
            margin-top: 0;
            margin-bottom: 1rem;
            orphans: 3;
            widows: 3;
        }

        .text-right {
            text-align: right !important;
        }

        .text-success {
            color: #28a745 !important;
        }
    </style>
</head>
<?php

$svgContent = '<svg data-name="Capa 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 595.28 841.89"><path d="M90.39 781.71c0-70.13-36.91-131.82-92.31-166.69v3.41c53.73 34.51 89.4 94.8 89.4 163.28 0 20.89-3.33 41.02-9.47 59.89h3.05c6.05-18.9 9.33-39.02 9.33-59.89M593.58 82.98C542.72 74.36 499.54 42.8 475.11-.65h2.83c24.11 42.08 66.15 72.61 115.64 81.12z" style="fill-rule:evenodd;stroke-width:0;fill:#f0c800"/><path d="M.99 841.6c43.85-25.52 94.39-40.78 148.36-42.52h20.71c53.97 1.74 104.46 17 148.23 42.52zM581.45-.65c-42.05 22.94-89.89 36.59-140.77 38.23h-20.73c-50.87-1.64-98.66-15.29-140.64-38.23z" style="fill-rule:evenodd;stroke-width:0;fill:red"/></svg>';
$base64Svg = 'data:image/svg+xml;base64,' . base64_encode($svgContent);
helper('logo');
$base64Logo = logo_medinafarma();

if($facart[0]->FAR_FBG=='B'){
    $documento= "B0";
    $tipoCom= "BOLETA ELECTRONICA";
    $tipoDoc="D.N.I.";
    $direccion="";
    $NroDoc=empty(trim($facart[0]->CLI_RUC_ESPOSA))?'':$facart[0]->CLI_RUC_ESPOSA."\n";
}elseif ($facart[0]->FAR_FBG=='F') {
    $documento= "FA";
    $tipoCom= "FACTURA ELECTRONICA";
    $NroDoc=TRIM($facart[0]->CLI_RUC_ESPOSO)."\n";
    $tipoDoc="R.U.C.";
    $direccion =TRIM($facart[0]->CLI_CASA_DIREC).' '.TRIM($facart[0]->CLI_CASA_NUM)."\n";
}elseif ($facart[0]->FAR_FBG=='G') {
    $documento= "G0";
    $tipoCom= "GUIA ELECTRONICA";
    $NroDoc='';
    $tipoDoc="";
    $direccion="";
}
$cliente = $facart[0]->FAR_CODCLIE==1?$facart[0]->FAR_CLIENTE:$facart[0]->CLI_NOMBRE;

$nro_Boleta = $documento.TRIM($facart[0]->FAR_NUMSER)."-".$facart[0]->FAR_NUMFAC;

?>
<body>
<img src="<?=$base64Svg ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;">
    <div class="">
        <table cellpadding="0" cellspacing="0" width='100%'>
            <tr>
                <td width="20" style="padding-left: 15px">
                    <!-- Logo de la empresa aquí -->
                    <img style="width:350px!important;" alt="" src="<?=$base64Logo?>" />
                </td>
                <td width="10"></td>
                <td width="30" class="text-right" style="padding-top: 15px; padding-right: 15px;">
                    <div class="card">
                        <div class="card-header text-center p-2">
                            <h5 class="m-0">R.U.C. <?= isset($empresa_ruc) ? $empresa_ruc : '20450337839' ?></h5>
                        </div>
                        <div class="card-body text-center p-2"
                            style="border-right: 1px solid #ced4da; border-left: 1px solid #ced4da;">
                            <strong><?=$tipoCom?></strong>
                        </div>
                        <div class="card-footer text-center p-2 ">
                            <strong><?=$nro_Boleta ?></strong>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="row mb-1 m-0">
        <table cellpadding="0" cellspacing="0" width='100%'>
            <tr>
                <td colspan="2" class="text-bold">INVERSIONES SAN MARTIN S.C.R.L.</td>
                <td class="text-right">Página 1/1</td>
            </tr>
            <tr>
                <td>Domicilio Fiscal</td>
                <td>: JR. HUALLAGA NRO 601 - JUANJUI - MARISCAL CACERES - SAN MARTIN</td>
                <td class="text-right"><?= isset($facart[0]) ? date('d/m/Y', strtotime($facart[0]->FAR_FECHA)) : date('d/m/Y') ?></td>
            </tr>
            
            <tr>
                <td>Teléfono Tienda</td>
                <td>: 930 487 039</td>
                <td></td>
            </tr><tr>
                <td></td>
                <td>&nbsp;</td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="row mb-1 m-0 ">
        <table cellpadding="0" cellspacing="0" width='100%'>
            <tr>
                <td class="tr-inicio col1 color-gris">CLIENTE</td>
                <td class="tr-medio col6 color-blanco"><?= $cliente ?></td>
                <td class="tr-medio col1 color-gris"><?=$tipoDoc?></td>
                <td class="tr-fin col1 color-blanco"><?= $NroDoc ?></td>
            </tr>
        </table>
    </div>
    
    <div class="row mb-1 m-0 ">
        <table cellpadding="0" cellspacing="0" width='100%'>
            <tr>
                <td class="tr-inicio col1 color-gris">DIRECCION</td>
                <td class="tr-fin col9 color-blanco"><?= $direccion ?></td>
            </tr>
        </table>
    </div>

    <div class="row mb-1 m-0">
        <table cellpadding="0" cellspacing="0" width='100%'>
            <tr>
                <td class="tr-inicio col2 color-gris">CONDICION PAGO</td>
                <td class="tr-medio col2 color-blanco">CONTADO</td>
                <td class="tr-medio col1 color-gris">MONEDA</td>
                <td class="tr-medio col2 color-blanco"><?= isset($facart[0]) ? ($facart[0]->FAR_MONEDA == 'S' ? 'SOLES' : 'DOLARES') : 'SOLES' ?></td>
                <td class="tr-medio col1 color-gris">VENDEDOR</td>
                <td class="tr-fin col2 color-blanco"><?= isset($facart[0]) ? trim($facart[0]->VEM_NOMBRE) : 'VENDEDOR' ?></td>
            </tr>
        </table>
    </div>
    
    <div class="row mb-1 m-0">
        <div class="table-responsive">
            <table class="tablefact custom-table m-0 table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Und.</th>
                        <th>Precio</th>
                        <th>Sub Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($facart) && is_array($facart)): ?>
                        <?php foreach ($facart as $item): ?>
                        <tr>
                            <td>#<?= $item->ART_KEY ?></td>
                            <td><?= trim($item->ART_NOMBRE) ?></td>
                            <td class="text-center"><?= (float)($item->FAR_CANTIDAD_P / ($item->FAR_EQUIV ?: 1)) ?></td>
                            <td class="m-0 text-center"><?= trim($item->FAR_DESCRI) ?></td>
                            <td class="text-right">S/. <?= number_format($item->FAR_PRECIO, 2) ?></td>
                            <td class="text-right">S/. <?= number_format($item->FAR_PRECIO * $item->FAR_CANTIDAD_P / ($item->FAR_EQUIV ?: 1), 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <tr>
                        <td colspan="3">
                            <!-- QR Code aquí -->
                            <?php 
                            // Generar cadena para QR
                            if (isset($facart[0])) {
                                $fecha = date('Y-m-d', strtotime($facart[0]->FAR_FECHA));
                                $serie = trim($facart[0]->FAR_NUMSER);
                                $numero = str_pad($facart[0]->FAR_NUMFAC, 8, '0', STR_PAD_LEFT);
                                
                                // Calcular totales
                                $subtotal = 0;
                                if (isset($facart) && is_array($facart)) {
                                    foreach ($facart as $item) {
                                        $subtotal += $item->FAR_PRECIO * $item->FAR_CANTIDAD_P / ($item->FAR_EQUIV ?: 1);
                                    }
                                }
                                $igv = $subtotal * 0.18;
                                $total = $subtotal + $igv;
                                
                                $cadena_qr = "12345678901|01|{$serie}|{$numero}|" . number_format($igv, 2) . "|" . number_format($total, 2) . "|{$fecha}|6|20522224783|";
                            
                            }
                            ?>
                           <img src="<?= $facart[0]->FAR_FBG=='F'?'data:image/png;base64,' . base64_encode($qr):'' ?>">
                        </td>
                        <td colspan="2">
                            <p>
                                Total Gravado<br>
                                Total Exonerado<br>
                                Total Inafecto<br>
                                IGV<br>
                            </p>
                            <h5 class="text-success"><strong>Total</strong></h5>
                        </td>
                        <td>
                            <?php 
                            // Calcular totales
                            $subtotal = 0;
                            if (isset($facart) && is_array($facart)) {
                                foreach ($facart as $item) {
                                    $subtotal += $item->FAR_PRECIO * $item->FAR_CANTIDAD_P / ($item->FAR_EQUIV ?: 1);
                                }
                            }
                            $igv = $subtotal * 0;
                            $total = $subtotal + $igv;
                            ?>
                            <p class="text-right">
                                S/. <?= number_format($subtotal, 2) ?><br>
                                S/. 0.00<br>
                                S/. 0.00<br>
                                S/. <?= number_format($igv, 2) ?><br>
                            </p>
                            <h5 class="text-success text-right">
                                <strong>S/. <?= number_format($total, 2) ?></strong>
                            </h5>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <?= isset($total_texto) ? $total_texto : (isset($total) ? $total : 'CIEN SOLES') ?>
    </div>
</body>
</html>