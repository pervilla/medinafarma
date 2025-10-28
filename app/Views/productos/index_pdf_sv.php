<style>

@page { margin: 10px; }
body { margin: 10px; }
@media print {
    .page-break { display: block; page-break-before: always; }
}
.page_break { page-break-before: always; }

  #invoice-PDF {  padding: 0mm;
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
  margin:0px auto;
  padding:0px;
 /*border: 1px dashed #1C6EA4;*/
  width: 755px;
}
.bg{
   background-color: #E7E9EB;
}

</style>

<div id="invoice-PDF">
    <div id="mid">
      <div class="info">        
        <center id="body">
        <?php
        $titulo = "";
        $pagina = 1;
        $cabecera = "<table border='1' width='100%' style='border-collapse: collapse;'><tr><th>CODIGO</th><th>ARTICULO</th><th>UNIDAD</th><th>STOCK</th><th>FISICO</th><th>FALTANTE</th><th>SOBRANTE</th></tr>";
        $footer="</table>Responsable:<div class='page_break'></div>";
        $cont = 0 ;
        $tr= "";
        foreach ($productos as $producto) {
         if ($cont==0){
            $tr.="<b>INVENTARIO BOTICA MEDINAFARMA ($local)</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha Impresión: ".date('d-m-Y h:i:s a', time())." - PAGINA <b>$pagina</b> ";
            $tr.=$cabecera;
         }
         if($titulo<>$producto->TAB_NOMLARGO){
            $titulo=$producto->TAB_NOMLARGO;
            $tr.="<tr class='bg'><th colspan='7' align='left'>$producto->TAB_NOMLARGO</th></tr>";
            $cont++;
         }
         $desc=$producto->CNTLD=='C'?"<b>© ".trim($producto->ART_NOMBRE)."</b>":trim($producto->ART_NOMBRE);
         $tr.="<tr><td>$producto->ART_KEY</td><td>$desc</td><td>$producto->PRE_UNIDAD</td><td></td><td></td><td>-</td><td>+</td></tr>";

         $cont++;
         if ($cont>=61){
            $tr.=$footer; 
            $cont=0;
            $pagina++;
         }

        }
        echo $tr;
        ?>
        </center>
      </div>
    </div><!--End Invoice Mid-->
   </div><!--End Invoice-->  
  