<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Lista Precios <?=$local." ". date('d-m-Y', time())?></title>
  <style>
    @page {
      margin: 10px;
    }

    body {
      margin: 10px;
    }

    @media print {
      .page-break {
        display: block;
        page-break-before: always;
      }
    }

    .page_break {
      page-break-before: always;
    }

    #invoice-PDF {
      padding: 0mm;
      margin: 0 auto;
      font-family: monospace, monospace;
      font-size: .75em;
      line-height: 1.3em;
    }

    #invoice-PDF #mid {
      min-height: 10px;
      display: inline;
    }

    #invoice-PDF .info {
      display: block;
      margin: 0px auto;
      padding: 0px;
      /*border: 1px dashed #1C6EA4;*/
      width: 755px;
    }

    .bg {
      background-color: #E7E9EB;
    }

    .bgt {
      background-color: #000000;
      color: #FFFFFF;
    }
    .white-bg{background-color:#fff}  
  </style>
</head>

<body class="white-bg" style="background-color: #fff">
  <div id="invoice-PDF">
    <div id="mid">
      <div class="info">
        <center id="body">
          <?php
          $titulo = "";
          $pagina = 1;
          $cabe_pag = "<table border='0' width='100%'>";
          $cabecera = "<table border='1' width='100%' style='border-collapse: collapse;'>" .
            "<thead><tr border='1'  class='bgt'><th>CODIGO</th><th>ARTICULO</th><th>UND</th><th>PRECIO</th></tr></thead>";
          $footer = "</table><div class='page_break'></div>";
          $cont = 0;
          $tr = "";
          $nvalores = array();
          foreach ($productos as $producto) {
            $vocal = str_split(trim($producto->ART_NOMBRE));
            if ($cont == 0) {
              $tr .= "<b>LISTA DE PRECIOS BOTICA MEDINAFARMA ($local)</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha Impresión: " . date('d-m-Y h:i a', time()) . " - PAGINA <b>$pagina</b> ";
              $tr .= $cabe_pag;
            }
            if ($titulo <> $vocal[0]) { //CAMBIA POR VOCAL DE INICIO
              $titulo = $vocal[0];
              $nvalores[] = array("COD" => "", "NOMBRE" => $titulo, "UND" => "", "PRE" => "");
              $cont++;
            }
            $desc = $producto->CNTLD == 'C' ? "© " . trim($producto->ART_NOMBRE) : trim($producto->ART_NOMBRE);
            $nvalores[] = array("COD" => $producto->ART_KEY, "NOMBRE" => $desc, "UND" => $producto->PRE_UNIDAD, "PRE" => $producto->PRE_PRE1);
            $cont++;
            if ($cont >= 132) {
              $cantidad = count($nvalores);
              $mitad_pg = intdiv($cantidad, 2);
              $tr .= "<tr><td>"; //inicia una columna de tabla general
              $tr .= $cabecera; //inicia una tabla en la columnal
              for ($xP = 0; $xP < $cantidad; $xP++) {
                if ($mitad_pg == $xP) {
                  $tr .= "</table>"; //finaliza tabla en la columna
                  $tr .= "</td><td valign='top'>"; //inicia nueva columna tabla general
                  $tr .= $cabecera; //inicia una tabla en la columnal               
                }
                if ($nvalores[$xP]["COD"] == "") {
                  $tr .= "<tr><th colspan='4' align='left' class='bg'><b>" . $nvalores[$xP]["NOMBRE"] . "</b></th></tr>";
                } else {
                  $tr .= "<tr><td>" . $nvalores[$xP]["COD"] . "</td><td><b>" . substr($nvalores[$xP]["NOMBRE"], 0, 34) . "</b></td><td>" . substr($nvalores[$xP]["UND"], 0, 3) . "</td><td align='right'>" . number_format(round($nvalores[$xP]["PRE"], 2), 2, '.', '') . "</td></tr>";
                }
              }
              $tr .= "</table></td></tr>";
              $tr .= $footer;
              $cont = 0;
              $pagina++;
              $nvalores = null;
            }
          }

          $cantidad = count($nvalores);
          $mitad_pg = intdiv($cantidad, 2);
          $tr .= "<tr><td valign='top'>"; //inicia una columna de tabla general
          $tr .= $cabecera; //inicia una tabla en la columnal
          for ($xP = 0; $xP < $cantidad; $xP++) {
            if ($mitad_pg == $xP) {
              $tr .= "</table>"; //finaliza tabla en la columna
              $tr .= "</td><td valign='top'>"; //inicia nueva columna tabla general
              $tr .= $cabecera; //inicia una tabla en la columnal               
            }
            if ($nvalores[$xP]["COD"] == "") {
              $tr .= "<tr><th colspan='4' align='left' class='bg'><b>" . $nvalores[$xP]["NOMBRE"] . "</b></th></tr>";
            } else {
              $tr .= "<tr><td>" . $nvalores[$xP]["COD"] . "</td><td><b>" . substr($nvalores[$xP]["NOMBRE"], 0, 34) . "</b></td><td>" . substr($nvalores[$xP]["UND"], 0, 3) . "</td><td align='right'>" . number_format(round($nvalores[$xP]["PRE"], 2), 2, '.', '') . "</td></tr>";
            }
          }
          $tr .= "</table></td></tr></table>";


          echo $tr;
          ?>
        </center>
      </div>
    </div><!--End Invoice Mid-->
  </div><!--End Invoice-->
</body>

</html>