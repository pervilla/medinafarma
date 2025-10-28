<!DOCTYPE html>
<html lang="es" >
<head>
  <meta charset="UTF-8">
  <title>Reporte de Caja</title>
  <style>
@media print {
    .page-break { display: block; page-break-before: always; }
}
      #invoice-POS {
  /*box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);*/
  padding: 0mm;
  margin: 0 auto;
  width: 80mm;
  background: #FFF;
  font-family: monospace, monospace;
}
#invoice-POS ::selection {
  background: #f31544;
  color: #FFF;
}
#invoice-POS ::moz-selection {
  background: #f31544;
  color: #FFF;
}
#invoice-POS h1 {
  font-size: 1.5em;
  color: #222;
}
#invoice-POS h2 {
  font-size: .9em;
}
#invoice-POS h3 {
  font-size: 1.2em;
  font-weight: 300;
  line-height: 2em;
}
#invoice-POS p {
  font-size: .7em;
  color: #666;
  line-height: 1.2em;
}
#invoice-POS #top, #invoice-POS #mid, #invoice-POS #bot {
  /* Targets all id with 'col-' */
  border-bottom: 1px solid #EEE;
}
#invoice-POS #top {
  min-height: 100px;
}
#invoice-POS #mid {
  min-height: 80px;
}
#invoice-POS #bot {
  min-height: 50px;
}
#invoice-POS #top .logo {
  height: 58px;
  width: 231px;
  background: url(<?=site_url('../../dist/img/medinafarma-black.jpg')?>) no-repeat;
  background-size: 231px 60px;
}
#invoice-POS .clientlogo {
  float: left;
  height: 58px;
  width: 231px;
  background: url(<?=site_url('../../dist/img/medinafarma-black.jpg')?>) no-repeat;
  background-size: 231px 60px;
  border-radius: 50px;
}
#invoice-POS .info {
  display: block;
  margin-left: 0;
}
#invoice-POS .title {
  float: right;
}
#invoice-POS .title p {
  text-align: right;
}
#invoice-POS table {
  width: 100%;
  border-collapse: collapse;
}
#invoice-POS .tabletitle {
  font-size: .5em;
  background: #EEE;
}
#invoice-POS .service {
  border-bottom: 1px solid #EEE;
}
#invoice-POS .item {
  width: 24mm;
}
#invoice-POS .itemtext {
  font-size: .7em;
}
#invoice-POS #legalcopy {
  margin-top: 5mm;
}
.abutton {
  background: #bada55; padding: 5px; border-radius: 5px;
  transition: 1s; text-decoration: none; color: black;
}
.abutton:hover { background: #2a2;  }
    </style>

  <script>
  window.console = window.console || function(t) {};
</script>



  <script>
  if (document.location.search.match(/type=embed/gi)) {
    window.parent.postMessage("resize", "*");
  }
</script>


</head>

<body translate="no" >


  <div id="invoice-POS">

    <center id="top">
      <div class="logo"></div>
      <div class="info"> 
        <br>
        <p>█████▓▒░░ REPORTE DE CIERRE DE CAJA ░░▒▓█████ </p>
      </div><!--End Info-->
    </center><!--End InvoiceTop-->

    <div id="mid">
      <div class="info">
        <p>
        <?php
            echo nl2br("╔═══════╦════════════╗  ╔══════╦═══════╗\n");
            echo nl2br("║ FECHA ║ ".date('d/m/Y', strtotime($caja[0]->CAJ_FECHA))." ║  ║ CAJA ║ ".str_replace(" ", "&nbsp;", str_pad($nrocaja, 5, " ", STR_PAD_LEFT))." ║\n");
            echo nl2br("╚═══════╩════════════╝  ╚══════╩═══════╝\n");        
            echo nl2br("╔═════════╦══════════╗  ╔══════╦═══════════════╗\n");
            echo nl2br("║ CLIENTE ║ ".str_replace(" ", "&nbsp;", str_pad($caja[0]->CAJ_NUMOPER, 8, " ", STR_PAD_LEFT))." ║  ║ DOCU ║ ".str_replace(" ", "&nbsp;", str_pad($caja[0]->CAJ_NUMFAC, 13, " ", STR_PAD_LEFT))." ║\n");  
            echo nl2br("╚═════════╩══════════╝  ╚══════╩═══════════════╝\n");
            echo nl2br("╔═══════╦══════════════╗ \n");
            echo nl2br("║ MONTO ║ S/. " . str_replace(" ", "&nbsp;", str_pad(number_format((float)round( $caja[0]->CAJ_EFECTIVO ,2, PHP_ROUND_HALF_DOWN),2,'.',','), 8, " ", STR_PAD_LEFT))." ║\n");
            echo nl2br("╚═══════╩══════════════╝ \n");
            echo nl2br("┌─────────────┐ \n");
            echo nl2br("│ MOVIMIENTOS │ \n");
            echo nl2br("├─────────────┴───────────────────┬────────────┐\n");
            echo nl2br("│ CONCEPTO &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│ &nbsp;&nbsp; MONTO &nbsp;&nbsp;│\n");
            echo nl2br("├─────────────────────────────────┼────────────┤\n");

            foreach ($movimientos as $val) { 
                // 31 array ( 0 => (object) array( 'CMV_NRO' => 322, 'CMV_CAJA' => 313, 'CMV_TIPO' => 1, 'CMV_CODVEN' => 18, 'CMV_DESCRIPCION' => 'DEPOSITO', 'CMV_MONTO' => '100.00', ), ) -->
                $concep = $motivos[$val->CMV_TIPO]." : ".$val->CMV_DESCRIPCION;
                $find = array('á','é','í','ó','ú','â','ê','î','ô','û','ã','õ','ç','ñ','Á','É','Í','Ó','Ú','Â','Ê','Î','Ô','Û','Ã','Õ','Ç','Ñ');
                $repl = array('a','e','i','o','u','a','e','i','o','u','a','o','c','n','A','E','I','O','U','A','E','I','O','U','A','O','C','N');
                $concep = str_replace($find, $repl, $concep);
                $partes = str_split(trim($concep), 31);
                $tamano = count($partes);
                $a=0;
                if($tamano>1){
                    foreach($partes as $parte){
                        $concepto = str_replace(" ", "&nbsp;", str_pad(substr($parte, 0, 31),31," "));
                        $monto = $a==0?"S/." . str_replace(" ", "&nbsp;", str_pad($val->CMV_MONTO, 7, " ", STR_PAD_LEFT)):'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; 
                        echo nl2br("│ ".$concepto." │ ".$monto." │\n");
                        $a++;
                    }
                }else{
                    $concepto = str_replace(" ", "&nbsp;", str_pad(substr($partes[0], 0, 31),31," "));
                    $monto = "S/." . str_replace(" ", "&nbsp;", str_pad($val->CMV_MONTO, 7, " ", STR_PAD_LEFT)); 
                    echo nl2br("│ ".$concepto." │ ".$monto." │\n");
                }



                }   

                echo nl2br("└─────────────────────────────────┴────────────┘\n");
                echo nl2br(str_replace(" ", "&nbsp;", "                                  ┌────────────┐\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "              MONTO TOTAL DE CAJA │ S/." . str_pad(number_format((float)round(($cajar[0]->TOT_MOVIM+$cajar[0]->TOT_EFECTIVO),2, PHP_ROUND_HALF_DOWN),2,'.',''), 7, " ", STR_PAD_LEFT)." │\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "                                  ├────────────┤\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "                MONTO DEL SISTEMA │ S/." . str_pad(number_format((float)round( $cajar[0]->TOT_VENTAS ,2, PHP_ROUND_HALF_DOWN),2,'.',''), 7, " ", STR_PAD_LEFT)." │\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "                                  ├────────────┤\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "                       DIFERENCIA │ S/." . str_pad(number_format((float)round(($cajar[0]->TOT_MOVIM+$cajar[0]->TOT_EFECTIVO)-$cajar[0]->TOT_VENTAS,2, PHP_ROUND_HALF_DOWN),2,'.',''), 7, " ", STR_PAD_LEFT)." │\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "                                  └────────────┘\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "┌────────────────────────────┬─────────────────┐\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "│ CAJERO:                    │ FIRMA:          │\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "│ ".str_pad(trim($cajar[0]->VEM_NOMBRE), 26)." │                 │\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "│                            │                 │\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "└────────────────────────────┴─────────────────┘\n"));
                echo nl2br(str_replace(" ", "&nbsp;", "OBSERVACIONES:\n"));
                echo nl2br("Fecha de Impresión:".date('d-m-Y h:i:s a', time())."\n");

            


            ?>
            </p>
      </div>
    </div><!--End Invoice Mid-->

    <div id="bot">
            <div id="legalcopy">
                        <p class="legal"><strong>Copia Administrativa</strong>  Solo para uso interno 
                        </p>
                        <a href="<?=$path?>" target="_blank" class="abutton">Imprimir</a>
  
                    </div>

                </div><!--End InvoiceBot-->
  </div><!--End Invoice-->

  


  <script>
    
    <?php if($print==1){ ?>
        window.addEventListener("load", window.print());
    
    <?php } ?>
    </script>
</body>

</html>


