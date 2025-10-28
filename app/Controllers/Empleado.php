<?php

namespace App\Controllers;

use App\Models\CajaMovimientosModel;
use App\Models\FacartModel;
use App\Models\Server03Model;
class Empleado extends BaseController {

    public function index() {
        echo "Empleados";
    }

    public function creditos_adelantos() {
        $data['menu']['p'] = 20;
        $data['menu']['i'] = 22;
        return view('empleado/index_creditos',$data);
    }

    public function rentabilidad() {
        $data['menu']['p'] = 20;
        $data['menu']['i'] = 23;
        return view('empleado/index_rentabilidad',$data);
    }
    public function get_rentabilidad(){
        $mes = $this->request->getVar('mes');
        $anio = $this->request->getVar('anio');
        $mes = $mes?$mes:date('n');
        $anio = $anio?$anio:date('Y');
        $Facart_model = new FacartModel();
        $comisiones = $Facart_model->get_rentabilidad_empleado($mes, $anio);
        $Facart3_model = new Server03Model();
        $comisiones3 = $Facart3_model->get_rentabilidad_empleado($mes, $anio,3);
        $comisiones2 = $Facart3_model->get_rentabilidad_empleado($mes, $anio,2);
        $comiT = array();
        foreach ($comisiones as $comision) {
            $comi['VEM_CODVEN'] = $comision->VEM_CODVEN;
            $comi['VEM_NOMBRE'] = $comision->VEM_NOMBRE;
            $comi['VEM_META'] = number_format((float)$comision->VEM_META, 2, '.', '');
            $comi['COMISION'] = number_format((float)$comision->NETO, 2, '.', '');
            $comi['COSTO'] = number_format((float)$comision->COSTO, 2, '.', '');
            $comi['COMISION3'] = 0;
            $comi['COSTO3'] = 0;
            $comi['COMISION2'] = 0;
            $comi['COSTO2'] = 0;
            $comi['TOTAL'] = number_format((float)$comision->NETO, 2, '.', '');
            $comi['TOTALCOST'] = number_format((float)$comision->COSTO, 2, '.', '');
            $comi['AVANCE'] = $comision->VEM_META > 0 ? number_format((float)(($comision->NETO) * 100 / $comision->VEM_META), 2, '.', '') : 0;
            foreach ($comisiones3 as $comision3) {
                if ($comision->VEM_CODVEN == $comision3->VEM_CODVEN) {
                    $comi['COMISION3'] = number_format((float)$comision3->VENTA, 2, '.', '');
                    $comi['COSTO3'] = number_format((float)$comision3->COSTO, 2, '.', '');
                    $comi['TOTAL'] = number_format((float)$comision->NETO + $comision3->VENTA, 2, '.', '');
                    $comi['TOTALCOST'] = number_format((float)$comision->COSTO + $comision3->COSTO, 2, '.', '');
                    $comi['AVANCE'] = $comision->VEM_META > 0 ? number_format((float)(($comision->NETO + $comision3->VENTA) * 100 / $comision->VEM_META), 2, '.', '') : 0;
                }
            }
            foreach ($comisiones2 as $comision2) {
                if ($comision->VEM_CODVEN == $comision2->VEM_CODVEN) {
                    $comi['COMISION2'] = number_format((float)$comision2->VENTA, 2, '.', '');
                    $comi['COSTO2'] = number_format((float)$comision2->COSTO, 2, '.', '');
                    $comi['TOTAL'] = number_format((float)$comision->NETO + $comision2->VENTA, 2, '.', '');
                    $comi['TOTALCOST'] = number_format((float)$comision->COSTO + $comision2->COSTO, 2, '.', '');
                    $comi['AVANCE'] = $comision->VEM_META > 0 ? number_format((float)(($comision->NETO + $comision2->VENTA) * 100 / $comision->VEM_META), 2, '.', '') : 0;
                }
            }

            $rent = number_format((float)($comi['TOTAL']-$comi['TOTALCOST']), 2, '.', '');
            $porc = number_format((float)(100*($comi['TOTAL']-$comi['TOTALCOST']) /$comi['TOTAL']), 2, '.', '');

            $comi['RENTABILIDAD'] = $rent;
            $comi['PORCENTAJE'] = "<span class='badge bg-success'>$porc%</span>";
            $comi['PORCIENTO'] = $porc;
            array_push($comiT, (object) $comi);
        }
        echo json_encode($comiT);
    }
    public function get_creditos() {
        $mes = $this->request->getVar('mes');
        $anio = $this->request->getVar('anio');
        $mes = $mes?$mes:date('n');
        $anio = $anio?$anio:date('Y');

        $CajaMovimientosModel = new CajaMovimientosModel();
        $creditos = $CajaMovimientosModel->get_creditos($mes, $anio);
        return $this->response->setJSON($creditos);
    }

    public function get_creditos_empleado() {
        $request = service('request');

        $CajaMovimientosModel = new CajaMovimientosModel();
        $movi = $CajaMovimientosModel->get_creditos_empleado($request->getPost('mes'), $request->getPost('anio'), $request->getPost('empl'));

        foreach ($movi as $val) {
            $fecha = date('d/m/Y', strtotime($val->CAJ_FECHA));
            echo "<tr><td>$fecha</td><td>$val->TAB_NOMLARGO</td><td>$val->CMV_DESCRIPCION</td><td>$val->CMV_MONTO</td></tr>";
        }
    }

    //put your code here
}
