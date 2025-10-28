<?= $this->extend('templates/admin_template'); ?>
<?= $this->section('content'); ?>
<?php $session = session(); ?>
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
</style>
<!-- Content Header (Page header) -->
<div class="content-header"></div>
<!-- /.content-header -->
<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
    <div class="col-sm-3 default_cursor_cs">
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
        <div class="card">
          <div class="card-header">

            <h3 class="card-title"><?=$nro_Boleta?></h3>
            <div class="card-tools">
              <span class="badge badge-primary"><i class='fas fa-file-invoice' style='font-size:16px'></i></span>
            </div>
          </div>
          <div id="invoice-POS" class="card-body">
              <?php 
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
            
              ?>
          </div>
          <div class="card-footer">
              <?="TOTAL : S/. ".str_pad(number_format((float)round($total,2, PHP_ROUND_HALF_DOWN),2,'.',','), 6, " ", STR_PAD_LEFT)?>
          </div>
        </div>
      </div>
      <div class="col-sm-3 default_cursor_cs">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Subir Foto de la Receta</h3>
            <div class="card-tools">
              <span class="badge badge-primary"><i class='fas fa-photo-video'style='font-size:16px'></i></span>
            </div>
          </div>
          <div class="card-body">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <button type="button" class="btn btn-danger default_pointer_cs">P.A.</button>
                </div>
                <input type="text" id="pa" class="form-control">
              </div>
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <button type="button" class="btn btn-success default_pointer_cs">Cant.</button>
                </div>
                <input type="number" id="cant" class="form-control">
              </div>
              <div class="input-group mb-3">
                
                  <button type="button" id="receta_add" class="btn btn-success default_pointer_cs">Agregar Receta</button>
               
              </div>
              <!-- Change /upload-target to your upload address -->
            <form id='dropzone' action="/operaciones/subirimagen"class="dropzone" 
                style="flex: 1;display: none;flex-direction: column;align-items: center;padding: 20px;border-width: 2px;border-radius: 2px;border-color: ${props => getColor(props)};border-style: dashed;background-color: #fafafa;color: #bdbdbd;outline: none;transition: border .24s ease-in-out">
              <!-- Now setup your input fields -->
              <input type="hidden" name="local" value='<?=$local?>'/>
              <input type="hidden" name="serie" value='<?=$numser?>'/>
              <input type="hidden" name="boleta" value='<?=$numfac?>'/>
              <input type="hidden" name="tipmov" value='<?=$tipmov?>'/>
              <input type="hidden" name="dpa" name="dpa"/>
              <input type="hidden" name="dcant" name="dcant"/>              
            </form>
          </div>
          <div class="card-footer"></div>
        </div>            
      </div>
      <div class="col-sm-3 default_cursor_cs">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Fotos Adjuntas</h3>
            <div class="card-tools">
              <span class="badge badge-primary"><a href="<?=$path?>" class="btn btn-block bg-primary btn-xs" target="_blank"> <i class="fas fa-print"></i> Imprimir</a></span>
            </div>
          </div>
          <div class="card-body table-responsive p-0">
              <table id="table_imagenes" class="table table-striped table-valign-middle">
                  <thead>
                      <tr>
                          <th>PA</th>
                          <th>CANTIDAD</th>
                          <th>IMAGEN</th>     
                          <th></th>
                      </tr>
                  </thead>
                  <tbody></tbody>
              </table>                                          
          </div>
          <div class="card-footer"></div>
        </div>            
      </div>
    </div>
  </div>
</section>
<!-- /.content -->
<?= $this->endSection(); ?>
<?= $this->section('footer'); ?>
<link rel="stylesheet" href="<?= site_url('../../plugins/dropzone/min/dropzone.min.css') ?>">
<!-- DataTables -->
<link rel="stylesheet" href="<?= site_url('../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
<link rel="stylesheet" href="<?= site_url('../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">

<script src="<?= site_url('../../plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= site_url('../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= site_url('../../plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= site_url('../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>
<!-- dropzonejs -->
<script src="<?= site_url('../../plugins/dropzone/min/dropzone.min.js') ?>"></script>
<script>
Dropzone.autoDiscover = false;
$(document).ready(function() {
  var dtableImagen = $('#table_imagenes').DataTable({
        ajax: {
            url: "<?= site_url('operaciones/listar_imagenes') ?>",
            type: "POST",
            dataSrc: '',
            data: {
              local: function() { return $('input[name="local"]').val(); },
              serie: function() { return $('input[name="serie"]').val(); },
              bolet: function() { return $('input[name="boleta"]').val(); },
            }
        },
        processing: true,
        columns: [
            {data: 'REC_PA'},
            {data: 'REC_CANTIDAD'},
            {data: 'REC_IMG',
                render: function(data, type, row, meta) {
                    var rpt = '<img src="/imageRender/'+row.REC_IMG+'" class="img-thumbnail">';                    
                    return rpt       
                }       
            },
            {data: 'REC_FECHA',
                render: function() {
                    return "<button type='button' id='delmov' class='btn btn-outline-primary btn-block'><i class='fa fa-trash'></i></button>"
                }
            },
        ],
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        paging: false,
        ordering: false,
        info: false,
        bFilter: false,
        searching: false,

    });

$(".dropzone").dropzone({
  success: function(file, response) {
    dtableImagen.ajax.reload();
  },
  error: function(file) {
    // code here..
  }
});


  $("#receta_add").click(function(){
    
    if ($('#pa').val().length === 0) {
      $('#pa').focus();
      return false;
    }else{
      $('input[name="dpa"]').val($('#pa').val());
    }
    if ($('#cant').val().length === 0) {
      $('#cant').focus();
      return false;
    }else{
      $('input[name="dcant"]').val($('#cant').val());
    }

    $('#dropzone').css('display', 'flex');

  });

  


})
</script>
<?= $this->endSection(); ?>