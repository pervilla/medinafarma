<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($titulo) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; background: #fff; color: #000; }
        .hoja { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
        .box { border: 1px solid #ccc; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .box-title { font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; font-size: 16px;}
        .data-row { margin-bottom: 8px; }
        .data-label { font-weight: bold; width: 130px; display: inline-block; }
        .lines { border-bottom: 1px dashed #999; height: 30px; margin-top: 20px; }
        @media print {
            body { background: #fff; }
            .hoja { padding: 0; width: 100%; max-width: none; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="hoja">
        <div class="text-right mb-3 btn-print">
            <button onclick="window.print()" class="btn btn-primary">Imprimir Hoja</button>
            <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
        </div>

        <div class="header text-center">
            <h2>HOJA DE TRIAJE Y ATENCIÓN MÉDICA</h2>
            <p class="mb-0">Cita #<?= str_pad($cita->id, 6, '0', STR_PAD_LEFT) ?> - Fecha: <?= date('d/m/Y', strtotime($cita->fecha_especifica ?: $cita->created_at)) ?></p>
            <p class="mb-0"><strong>Médico:</strong> <?= esc($cita->medico ?: 'No asignado') ?></p>
        </div>

        <div class="box">
            <div class="box-title">DATOS DEL PACIENTE</div>
            <div class="row">
                <div class="col-sm-12 data-row"><span class="data-label">Paciente:</span> <?= esc($cita->CLI_NOMBRE) ?></div>
                <div class="col-sm-4 data-row"><span class="data-label">DNI:</span> <?= esc($cita->DNI ?: '-') ?></div>
                <div class="col-sm-4 data-row"><span class="data-label">Edad:</span> <?= esc($cita->edad ? $cita->edad . ' años' : '-') ?></div>
                <div class="col-sm-4 data-row"><span class="data-label">Teléfono:</span> <?= esc($cita->CLI_TELEF1 ?: '-') ?></div>
                <div class="col-sm-4 data-row"><span class="data-label">Tipo Sangre:</span> <?= esc($cita->tipo_sangre ?: 'No registrado') ?></div>
                <div class="col-sm-12 data-row mt-2"><span class="data-label">Alergias:</span> <?= esc($cita->alergias ?: 'Ninguna declarada') ?></div>
                <div class="col-sm-12 data-row"><span class="data-label">Enfermedades:</span> <?= esc($cita->enfermedades_cronicas ?: 'Ninguna declarada') ?></div>
            </div>
        </div>

        <div class="box">
            <div class="box-title">TRIAJE</div>
            <div class="row">
                <div class="col-sm-4 data-row"><span class="data-label">Presión Art.:</span> <?= esc($historia->presion_arterial ?: '______') ?> mmHg</div>
                <div class="col-sm-4 data-row"><span class="data-label">Frec. Cardiaca:</span> <?= esc($historia->frec_cardiaca ?: '______') ?> lpm</div>
                <div class="col-sm-4 data-row"><span class="data-label">Frec. Resp.:</span> <?= esc($historia->frec_respiratoria ?: '______') ?> rpm</div>
                <div class="col-sm-4 data-row"><span class="data-label">Temperatura:</span> <?= esc($historia->temperatura ?: '______') ?> °C</div>
                <div class="col-sm-4 data-row"><span class="data-label">Saturación O2:</span> <?= esc($historia->saturacion ?: '______') ?> %</div>
                <div class="col-sm-4 data-row"><span class="data-label">Peso:</span> <?= esc($historia->peso ?: '______') ?> kg</div>
                <div class="col-sm-4 data-row"><span class="data-label">Talla:</span> <?= esc($historia->talla ?: '______') ?> cm</div>
            </div>
        </div>

        <div class="box" style="min-height: 400px;">
            <div class="box-title">ATENCIÓN MÉDICA (Llenado por el Médico)</div>
            <p style="font-weight: bold; margin-top: 15px;">Motivo de Consulta / Anamnesis:</p>
            <div class="lines"></div><div class="lines"></div><div class="lines"></div>
            
            <p style="font-weight: bold; margin-top: 25px;">Examen Clínico:</p>
            <div class="lines"></div><div class="lines"></div><div class="lines"></div>

            <p style="font-weight: bold; margin-top: 25px;">Diagnóstico(s):</p>
            <div class="lines"></div><div class="lines"></div><div class="lines"></div>

            <p style="font-weight: bold; margin-top: 25px;">Tratamiento / Plan de Trabajo:</p>
            <div class="lines"></div><div class="lines"></div><div class="lines"></div>
        </div>
        
        <div class="text-right mt-5 pt-5">
            <p>___________________________________<br>Firma y Sello del Médico</p>
        </div>
    </div>
</body>
</html>
