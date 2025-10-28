<style><?php $color='black'; ?> 
@page {
    margin: 10px;
}
body {
    margin: 10px;
    font-family: monospace;
    font-size: 0.75em;
    line-height: 1.3em;
    color: <?=$color?>;
}
.page-break {
    display: block;
    page-break-before: always;
}
#invoice-PDF {
    padding: 0;
    margin: 0 auto;
    width: 755px;
}
.bg {
    background-color: #E7E9EB;
}
#invoice-PDF table {
    border-collapse: collapse;
    width: 100%;
    border: 1px solid <?=$color?>;
}
#invoice-PDF th, #invoice-PDF td {
    border: 1px solid <?=$color?>;
    text-align: left;
    color: <?=$color?>;
}
#invoice-PDF .bg th {
    background-color:rgba(200, 200, 200);
    color: navy;
}
#invoice-PDF b {
    color: <?=$color?>;
}
</style>

<div id="invoice-PDF">
    <center>
        <?php
        $titulo = "";
        $pagina = 1;
        $cabecera = "<table border='1' width='100%'><tr><th>CODIGO</th><th>ARTICULO</th><th>UNIDAD</th><th>STOCK</th><th>FISICO</th><th>FALTANTE</th><th>SOBRANTE</th></tr>";
        $footer = "</table><div class='page-break'>Responsable:</div>";
        $cont = 0;
        $tr = "";
        foreach ($productos as $producto) {
            if ($cont == 0) {
                $tr .= "<b>INVENTARIO BOTICA MEDINAFARMA ($local)</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha Impresión: " . date('d-m-Y h:i:s a', time()) . " - PAGINA <b>$pagina</b> ";
                $tr .= $cabecera;
            }
            if ($titulo != $producto->TAB_NOMLARGO) {
                $titulo = $producto->TAB_NOMLARGO;
                $tr .= "<tr class='bg'><th colspan='7'>$titulo</th></tr>";
                $cont++;
            }
            $nombre = substr(trim($producto->ART_NOMBRE), 0, 52);
            $desc = $producto->CNTLD == 'C' ? "<b>© $nombre</b>" : $nombre;
            $tr .= "<tr><td>$producto->ART_KEY</td><td>$desc</td><td>" . TRIM($producto->PRE_UNIDAD) . "(" . intval($producto->PRE_EQUIV) . ")</td><td>$producto->STOCK</td><td></td><td>-</td><td>+</td></tr>";

            $cont++;
            if ($cont >= 61) {
                $tr .= $footer;
                $cont = 0;
                $pagina++;
            }
        }
        echo $tr;
        ?>
    </center>
</div>