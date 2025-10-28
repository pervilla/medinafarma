<?php

namespace App\Controllers;

use App\Models\FacartModel;
use App\Models\Server03Model;
use App\Models\ArtiModel;

class Comisiones extends BaseController {

    public function index() {
        /*     $Facart_model = new FacartModel();
          $comisiones = $Facart_model->get_comisiones('5', '2021');
          $Facart3_model = new Server03Model();
          $comisiones3 = $Facart3_model->get_comisiones_simple('5', '2021');

          $comiT = array();
          foreach ($comisiones as $comision) {
          $comi['VEM_CODVEN'] = $comision->VEM_CODVEN;
          $comi['VEM_NOMBRE'] = $comision->VEM_NOMBRE;
          $comi['VEM_META'] = $comision->VEM_META;
          $comi['COMISION'] = $comision->COMISION;
          $comi['COMISION3'] = 0;
          $comi['TOTAL'] = $comision->COMISION;
          $comi['AVANCE'] = $comision->VEM_META > 0 ? (($comision->COMISION) * 100 / $comision->VEM_META) : 0;
          foreach ($comisiones3 as $comision3) {
          if ($comision->VEM_CODVEN == $comision3->VEM_CODVEN) {
          $comi['COMISION3'] = $comision3->COMISION;
          $comi['TOTAL'] = $comision->COMISION + $comision3->COMISION;
          $comi['AVANCE'] = $comision->VEM_META > 0 ? (($comision->COMISION + $comision3->COMISION) * 100 / $comision->VEM_META) : 0;
          }
          }
          array_push($comiT, (object) $comi);
          }
          $data['comisiones'] = $comiT; */
        $data['menu']['p'] = 20;
        $data['menu']['i'] = 21;
        return view('comisiones/index',$data);
    }

    public function empleado() {
        $number = cal_days_in_month(CAL_GREGORIAN, 2, 2021); // 31
        echo $number;
        return view('comisiones/index_empleado');
    }

    public function get_comisiones() {
        $mes = $this->request->getVar('mes');
        $anio = $this->request->getVar('anio');
        $mes = $mes?$mes:date('n');
        $anio = $anio?$anio:date('Y');
        $Facart_model = new FacartModel();
        $comisiones = $Facart_model->get_comisiones($mes, $anio); 
        $Facart3_model = new Server03Model();

        //$comisiones3 = $Facart3_model->get_comisiones_simple($mes, $anio,3);        
        $comisiones2 = $Facart3_model->get_comisiones_simple($mes, $anio,2);

        $comiT = [];
        foreach ($comisiones as $comision) {
            $comi['VEM_CODVEN'] = $comision->VEM_CODVEN;
            $comi['VEM_NOMBRE'] = $comision->VEM_NOMBRE;
            $comi['VEM_META'] = $comision->VEM_META;
            $comi['COMISION'] = number_format((float)$comision->COMISION, 2, '.', '');
            $comi['COMISION3'] = 0;
            $comi['COMISION2'] = 0;
            $comi['TOTAL'] = number_format((float)$comision->COMISION, 2, '.', '');
            $comi['AVANCE'] = $comision->VEM_META > 0 ? number_format(($comision->COMISION * 100 / $comision->VEM_META), 2, '.', '') : 0;


/*
            foreach ($comisiones3 as $comision3) {
                if ($comision->VEM_CODVEN == $comision3->VEM_CODVEN) {

                    $comi['COMISION3'] = number_format((float)$comision3->COMISION, 2, '.', '');
                    $comi['TOTAL'] = number_format($comi['TOTAL'] + $comision3->COMISION, 2, '.', '');
                    $comi['AVANCE'] = $comision->VEM_META > 0 ? number_format((($comision->COMISION + $comision3->COMISION) * 100 / $comision->VEM_META), 2, '.', '') : 0;
                }
            }
*/
            foreach ($comisiones2 as $comision2) {
                if ($comision->VEM_CODVEN == $comision2->VEM_CODVEN) {
                    $comi['COMISION2'] = number_format((float)$comision2->COMISION, 2, '.', '');
                    $comi['TOTAL'] = number_format($comi['TOTAL'] + $comision2->COMISION, 2, '.', '');
                    $comi['AVANCE'] = $comision->VEM_META > 0 ? number_format((($comision->COMISION + $comision2->COMISION) * 100 / $comision->VEM_META), 2, '.', '') : 0;
                }
            }
            array_push($comiT, $comi);
        }
        echo json_encode($comiT);
    }

}
