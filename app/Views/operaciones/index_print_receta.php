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
  width: 180mm;
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
  min-height: 90px;
  display: inline;
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
#invoice-POS .cabecera {
  text-align: center;
  width:100%;
  display: block;
  float: left;
  margin: auto;
}
#invoice-POS .info {
  display: block;
  margin:20px auto;
  padding:20px;
  border: 1px dashed #1C6EA4;
  width: 110mm;
}
#invoice-POS .title {
  float: left;
}
#invoice-POS .title p {
  text-align: left;
}
#invoice-POS table {
  width: 100%;
  border-collapse: collapse;
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


img {
 height: 100%;
 width: 100%;
 object-fit: contain;
}



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
<?php
        if($facart[0]->FAR_FBG=='B'){
          $documento= "BOLETA : B0";
          $tipoDoc=empty(trim($facart[0]->CLI_RUC_ESPOSA))?'':'DNI:'.$facart[0]->CLI_RUC_ESPOSA."\n";
          $direccion="";
      }elseif ($facart[0]->FAR_FBG=='F') {
          $documento= "FACTURA : F0";
          $tipoDoc='RUC : '.TRIM($facart[0]->CLI_RUC_ESPOSO)."\n";
          $direccion ='DIRECCION : '.TRIM($facart[0]->CLI_CASA_DIREC).' '.TRIM($facart[0]->CLI_CASA_NUM)."\n";
      }elseif ($facart[0]->FAR_FBG=='G') {
          $documento= "GUIA : G0";
          $tipoDoc='';
          $direccion="";
      }
      $nro_Boleta = $documento.TRIM($facart[0]->FAR_NUMSER)."-".$facart[0]->FAR_NUMFAC;
    ?>

  <div id="invoice-POS">

    <center id="top">
      <div class="logo"></div>
      <div class="cabecera"> 
        <br>
        <p>█████▓▒░░ REPORTE DE RECETA DE PSICOTROPICOS ░░▒▓█████ </p>
        <p>&nbsp;</p>
        &nbsp;
        &nbsp;
        &nbsp;
      </div><!--End Info-->
    </center><!--End InvoiceTop-->

    <div id="mid">
    <div class="info">
      <p>
              <?php 
              
              echo nl2br($nro_Boleta."\n");
              echo nl2br("--------------------------------------------------------------\n");
              echo nl2br(str_replace(" ", "&nbsp;", ("PRODUCTO                             CANT.      P/U.   IMPORTE\n")));
              echo nl2br("--------------------------------------------------------------\n");
              
            foreach ($facart as $val2) {
              $concepto = str_replace(" ", "&nbsp;", str_pad(substr(trim($val2->ART_NOMBRE), 0, 62),62, " "));                 
              $monto = str_replace(" ", "&nbsp;", str_pad($val2->FAR_CANTIDAD_P/$val2->FAR_EQUIV, 38, " ", STR_PAD_LEFT)); 
              $monto.= " ";
              $monto.= str_replace(" ", "&nbsp;", str_pad(substr($val2->FAR_DESCRI, 0, 5), 5, " ", STR_PAD_LEFT)); 
              $monto.= str_replace(" ", "&nbsp;", str_pad(number_format((float)round($val2->FAR_PRECIO ,2, PHP_ROUND_HALF_DOWN),2,'.',','), 8, " ", STR_PAD_LEFT)); 
              $monto.= str_replace(" ", "&nbsp;", str_pad(number_format((float)round($val2->FAR_PRECIO*$val2->FAR_CANTIDAD_P/$val2->FAR_EQUIV ,2, PHP_ROUND_HALF_DOWN),2,'.',','), 8, " ", STR_PAD_LEFT)); 
              echo nl2br($concepto."\n");
              echo nl2br($monto."\n");
              $total = $val2->FAR_BRUTO;
            }
            echo nl2br("--------------------------------------------------------------\n");
            echo nl2br("TOTAL : S/. ".str_pad(number_format((float)round($total,2, PHP_ROUND_HALF_DOWN),2,'.',','), 6, " ", STR_PAD_LEFT)."\n");
            
            echo nl2br("Fecha de Impresión:".date('d-m-Y h:i:s a', time())."\n");
              ?>
              </p>
          </div>


      <div class="title">
        
        <?php
foreach ($imagenes as $val) {
echo "<p>P.A.    :$val->REC_PA</p>";
echo "<p>CANTIDAD:$val->REC_CANTIDAD</p>";
echo '<img src="/imageRender/'.$val->REC_IMG.'" class="img-thumbnail" style="min-height: 100%; max-width: 180mm;">';

}

            

            


            ?>
         
      </div>
    </div><!--End Invoice Mid-->


</body>

</html>


