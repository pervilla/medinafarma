<?php
/**
 * Bloque individual de un empleado para el formato de asistencia.
 * Variables esperadas: $empleado, $mes, $anio, $mesNombre, $dias, $local
 */
?>
<div class="emp-bloque">

    <div class="logo">
        <img src="<?= logo_medinafarma() ?>" alt="Medinafarma">
    </div>

    <div class="titulo">Registro de Asistencia de Personal</div>

    <table class="info">
        <tr>
            <td style="width: 60%;"><b>MES:</b> <?= $mesNombre ?> &nbsp; <b>AÑO:</b> <?= $anio ?></td>
            <td style="width: 40%; text-align: right;"><b>CÓDIGO:</b> <?= htmlspecialchars($empleado['codven']) ?></td>
        </tr>
        <tr>
            <td colspan="2"><b>TRABAJADOR:</b> <?= htmlspecialchars($empleado['nombre']) ?></td>
        </tr>
        <tr>
            <td colspan="2"><b>ÁREA / LOCAL:</b> <?= htmlspecialchars((string) $local) ?></td>
        </tr>
    </table>

    <table class="grid">
        <colgroup>
            <col style="width: 26%;">
            <col style="width: 18%;">
            <col style="width: 19%;">
            <col style="width: 18%;">
            <col style="width: 19%;">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">FECHA</th>
                <th colspan="2">INGRESO</th>
                <th colspan="2">SALIDA</th>
            </tr>
            <tr>
                <th>HORA</th>
                <th>FIRMA</th>
                <th>HORA</th>
                <th>FIRMA</th>
            </tr>
        </thead>
        <tbody>
        <?php for ($d = 1; $d <= $dias; $d++): ?>
            <?php $w = (int) date('w', mktime(0, 0, 0, $mes, $d, $anio)); ?>
            <tr class="<?= $w === 0 ? 'dom' : '' ?>">
                <td><?= sprintf('%02d/%02d/%04d', $d, $mes, $anio) ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>

    <table class="firmas">
        <tr>
            <td>
                <div class="linea"></div>
                Firma del Trabajador
            </td>
            <td>
                <div class="linea"></div>
                V°B° Jefe / Administración
            </td>
        </tr>
    </table>

</div>
