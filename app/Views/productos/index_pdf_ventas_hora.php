<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 0.85em;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #007bff;
            font-size: 1.5em;
        }
        .info {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        .bg-low { background-color: #e9f7ef; }
        .bg-mid { background-color: #fef9e7; }
        .bg-high { background-color: #fdedec; }
        
        .bar-container {
            width: 150px;
            background-color: #eee;
            height: 12px;
            display: inline-block;
            vertical-align: middle;
            border-radius: 2px;
        }
        .bar {
            height: 100%;
            background-color: #28a745;
            border-radius: 2px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte: Promedio de Ventas por Hora</h1>
        <p><strong>Local:</strong> <?= $local_nombre ?> | <strong>Rango:</strong> <?= date('d/m/Y', strtotime($fecha_inicio)) ?> - <?= date('d/m/Y', strtotime($fecha_fin)) ?></p>
    </div>

    <?php
    $max_promedio = 0;
    foreach ($reporte as $row) {
        if ($row->PromedioTransacciones > $max_promedio) {
            $max_promedio = $row->PromedioTransacciones;
        }
    }
    if ($max_promedio == 0) $max_promedio = 1;
    ?>

    <table>
        <thead>
            <tr>
                <th width="15%" class="text-center">Hora</th>
                <th width="20%" class="text-right">Total Ventas</th>
                <th width="25%" class="text-right">Promedio Diario</th>
                <th width="40%">Visualización (Carga de Trabajo)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reporte as $row): 
                $percent = ($row->PromedioTransacciones / $max_promedio) * 100;
                $hora_label = str_pad($row->Hora, 2, '0', STR_PAD_LEFT) . ":00 - " . str_pad($row->Hora, 2, '0', STR_PAD_LEFT) . ":59";
                
                // Determinar color de barra según intensidad
                $bar_color = "#28a745"; // Verde (Bajo)
                if ($percent > 70) {
                    $bar_color = "#dc3545"; // Rojo (Alto)
                } elseif ($percent > 40) {
                    $bar_color = "#ffc107"; // Amarillo (Medio)
                }
            ?>
                <tr>
                    <td class="text-center"><?= $hora_label ?></td>
                    <td class="text-right"><?= number_format($row->TotalTransacciones, 0) ?></td>
                    <td class="text-right"><?= number_format($row->PromedioTransacciones, 2) ?></td>
                    <td>
                        <div class="bar-container">
                            <div class="bar" style="width: <?= $percent ?>%; background-color: <?= $bar_color ?>;"></div>
                        </div>
                        <span style="font-size: 0.8em; margin-left: 5px;"><?= round($percent) ?>%</span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 30px; font-size: 0.8em; color: #666; font-style: italic;">
        * El porcentaje (%) representa la carga de trabajo relativa a la hora pico de mayor actividad.<br>
        * Las horas con barras amarillas o rojas indican momentos donde posiblemente se requiera más personal.
    </div>
    
    <div style="margin-top: 20px; text-align: right; font-size: 0.7em;">
        Fecha de impresión: <?= date('d/m/Y H:i:s') ?>
    </div>
</body>
</html>
