<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket de Pago - <?= esc($pago->ticket_nro) ?></title>
    <link rel="stylesheet" href="<?= site_url('plugins/fontawesome-free/css/all.min.css') ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; padding: 15px; color: #000; }
        .ticket { max-width: 300px; margin: 0 auto; border: 1px dashed #000; padding: 12px; }
        .center { text-align: center; }
        .brand { font-size: 16px; font-weight: bold; letter-spacing: 1px; }
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; margin: 2px 0; }
        .row .lbl { font-weight: bold; }
        .big { font-size: 18px; font-weight: bold; }
        .footer { margin-top: 10px; text-align: center; font-size: 10px; }
        .badge { display: inline-block; border: 1px solid #000; padding: 3px 10px; font-weight: bold; margin: 5px 0; }
        @media print { body { padding: 5px; } .ticket { border: none; } }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="center">
            <img src="<?= site_url('dist/img/medinafarma-black.jpg') ?>" alt="MedinaFarma" style="width: 180px;">
            <div class="badge">CONSTANCIA DE PAGO</div>
            <div><?= date('d/m/Y H:i') ?></div>
        </div>
        <div class="line"></div>
        <div class="row"><span class="lbl">TICKET N°:</span><span class="big"><?= esc($pago->ticket_nro) ?></span></div>
        <div class="row"><span class="lbl">Paciente:</span><span><?= esc($pago->CLI_NOMBRE) ?></span></div>
        <div class="row"><span class="lbl">Médico:</span><span><?= esc($pago->medico ?: '-') ?></span></div>
        <div class="row"><span class="lbl">Campaña:</span><span><?= $pago->fecha_especifica ? date('d/m/Y', strtotime($pago->fecha_especifica)) : '-' ?></span></div>
        <div class="row"><span class="lbl">Forma Pago:</span><span><?= esc($pago->forma_pago) ?></span></div>
        <?php if (!empty($pago->nro_operacion)): ?>
        <div class="row"><span class="lbl">N° Operación:</span><span><?= esc($pago->nro_operacion) ?></span></div>
        <?php endif; ?>
        <div class="row"><span class="lbl">Local:</span><span><?= esc($pago->local_pago) ?></span></div>
        <div class="line"></div>
        <div class="row"><span class="lbl">MONTO PAGADO:</span><span class="big">S/ <?= number_format($pago->monto, 2) ?></span></div>
        <div class="line"></div>
        <div class="footer">
            Este ticket es solo una constancia de pago.<br>
            El comprobante (Boleta/Factura) se emitirá el día de la consulta.<br><br>
            MedinaFarma - <?= date('d/m/Y H:i') ?>
        </div>
    </div>
    <script>window.print();</script>
</body>
</html>
