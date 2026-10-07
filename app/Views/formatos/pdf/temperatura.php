<?php
helper('logo');

$mitad       = (int) ceil($dias / 2);
$turnos      = ['9-10', '15-16', '21-22'];
$izq         = range(1, $mitad);
$der         = $mitad < $dias ? range($mitad + 1, $dias) : [];
$totalGrupos = $mitad;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 12px 16px;
    }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8px;
        color: #000;
        margin: 0;
    }
    .logo {
        text-align: center;
        margin: 0 0 2px 0;
    }
    .logo img {
        width: 190px;
    }
    .titulo {
        text-align: center;
        font-size: 11px;
        font-weight: bold;
        margin: 0 0 6px 0;
        text-transform: uppercase;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    th, td {
        border: 1px solid #000;
        text-align: center;
        vertical-align: middle;
    }
    th {
        background-color: #e9e9e9;
        font-size: 7.5px;
        padding: 3px 1px;
    }
    td {
        height: 18px;
        padding: 0 2px;
    }
    table.info {
        margin-bottom: 5px;
    }
    table.info td {
        border: none;
        text-align: left;
        padding: 2px 0;
        font-size: 8.5px;
    }
    td.fecha {
        font-weight: bold;
        font-size: 9px;
    }
    .firma {
        margin-top: 34px;
        text-align: center;
        font-size: 9px;
    }
    .firma .linea {
        width: 280px;
        border-top: 1px solid #000;
        margin: 0 auto 3px auto;
    }
</style>
</head>
<body>

    <div class="logo">
        <img src="<?= logo_medinafarma() ?>" alt="Medinafarma">
    </div>

    <div class="titulo">Formato de Registro de Temperatura y Humedad</div>

    <table class="info">
        <tr>
            <td style="width: 50%;"><b>ÁREA:</b> <?= htmlspecialchars((string) $area) ?></td>
            <td style="width: 50%;"><b>COD. INST.:</b> <?= htmlspecialchars((string) $codInst) ?></td>
        </tr>
        <tr>
            <td><b>MES:</b> <?= $mesNombre ?></td>
            <td><b>AÑO:</b> <?= $anio ?></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 6%;">FECHA</th>
                <th style="width: 9%;">HORA</th>
                <th style="width: 7%;">Tª (°C)</th>
                <th style="width: 6%;">H (%)</th>
                <th style="width: 22%;">RESPONSABLE</th>
                <th style="width: 6%;">FECHA</th>
                <th style="width: 9%;">HORA</th>
                <th style="width: 7%;">Tª (°C)</th>
                <th style="width: 6%;">H (%)</th>
                <th style="width: 22%;">RESPONSABLE</th>
            </tr>
        </thead>
        <tbody>
        <?php for ($i = 0; $i < $totalGrupos; $i++): ?>
            <?php
            $diaIzq = $izq[$i] ?? null;
            $diaDer = $der[$i] ?? null;
            ?>
            <?php foreach ($turnos as $t => $turno): ?>
                <tr>
                    <?php if ($t === 0): ?>
                        <td rowspan="3" class="fecha"><?= $diaIzq ?></td>
                    <?php endif; ?>
                    <td><?= $turno ?></td>
                    <td></td>
                    <td></td>
                    <td></td>

                    <?php if ($diaDer !== null): ?>
                        <?php if ($t === 0): ?>
                            <td rowspan="3" class="fecha"><?= $diaDer ?></td>
                        <?php endif; ?>
                        <td><?= $turno ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    <?php else: ?>
                        <td colspan="5"></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php endfor; ?>
        </tbody>
    </table>

    <div class="firma">
        <div class="linea"></div>
        Químico Farmacéutico Director Técnico
    </div>

</body>
</html>
