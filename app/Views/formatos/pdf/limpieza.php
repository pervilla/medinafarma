<?php
helper('logo');

$numActividades = count($actividades);

// Definimos el ancho reducido igual para el día y para cada actividad (en %)
$anchoColumnaActividad = 5; 

// Calculamos el ancho restante total (100% - el ancho del día y todas las actividades)
$anchoRestante = 100 - ($anchoColumnaActividad * ($numActividades + 1));

// Repartimos el sobrante equitativamente entre "REALIZADO POR" y "SUPERVISADO POR"
$anchoFirmas = $anchoRestante / 2;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 14px 16px;
    }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8px;
        color: #000;
        margin: 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table.top {
        table-layout: auto;
    }
    table.top td {
        border: none;
        vertical-align: middle;
        padding: 0;
    }
    .logo img {
        width: 175px;
    }
    .codigo {
        text-align: right;
        font-weight: bold;
        font-size: 10px;
    }
    .titulo {
        text-align: center;
        font-size: 12px;
        font-weight: bold;
        margin: 8px 0 6px 0;
        text-transform: uppercase;
    }
    table.datos {
        table-layout: auto;
        margin-bottom: 4px;
    }
    table.datos td {
        border: none;
        padding: 2px 0;
        font-size: 9px;
        vertical-align: middle;
    }
    .box {
        border: 1px solid #000;
        padding: 2px 8px;
        display: inline-block;
        min-width: 110px;
        text-align: center;
    }
    table.grid {
        table-layout: fixed;
        width: 100%;
    }
    table.grid th, table.grid td {
        border: 1px solid #000;
        text-align: center;
        vertical-align: middle;
    }
    table.grid th {
        background-color: #fff;
        font-size: 7px;
        padding: 0;
        font-weight: bold;
    }
    table.grid td {
        height: 18px;
    }
    .vertical {
        display: inline-block;
        transform: rotate(-90deg);
        white-space: nowrap;
        font-size: 7px;
    }
    .vertical-cell {
        height: 155px;
    }
    .sign-head {
        font-size: 8px;
    }
    table.freq {
        width: 46%;
        table-layout: fixed;
        margin-top: 12px;
    }
    table.freq th, table.freq td {
        border: 1px solid #000;
        font-size: 8px;
        padding: 3px 6px;
        text-align: left;
    }
    table.freq th {
        text-align: center;
        font-weight: bold;
    }
    .firma-container {
        margin-top: 30px;
        float: right;
        margin-right: 5%; /* Ajusta esta distancia para mover todo el bloque hacia la izquierda o derecha */
        text-align: center;
        width: 250px; /* Ancho fijo para el bloque de la firma */
    }
    .firma-container .linea {
        border-top: 1px solid #000;
        margin-bottom: 5px;
        width: 100%;
    }
    .firma-container .texto {
        font-size: 9px;
        font-weight: bold;
    }
</style>
</head>
<body>

    <table class="top">
        <tr>
            <td class="logo"><img src="<?= logo_medinafarma() ?>" alt="Medinafarma"></td>
            <td class="codigo">F-BMF-19</td>
        </tr>
    </table>

    <div class="titulo">Registro de Control de Limpieza</div>

    <table class="datos">
        <tr>
            <td style="width: 33%;"><b>AÑO</b> <span class="box"><?= $anio ?></span></td>
            <td style="width: 34%; text-align: center;"><b>MES</b> <span class="box"><?= $mesNombre ?></span></td>
            <td style="width: 33%; text-align: right;"><span class="box">VERSION:01</span></td>
        </tr>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <!-- Asignamos los anchos inline en el primer TR para que Dompdf los fuerce correctamente -->
                <th style="width: <?= $anchoColumnaActividad ?>%;"></th>
                <th colspan="<?= $numActividades ?>">ACTIVIDAD</th>
                <th colspan="2"></th>
            </tr>
            <tr>
                <th></th>
                <?php for ($i = 1; $i <= $numActividades; $i++): ?>
                    <th style="width: <?= $anchoColumnaActividad ?>%;"><?= $i ?></th>
                <?php endfor; ?>
                <th style="width: <?= $anchoFirmas ?>%;"></th>
                <th style="width: <?= $anchoFirmas ?>%;"></th>
            </tr>
            <tr>
                <th class="vertical-cell"><span class="vertical">FECHA</span></th>
                <?php foreach ($actividades as $actividad): ?>
                    <th class="vertical-cell"><span class="vertical"><?= htmlspecialchars($actividad) ?></span></th>
                <?php endforeach; ?>
                <th class="vertical-cell sign-head">REALIZADO<br>POR</th>
                <th class="vertical-cell sign-head">SUPERVISADO<br>POR</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($d = 1; $d <= $dias; $d++): ?>
                <tr>
                    <td><?= $d ?></td>
                    <?php for ($i = 0; $i < $numActividades; $i++): ?>
                        <td></td>
                    <?php endfor; ?>
                    <td></td>
                    <td></td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <table class="freq">
        <tr>
            <th>FRECUENCIA DE LIMPIEZA</th>
            <th>ACTIVIDAD</th>
        </tr>
        <?php foreach ($frecuencias as $frecuencia => $acts): ?>
            <tr>
                <td><?= $frecuencia ?></td>
                <td><?= $acts ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

<div class="firma-container">
        <div class="linea"></div>
        <div class="texto">Director Técnico</div>
    </div>

</body>
</html>