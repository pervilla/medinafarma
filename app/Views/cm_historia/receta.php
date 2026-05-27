<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receta - <?= esc($cita->CLI_NOMBRE) ?></title>
    <link rel="stylesheet" href="<?= site_url('plugins/fontawesome-free/css/all.min.css') ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; padding: 15px; }
        .duplex { display: flex; gap: 10px; }
        .copy { flex: 1; border: 1px solid #000; padding: 10px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        .header img { width: 180px; }
        .header .num { font-weight: bold; font-size: 14px; }
        .info { margin-bottom: 8px; }
        .info td { padding: 2px 5px; }
        .label { font-weight: bold; width: 80px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        th, td { border: 1px solid #000; padding: 3px 5px; text-align: left; font-size: 11px; }
        th { background: #f0f0f0; }
        .footer { margin-top: 10px; text-align: center; font-size: 11px; }
        .signature { margin-top: 40px; text-align: center; }
        .signature .line { border-top: 1px solid #000; width: 200px; margin: 0 auto; padding-top: 5px; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
    <div class="duplex">
        <?php for($i=0; $i<2; $i++): ?>
        <div class="copy">
            <div class="header">
                <img src="<?= site_url('dist/img/medinafarma-black.jpg') ?>" alt="MedinaFarma">
                <span class="num">Historia: <?= $historia ? str_pad($historia->id, 8, '0', STR_PAD_LEFT) : 'N/A' ?></span>
            </div>
            <table class="info">
                <tr><td class="label">Paciente</td><td>: <?= esc($cita->CLI_NOMBRE) ?></td></tr>
                <tr><td class="label">DNI</td><td>: <?= $cita->DNI ?: '-' ?></td></tr>
                <tr><td class="label">Edad</td><td>: <?= $cita->edad ?> años</td></tr>
                <tr><td class="label">Médico</td><td>: <?= esc($cita->medico) ?></td></tr>
                <tr><td class="label">Fecha</td><td>: <?= $cita->fecha_especifica ? date('d/m/Y', strtotime($cita->fecha_especifica)) : date('d/m/Y') ?></td></tr>
            </table>
            
            <?php if(!empty($diagnosticos)): ?>
            <p style="font-weight:bold; margin-top:5px;">Diagnósticos CIE-10:</p>
            <table><tr><th>Código</th><th>Descripción</th></tr>
                <?php foreach($diagnosticos as $d): ?>
                <tr><td><?= esc($d->cie_codigo) ?></td><td><?= esc($d->cie_descripcion) ?></td></tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
            
            <p style="font-weight:bold; margin-top:5px;">Receta:</p>
            <?php if(!empty($recetas)): ?>
            <table>
                <tr><th>Medicamento</th><th>Cant</th><th>Días</th><th>Indicaciones</th></tr>
                <?php foreach($recetas as $r): ?>
                <tr><td><?= esc($r->nombre_articulo) ?></td><td><?= $r->cantidad ?></td><td><?= $r->dias ?></td><td><?= esc($r->indicaciones) ?></td></tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p style="color:#999; font-style:italic;">Sin recetas registradas</p>
            <?php endif; ?>
            
            <?php if($historia && $historia->indicaciones): ?>
            <p style="font-weight:bold; margin-top:5px;">Indicaciones adicionales:</p>
            <p><?= nl2br(esc($historia->indicaciones)) ?></p>
            <?php endif; ?>
            
            <div class="signature">
                <div class="line">Firma y Sello del Médico</div>
            </div>
            <div class="footer">MedinaFarma - <?= date('d/m/Y H:i') ?> - <?= $i==0 ? 'COPIA PACIENTE' : 'COPIA ESTABLECIMIENTO' ?></div>
        </div>
        <?php endfor; ?>
    </div>
    <script>window.print();</script>
</body>
</html>
