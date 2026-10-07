<?php
helper('logo');

$porHoja      = 2;
$grupos       = array_chunk($empleados, $porHoja);
$totalPaginas = count($grupos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 12px 12px;
    }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 7px;
        color: #000;
        margin: 0;
    }
    .hoja {
        page-break-after: always;
    }
    .hoja.ultima {
        page-break-after: auto;
    }
    table.dos-col {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .celda-empleado {
        width: 50%;
        vertical-align: top;
        padding: 0 6px;
        border: none;
    }
    .celda-izq {
        border-right: 1px solid #cccccc;
    }
    .logo {
        text-align: center;
        margin: 0 0 2px 0;
    }
    .logo img {
        width: 120px;
    }
    .titulo {
        text-align: center;
        font-size: 8.5px;
        font-weight: bold;
        margin: 0 0 4px 0;
        text-transform: uppercase;
    }
    table.info {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 3px;
    }
    table.info td {
        border: none;
        font-size: 7px;
        padding: 1px 0;
        text-align: left;
    }
    table.grid {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    table.grid th, table.grid td {
        border: 1px solid #000;
        text-align: center;
        vertical-align: middle;
    }
    table.grid th {
        background-color: #e9e9e9;
        font-size: 6px;
        padding: 2px 0;
    }
    table.grid td {
        height: 24px;
        font-size: 6.5px;
    }
    tr.dom td {
        background-color: #f2f2f2;
    }
    table.firmas {
        width: 100%;
        border-collapse: collapse;
        margin-top: 14px;
    }
    table.firmas td {
        border: none;
        text-align: center;
        font-size: 6.5px;
        vertical-align: bottom;
    }
    .linea {
        width: 80%;
        border-top: 1px solid #000;
        margin: 0 auto 2px auto;
        height: 1px;
    }
</style>
</head>
<body>

<?php foreach ($grupos as $gi => $grupo): ?>
    <div class="hoja <?= $gi === $totalPaginas - 1 ? 'ultima' : '' ?>">
        <table class="dos-col">
            <tr>
                <?php $slot = 0; ?>
                <?php foreach ($grupo as $emp): ?>
                    <?php $slot++; ?>
                    <td class="celda-empleado <?= ($slot === 1 && count($grupo) > 1) ? 'celda-izq' : '' ?>">
                        <?= view('formatos/pdf/_empleado_asistencia', [
                            'empleado'  => $emp,
                            'mes'       => $mes,
                            'anio'      => $anio,
                            'mesNombre' => $mesNombre,
                            'dias'      => $dias,
                            'local'     => $local,
                        ]) ?>
                    </td>
                <?php endforeach; ?>
                <?php if (count($grupo) < $porHoja): ?>
                    <td class="celda-empleado"></td>
                <?php endif; ?>
            </tr>
        </table>
    </div>
<?php endforeach; ?>

</body>
</html>
