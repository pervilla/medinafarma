<style>
@media print {
    .page-break { display: block; page-break-before: always; }
}
  #invoice-POS {
  /*box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);*/
  padding: 0mm;
  margin: 0 auto;  
  font-family: monospace, monospace;
  font-size: .7em;
  line-height: 1.2em;
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
#invoice-POS #mid {
  min-height: 90px;
  display: inline;
}
#invoice-POS .info {
  display: block;
  margin:5px auto;
  padding:5px;
 /*border: 1px dashed #1C6EA4;*/
  width: 114mm;
}

</style>


    <?php
        if($facart[0]->FAR_FBG=='B'){
          $documento= "BOLETA : B0";
          $tipoDoc=empty(trim($facart[0]->CLI_RUC_ESPOSA))?'':'DNI     :'.$facart[0]->CLI_RUC_ESPOSA."\n";
          $direccion="";
      }elseif ($facart[0]->FAR_FBG=='F') {
          $documento= "FACTURA : FA";
          $tipoDoc='RUC     : '.TRIM($facart[0]->CLI_RUC_ESPOSO)."\n";
          $direccion ='DIRECCION : '.TRIM($facart[0]->CLI_CASA_DIREC).' '.TRIM($facart[0]->CLI_CASA_NUM)."\n";
      }elseif ($facart[0]->FAR_FBG=='G') {
          $documento= "GUIA : G0";
          $tipoDoc='';
          $direccion="";
      }
      $cliente = $facart[0]->FAR_CODCLIE==1?$facart[0]->FAR_CLIENTE:$facart[0]->CLI_NOMBRE;
      
      $nro_Boleta = $documento.TRIM($facart[0]->FAR_NUMSER)."-".$facart[0]->FAR_NUMFAC;
    ?>

<div id="invoice-POS">

    <center id="top">
      <img src="<?=site_url('../../dist/img/medinafarma-black.jpg')?>" style="height: 58px; width: 231px;">
      <div class="info"> 
        <br>
        <p><?php 
          echo nl2br("INVERSIONES SAN MARTIN S.C.R.L.\n");
          echo nl2br("RUC: 20450337839\n");
          echo nl2br("Jr. Huallaga Nro 601 - Juanjuí - Mcal Cacéres - San Martín\n");
          ?>
          </p>
      </div><!--End Info-->
    </center><!--End InvoiceTop-->

    <div id="mid">
      <div class="info">
        <p> 
        <center id="body">
        <?php
        $copia = 0 ;
        foreach ($facart as $val2) {
          if($copia == 0){
          echo nl2br("--------------------------------------------------------------" . "\n");          
          echo nl2br($documento);
          echo nl2br(TRIM($val2->FAR_NUMSER)."-".$val2->FAR_NUMFAC."\n");
          echo nl2br("--------------------------------------------------------------" . "\n");     
          date_default_timezone_set('America/Lima');
          echo str_replace(" ", "&nbsp;", str_pad(substr(trim("Fecha Emisión: ".date('d-m-Y', strtotime($val2->FAR_FECHA))." ".trim($val2->FAR_HORA)."m."), 0, 62),62, " ")); 
          
          
          echo nl2br("\n");
          echo str_replace(" ", "&nbsp;", str_pad(substr(trim("Responsable : ".trim($val2->VEM_NOMBRE)), 0, 62),62, " ")); 
          echo nl2br("\n");

          echo nl2br("--------------------------------------------------------------" . "\n");

          echo str_replace(" ", "&nbsp;", str_pad(substr(trim("Cliente : ".trim($cliente)), 0, 62),62, " ")); 

          echo nl2br("\n");
          echo str_replace(" ", "&nbsp;", str_pad(substr(trim($tipoDoc), 0, 62),62, " ")); 

          echo nl2br("\n");
          echo str_replace(" ", "&nbsp;", str_pad(substr(trim($direccion), 0, 62),62, " ")); 
          echo nl2br("\n");

          echo nl2br("--------------------------------------------------------------\n");
          echo nl2br(str_replace(" ", "&nbsp;", ("PRODUCTO                             CANT.      P/U.   IMPORTE\n")));
          echo nl2br("--------------------------------------------------------------\n");
          }

          
        
          $concepto = str_replace(" ", "&nbsp;", str_pad(substr(trim($val2->ART_NOMBRE), 0, 62),62, " "));                 
          $monto = str_replace(" ", "&nbsp;", str_pad($val2->FAR_CANTIDAD_P/$val2->FAR_EQUIV, 38, " ", STR_PAD_LEFT)); 
          $monto.= " ";
          $monto.= str_replace(" ", "&nbsp;", str_pad(substr($val2->FAR_DESCRI, 0, 5), 5, " ", STR_PAD_LEFT)); 
          $monto.= str_replace(" ", "&nbsp;", str_pad(number_format((float)round($val2->FAR_PRECIO ,2, PHP_ROUND_HALF_DOWN),2,'.',','), 8, " ", STR_PAD_LEFT)); 
          $monto.= str_replace(" ", "&nbsp;", str_pad(number_format((float)round($val2->FAR_PRECIO*$val2->FAR_CANTIDAD_P/$val2->FAR_EQUIV ,2, PHP_ROUND_HALF_DOWN),2,'.',','), 8, " ", STR_PAD_LEFT)); 
          echo nl2br($concepto."\n");
          echo nl2br($monto."\n");
          $total = $val2->FAR_BRUTO;
          $copia++;
        }
        echo nl2br("--------------------------------------------------------------\n");
    
        echo str_replace(" ", "&nbsp;", str_pad("SUBTOTAL  : S/. ".str_pad(number_format((float)round($val2->FAR_BRUTO,2, PHP_ROUND_HALF_DOWN),2,'.',','), 6, " ", STR_PAD_LEFT), 60, " ", STR_PAD_LEFT)) ;
        echo nl2br("\n");
        echo str_replace(" ", "&nbsp;", str_pad("DESCUENTO : S/. ".str_pad(number_format((float)round('0.00',2, PHP_ROUND_HALF_DOWN),2,'.',','), 6, " ", STR_PAD_LEFT), 60, " ", STR_PAD_LEFT)) ;
        echo nl2br("\n");
        echo str_replace(" ", "&nbsp;", str_pad("ISC       : S/. ".str_pad(number_format((float)round('0.00',2, PHP_ROUND_HALF_DOWN),2,'.',','), 6, " ", STR_PAD_LEFT), 60, " ", STR_PAD_LEFT)) ;
        echo nl2br("\n");
        echo str_replace(" ", "&nbsp;", str_pad("IGV (18%) : S/. ".str_pad(number_format((float)round('0.00',2, PHP_ROUND_HALF_DOWN),2,'.',','), 6, " ", STR_PAD_LEFT), 60, " ", STR_PAD_LEFT)) ;
        echo nl2br("\n");
        echo str_replace(" ", "&nbsp;", str_pad("IMP.TOTAL : S/. ".str_pad(number_format((float)round($total,2, PHP_ROUND_HALF_DOWN),2,'.',','), 6, " ", STR_PAD_LEFT), 60, " ", STR_PAD_LEFT)) ;
        echo nl2br("\n");
      



       
        if ($val2->FAR_FBG=='F') {
          $chl= "20450337839|01|F0".$val2->FAR_NUMSER."|".$val2->FAR_NUMFAC."|0.00|".$val2->FAR_BRUTO."|".date('Y-m-d', strtotime($val2->FAR_FECHA))."|6|";
          $img= '<img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl='.$chl.'" style="height: 150px; width: 150px;">';

        }else{
          $img = '';
        } 
        
        $copia = $val2->COP_DESCRIP;



       
        
        
        
        
        ?>
       
       </center>
      </p>
      </div>
    </div><!--End Invoice Mid-->

    <center id="footer">
      
      <div class="info"> 
        <br>
        <p><?php 
           echo nl2br("Forma de pago: CONTADO \n");
           if ($val2->FAR_FBG=='F') {
            echo $qr;
           }
           echo nl2br("\n");           
           echo nl2br("Representación impresa de la "."\n");
           echo nl2br("Obligado a ser Emisor Electrónico mediante la Resolución de Superintendecia"."\n");
           echo nl2br("N° 155-2017/SUNAT-Anexo IV  \n");
           echo nl2br(" " . "\n");
           
           echo nl2br("BIENES TRANSFERIDOS EN LA AMAZONÍA PARA SER CONSUMIDOS EN LA MISMA"."\n");
           echo nl2br(" " . "\n");


           echo nl2br("GRACIAS POR SU COMPRA !  \n");

          ?>
          </p>
      </div><!--End Info-->
    </center><!--End InvoiceTop-->


  </div><!--End Invoice-->