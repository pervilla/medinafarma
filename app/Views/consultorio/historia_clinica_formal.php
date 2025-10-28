<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historia Clínica - <?=$clientes->CLI_NOMBRE?></title>
    <link rel="stylesheet" href="<?= site_url('plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= site_url('dist/css/adminlte.min.css') ?>">
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        @page {
            size: A4;
            margin: 1cm 1.5cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
        }
        
        @media print {
            body { font-size: 10px; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .avoid-break { page-break-inside: avoid; }
        }
        
        .header-box {
            border: 2px solid #004269 !important;
            padding: 8px;
            margin-bottom: 12px;
            page-break-inside: avoid;
            width: 100%;
        }
        
        .header-box table {
            border-collapse: collapse;
            margin: 0;
        }
        
        .header-box td {
            border: none !important;
            padding: 5px;
        }
        
        .section-title {
            background-color: #004269 !important;
            color: white !important;
            padding: 6px;
            text-align: center;
            font-weight: bold;
            margin: 10px 0 8px 0;
            page-break-after: avoid;
        }
        
        .section-content {
            page-break-inside: avoid;
            margin-bottom: 10px;
        }
        
        .data-row {
            border-bottom: 1px solid #ddd !important;
            padding: 4px 0;
            margin: 2px 0;
        }
        
        .field-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
            vertical-align: top;
        }
        
        .field-value {
            border-bottom: 1px dotted #333 !important;
            min-height: 18px;
            display: inline-block;
            min-width: 150px;
            padding: 1px 3px;
        }
        
        .vital-signs-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        
        .vital-signs-table td {
            border: 1px solid #666 !important;
            padding: 4px;
            text-align: center;
        }
        
        .text-area {
            border: 1px solid #666 !important;
            min-height: 60px;
            padding: 8px;
            width: 100%;
            background: #f9f9f9 !important;
        }
        
        .empty-field {
            border-bottom: 1px dotted #333 !important;
            min-height: 20px;
            margin: 3px 0;
        }
        
        .signature-area {
            margin-top: 30px;
            text-align: center;
            page-break-inside: avoid;
        }
        
        .signature-line {
            border-top: 1px solid #000 !important;
            width: 250px;
            margin: 30px auto 8px auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        
        table th, table td {
            border: 1px solid #666 !important;
            padding: 4px;
            text-align: left;
            font-size: 10px;
        }
        
        table th {
            background-color: #f0f0f0 !important;
            font-weight: bold;
        }
        
        .page-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: white;
            border-bottom: 1px solid #ccc;
            padding: 10px;
            font-size: 10px;
        }
        
        .continuation {
            font-style: italic;
            text-align: center;
            margin: 10px 0;
            font-size: 9px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <section class="invoice">
            <!-- ENCABEZADO -->
            <div class="header-box">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="border: none; width: 60%; vertical-align: top;">
                            <img src="<?= site_url('dist/img/medinafarma-black.jpg') ?>" width="180" height="45">
                            <h3 style="margin: 8px 0;">HISTORIA CLÍNICA</h3>
                        </td>
                        <td style="border: none; width: 40%; text-align: center; vertical-align: middle;">
                            <div style="border: 2px solid #004269 !important; padding: 10px;">
                                <h4 style="margin: 5px 0;">Nro: <?=$historia->HIS_CODHIS?></h4>
                                <p style="margin: 5px 0;"><strong>Fecha:</strong> <?=date("d/m/Y H:i", strtotime($historia->HIS_FECHA_ATE))?></p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- DATOS DEL PACIENTE -->
            <div class="section-title">DATOS DEL PACIENTE</div>
            <div class="section-content">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="border: none; width: 50%;">
                            <div class="data-row">
                                <span class="field-label">Código:</span>
                                <span class="field-value"><?=$clientes->CLI_CODCLIE?></span>
                            </div>
                            <div class="data-row">
                                <span class="field-label">Paciente:</span>
                                <span class="field-value" style="width: 250px;"><?=$clientes->CLI_NOMBRE?></span>
                            </div>
                            <div class="data-row">
                                <span class="field-label">Fecha Nac.:</span>
                                <span class="field-value"><?=date("d/m/Y",strtotime($clientes->CLI_FECHA_NAC))?></span>
                            </div>
                        </td>
                        <td style="border: none; width: 50%;">
                            <div class="data-row">
                                <span class="field-label">DNI:</span>
                                <span class="field-value"><?=$clientes->CLI_RUC_ESPOSA?></span>
                            </div>
                            <div class="data-row">
                                <span class="field-label">Edad:</span>
                                <span class="field-value"><?php
                                    $date1=date_create($clientes->CLI_FECHA_NAC);
                                    $date2=date_create(date('Y-m-d'));
                                    $diff=date_diff($date1,$date2);
                                    echo $diff->format('%y años %m meses');
                                ?></span>
                            </div>
                            <div class="data-row">
                                <span class="field-label">Celular:</span>
                                <span class="field-value"><?=$clientes->CLI_TELEF1?></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                             <div class="data-row">
                                <span class="field-label">Dirección:</span>
                                <span class="field-value" style="width: 400px;"><?=$clientes->CLI_CASA_DIREC?></span>
                            </div>
                        </td>
                    </tr>
                </table>
               
            </div>

            <!-- ANAMNESIS -->
            <div class="section-title">ANAMNESIS</div>
            <div class="section-content">
                <div class="data-row">
                    <span class="field-label">Motivo consulta:</span><br>
                    <?php if($historia->HIS_EXA_CLI): ?>
                        <div class="text-area"><?=$historia->HIS_EXA_CLI?></div>
                    <?php else: ?>
                        <div class="empty-field" style="height: 50px;"></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SIGNOS VITALES -->
            <div class="section-title">SIGNOS VITALES</div>
            <div class="section-content">
                <table class="vital-signs-table">
                    <tr>
                        <td><strong>Peso:</strong> <?=$historia->HIS_PESO ?: '_______'?> kg</td>
                        <td><strong>Talla:</strong> <?=$historia->HIS_TALLA ?: '_______'?> cm</td>
                        <td><strong>Temperatura:</strong> <?=$historia->HIS_TEMPERATURA ?: '_______'?> °C</td>
                    </tr>
                    <tr>
                        <td><strong>P.A.:</strong> <?=$historia->HIS_PREART_MM ?: '___'?>/<?=$historia->HIS_PREART_HG ?: '___'?> mmHg</td>
                        <td><strong>F.C.:</strong> <?=$historia->HIS_FRE_CARD ?: '_______'?> lpm</td>
                        <td><strong>F.R.:</strong> <?=$historia->HIS_FRE_RESP ?: '_______'?> rpm</td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>Sat. O2:</strong> <?=$historia->HIS_SATURACION ?: '_______'?> %</td>
                        <td><strong>IMC:</strong> _______</td>
                    </tr>
                </table>
            </div>

            <!-- EXAMEN FÍSICO -->
            <div class="section-title">EXAMEN FÍSICO</div>
            <div class="section-content avoid-break">
                <?php if($historia->HIS_INDICACIONES): ?>
                    <div class="text-area"><?=$historia->HIS_INDICACIONES?></div>
                <?php else: ?>
                    <div class="empty-field" style="height: 180px;"></div>
                <?php endif; ?>
            </div>

            <!-- DIAGNÓSTICO -->
            <div class="section-title">DIAGNÓSTICO</div>
            <div class="section-content avoid-break">
                <?php if(!empty($diagnostico)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 15%;">Código CIE</th>
                                <th style="width: 50%;">Descripción</th>
                                <th style="width: 20%;">Tipo</th>
                                <th style="width: 15%;">Caso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diagnostico as $diag): ?>
                            <tr>
                                <td><?=$diag->HISD_CIE_CODIGO?></td>
                                <td><?=$diag->HISD_CIE_DESCRIPCION?></td>
                                <td><?=$tipo_diagnostico[$diag->HISD_TIPO]?></td>
                                <td><?=$caso_diagnostico[$diag->HISD_CASO]?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-field" style="height: 160px;"></div>
                <?php endif; ?>
            </div>

            <!-- PLAN DE TRATAMIENTO -->
            <div class="section-title">PLAN DE TRATAMIENTO</div>
            <div class="section-content avoid-break">
                <?php if($historia->HIS_PLAN_TRAB): ?>
                    <div class="text-area"><?=$historia->HIS_PLAN_TRAB?></div>
                <?php else: ?>
                    <div class="empty-field" style="height: 180px;"></div>
                <?php endif; ?>
            </div>

            <!-- RECETA -->
            <?php if(!empty($receta)): ?>
            <div class="section-title">PRESCRIPCIÓN MÉDICA</div>
            <div class="section-content avoid-break">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40%;">Medicamento</th>
                            <th style="width: 10%;">Días</th>
                            <th style="width: 10%;">Cant.</th>
                            <th style="width: 40%;">Indicaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receta as $med): ?>
                        <tr>
                            <td><strong><?=$med->HISR_NOMART?></strong></td>
                            <td><?=$med->HISR_DIAS?></td>
                            <td><?=$med->HISR_CANT?></td>
                            <td><?=$med->HISR_INDICACIONES?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- OBSERVACIONES -->
            <div class="section-title">OBSERVACIONES</div>
            <div class="section-content">
                <div class="empty-field" style="height: 60px;"></div>
            </div>

            <!-- FIRMA -->
            <div class="signature-area avoid-break">
                <table style="width: 100%; border: none; margin-top: 40px;">
                    <tr>
                        <td style="border: none; width: 50%; text-align: center;">
                            <div class="signature-line" style="width: 200px; margin: 20px auto;"></div>
                            <strong>Firma del Paciente</strong>
                        </td>
                        <td style="border: none; width: 50%; text-align: center;">
                            <div class="signature-line" style="width: 200px; margin: 20px auto;"></div>
                            <strong>Firma y Sello del Médico</strong><br>
                            <small>Colegio Médico del Perú</small>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- CONTINUACIÓN EN SEGUNDA PÁGINA -->
            <div class="continuation">
                <em>Si requiere más espacio, continúe en el reverso de la hoja</em>
            </div>

            <!-- BOTÓN IMPRIMIR -->
            <div class="no-print text-center" style="margin: 20px 0;">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Imprimir Historia Clínica
                </button>
                <button onclick="window.close()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </section>
    </div>
</body>
</html>