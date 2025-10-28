<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;

use App\Models\ArticuloModel;
use App\Models\Server03Model;
use App\Models\ArtiModel;
use App\Models\PreciosModel;
use App\Models\PreciosDigemidModel;
use App\Models\LoteModel;
use App\Models\EnfermedadesModel;
use App\Models\TablasModel;
use App\Models\VemaestModel;
use DonatelloZa\RakePlus\RakePlus;
use Dompdf\Dompdf;
use App\Models\FacartModel;
use Luecano\NumeroALetras\NumeroALetras;

/**
 * Description of Productos
 *
 * @author José Luis
 */
class Productos extends BaseController
{

    public function index()
    {
        $data['menu']['p'] = 50;
        $data['menu']['i'] = 51;
        return view('productos/index');
    }

    public function get_productos()
    {
        $busqueda = $this->request->getVar('busqueda');
        $ArticuloModel = new ArticuloModel();
        $articulos = $ArticuloModel->get_articulos_det($busqueda);
        return $this->response->setJSON($articulos);
    }
    public function enfermedades()
    {
        $EnfermedadesModel = new EnfermedadesModel();
        $data['sistemas'] = $EnfermedadesModel->get_sistema();

        $Tablas_model = new TablasModel();
        $data['pa'] = $Tablas_model->get_by_tipreg('129', '');

        return view('productos/index_enfermedades', $data);
    }
    public function get_enfermedades()
    {
        $EnfermedadesModel = new EnfermedadesModel();
        $enfermedades = $EnfermedadesModel->get_enfermedades();
        return $this->response->setJSON($enfermedades);
    }
    public function get_medicamentos()
    {
        $id_enfermedad = $this->request->getVar('id_enfermedad');
        $EnfermedadesModel = new EnfermedadesModel();
        $enfermedades = $EnfermedadesModel->get_medicamentos($id_enfermedad);
        return $this->response->setJSON($enfermedades);
    }

    public function set_pa_medicamentos()
    {
        $data['ID_ENFERMEDAD'] = $this->request->getVar('id_enfermedad');
        $data['TAB_NUMTAB'] = $this->request->getVar('id_pa');
        if ($data['ID_ENFERMEDAD'] > 0) {
            $EnfermedadesModel = new EnfermedadesModel();
            $nro = $EnfermedadesModel->get_exist_pa($data);
            if ($nro->ID_ENFERMEDAD == 0) {
                $EnfermedadesModel = new EnfermedadesModel();
                $EnfermedadesModel->insertar_pa($data);
                return true;
            }
        }
        return false;
    }
    public function set_enfermedad()
    {
        $data['CUADRO'] = $this->request->getVar('cuadro');
        $data['ID_SISTEMA'] = $this->request->getVar('idsistema');
        $data['DESCRIPCION'] = strtoupper($this->request->getVar('descripcion'));
        $EnfermedadesModel = new EnfermedadesModel();
        $id_enfermedad = $EnfermedadesModel->insertar($data);

        $phrases = RakePlus::create($data['CUADRO'], 'es_AR')->get();
        $data2 = array();
        $i = 0;
        foreach ($phrases as $frase) {
            $data2[$i]['ID_ENFERMEDAD'] = $id_enfermedad;
            $data2[$i]['DESCRIPCION'] = $frase;
            $i++;
        }

        $EnfermedadesKeyModel = new EnfermedadesModel();
        $EnfermedadesKeyModel->insertar_keys($data2);

        return $id_enfermedad;
    }

    public function get_stock()
    {
        $artkey = $this->request->getVar('artkey');
        $artsbg = $this->request->getVar('artsbg');
        $local = $this->request->getVar('local');
        $btn = $this->request->getVar('btn');
        if (!empty($artkey) || $artsbg > 0) {
            $Server03Model = new Server03Model();
            $articulos = $Server03Model->get_articulos_selec($artkey, $artsbg, $local);
            $locales = array('', 'CENTRO', 'JCILLO', 'PMEZA');
            foreach ($articulos as $val) {
                $class = $val->StockGen > 0 ? 'even bg-success' : '';
                $RNT = '';
                $RENTABLE = array(113, 444, 521, 456);
                if (in_array($val->art_familia, $RENTABLE)) {
                    $RNT = '<i class="fas fa-chart-line"></i>';
                    $class = 'even bg-gray-dark';
                }
                $group = "<div class='btn-group btn-group-sm' role='group' aria-label='Small button group'><button type='button' class='btn btn-primary' data-id='" . $val->ARM_CODART . "'  data-toggle='modal' data-target='#modal-overlay'><i class='fas fa-eye'></i></button><button type='button' id='addRequer' data-id='" . $val->ARM_CODART . "' data-local='" . $local . "' class='btn btn-warning'><i class='fa fa-shopping-basket'></i> </button></div>";
                $btn = $btn ? "<td><button id='selprod' class='btn btn-primary btn-xs'><i class='fas fa-pills'></i> </button></td>" : "<td>" . $group . "</td>";
                $tr = "<tr class='$class'><td>$locales[$local]</td></td></t><td class='descrip'>$RNT $val->ART_NOMBRE</td><td>$val->STOCK</td>$btn</tr>";
                echo $tr;
            }
        }
    }
    public function get_stock_sl()
    {
        $artkey = $this->request->getVar('artkey');
        $artsbg = $this->request->getVar('artsbg');
        $local = $this->request->getVar('local');
        $btn = $this->request->getVar('btn');
        if (!empty($artkey) || $artsbg > 0) {
            $Server03Model = new Server03Model();
            $articulos = $Server03Model->get_articulos_selec($artkey, $artsbg, $local);
            $locales = array('', 'CENTRO', 'JCILLO', 'PMEZA');
            foreach ($articulos as $val) {
                $class = $val->StockGen > 0 ? 'even bg-success' : '';
                $RNT = '';
                $RENTABLE = array(113, 444, 521, 456);
                if (in_array($val->art_familia, $RENTABLE)) {
                    $RNT = '<i class="fas fa-chart-line"></i>';
                    $class = 'even bg-gray-dark';
                }
                $pqt = round($val->ART_PQT, 0);
                $und = round($val->ART_UNID, 0);
                $pre = round($val->PRE_PRE1, 2);
                $btn = $btn ? "<td><button id='selprod' class='btn btn-block bg-gradient-primary btn-xs'><i class='fas fa-pills'></i></button></td>" : '';
                $tr = "<tr data-id='$val->ARM_CODART' data-toggle='modal' data-target='#modal-overlay' class='$class'></td></t><td class='descrip'>$RNT $val->ART_NOMBRE</td><td>$pqt</td><td>$und</td><td>S/." . number_format((float)round($pre, 2, PHP_ROUND_HALF_DOWN), 2, '.', ',') . "</td>$btn</tr>";





                echo $tr;
            }
        }
    }
    public function get_inventario()
    {
        $session = session();
        if ($session->get('caja')) {
            $caja = $session->get('caja');
            switch ($caja) {
                case '1':
                    $data['color'] = 'success';
                    break;
                case '2':
                    $data['color'] = 'danger';
                    break;
                case '3':
                    $data['color'] = 'info';
                    break;
            }
        } else {
            $caja = 1;
            $data['color'] = 'success';
        }
        $data['menu']['p'] = 50;
        $data['menu']['i'] = 54;
        return view('productos/index_inventario', $data);
    }
    public function get_controlados()
    {
        $session = session();
        if ($session->get('caja')) {
            $caja = $session->get('caja');
            switch ($caja) {
                case '1':
                    $data['color'] = 'success';
                    break;
                case '2':
                    $data['color'] = 'danger';
                    break;
                case '3':
                    $data['color'] = 'info';
                    break;
            }
        } else {
            $caja = 1;
            $session->set('caja', $caja);
            $data['color'] = 'success';
        }
        $data['caja'] = $caja;
        $data['menu']['p'] = 50;
        $data['menu']['i'] = 55;
        return view('productos/index_controlados', $data);
    }
    public function get_stock_articulos()
    {
        $session = session();
        $caja = $session->get('caja');
        $stock = $this->request->getVar('stock');
        $ArticuloModel = new ArticuloModel();
        $articulos = $ArticuloModel->get_stock_articulos($stock, $caja);
        return $this->response->setJSON($articulos);
    }
    public function get_mov_controlados()
    {
        $session = session();
        $caja = $session->get('caja');
        $fecha = $this->request->getPost('fecha');
        $factu = $this->request->getVar('factura');
        $porciones = explode("-", $fecha);
        $fecha01 = date('d/m/Y', strtotime($porciones[0]));
        $fecha02 = date('d/m/Y', strtotime($porciones[1]));

        $ArticuloModel = new ArticuloModel();
        $articulos = $ArticuloModel->get_mov_controlados($caja, $fecha01, $fecha02, $factu);
        return $this->response->setJSON($articulos);
    }
    public function actualizar_productos()
    {
        $data['menu']['p'] = 50;
        $data['menu']['i'] = 56;
        return view('productos/index_actualizar', $data);
    }
    public function crea_productos()
    {
        $serve =  $this->request->getVar('serve');
        $Server03Model = new Server03Model();
        $get_max_numart = $Server03Model->get_max_numart($serve);
        $ArtiModel = new ArtiModel();
        $get_arti_act = $ArtiModel->get_arti_act($get_max_numart);
        echo $get_max_numart . '|' . $get_arti_act;
    }
    public function exporta_nuevos_prod()
    {
        $serve = $this->request->getVar('serve');
        $keyar = $this->request->getVar('keyval');
        $total = $this->crea_arti($keyar, $serve);
        if ($total > 0) {
            $total = $this->crea_articulo($keyar, $serve);
        };
        if ($total > 0) {
            $total = $this->crea_precios($keyar, $serve);
        };
        if ($total > 0) {
            $total = $this->crea_lote($keyar, $serve);
        };
        return $total;
    }

    public function actualiza_precios()
    {
        try {
            $ArticuloModel = new ArticuloModel();
            $resultado = $ArticuloModel->ejecuta_actualizacion_productos();
            
            // Debug
            log_message('debug', 'Resultado del modelo: ' . print_r($resultado, true));
            
            $response = [
                'success' => $resultado['success'],
                'mensaje' => $resultado['success'] 
                    ? $resultado['message']
                    : "Error: " . $resultado['message']
            ];
    
            if (!$resultado['success'] && isset($resultado['error'])) {
                $response['error'] = $resultado['error'];
            }
    
            return $this->response->setJSON($response);
        } catch (\Throwable $e) {
            log_message('error', 'Error en actualiza_precios: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'mensaje' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function crea_arti($keyar, $serve)
    {
        $ArtiModel = new ArtiModel();
        $data = $ArtiModel->get_arti($keyar);
        $comiT = array();
        foreach ($data as $item) {
            $comi['ART_KEY'] = $item->ART_KEY;
            $comi['ART_CODCIA'] = $item->ART_CODCIA;
            $comi['ART_NOMBRE'] = $item->ART_NOMBRE;
            $comi['ART_COSTO'] = $item->ART_COSTO;
            $comi['ART_TIPO'] = $item->ART_TIPO;
            $comi['ART_ESTADO'] = $item->ART_ESTADO;
            $comi['ART_CALIDAD'] = $item->ART_CALIDAD;
            $comi['ART_UNIDAD'] = $item->ART_UNIDAD;
            $comi['ART_EX_IGV'] = $item->ART_EX_IGV;
            $comi['ART_DECIMALES'] = $item->ART_DECIMALES;
            $comi['ART_FAMILIA'] = $item->ART_FAMILIA;
            $comi['ART_ALTERNO'] = $item->ART_ALTERNO;
            $comi['ART_CP'] = $item->ART_CP;
            $comi['ART_FLAG_STOCK'] = $item->ART_FLAG_STOCK;
            $comi['ART_FLAG_CAMBIO'] = $item->ART_FLAG_CAMBIO;
            $comi['ART_FECHAHORA'] = $item->ART_FECHAHORA;
            $comi['ART_GRUPOP'] = $item->ART_GRUPOP;
            $comi['ART_CODREL'] = $item->ART_CODREL;
            array_push($comiT, $comi);
        }
        $Server03Model = new Server03Model();
        $total = $Server03Model->crear_prod_arti($comiT, $serve);
        return $total;
    }
    public function ver_arti()
    {
        $keyar = $this->request->getVar('keyval');
        $ArtiModel = new ArtiModel();
        $articulos = $ArtiModel->get_arti($keyar);
        return $this->response->setJSON($articulos);
    }
    public function crea_articulo($keyar, $serve)
    {
        $ArticuloModel = new ArticuloModel();
        $data = $ArticuloModel->get_articulos($keyar);
        $comiT = array();
        foreach ($data as $item) {
            $comi['ARM_CODART'] = $item->ARM_CODART;
            $comi['ARM_CODCIA'] = $item->ARM_CODCIA;
            $comi['ARM_STOCK'] = $item->ARM_STOCK;
            $comi['ARM_INGRESOS'] = $item->ARM_INGRESOS;
            $comi['ARM_SALIDAS'] = $item->ARM_SALIDAS;
            $comi['ARM_STOCK_INI'] = $item->ARM_STOCK_INI;
            $comi['ARM_COSPRO'] = $item->ARM_COSPRO;
            $comi['ARM_STOCK2'] = $item->ARM_STOCK2;
            $comi['ARM_COSTO_ULT'] = $item->ARM_COSTO_ULT;
            $comi['ARM_FECHA_ULT'] = date('d/m/Y');
            $comi['ARM_SALDO_S'] = $item->ARM_SALDO_S;
            $comi['ARM_SALDO_S2'] = $item->ARM_SALDO_S2;
            $comi['ARM_SALDO_N'] = $item->ARM_SALDO_N;
            $comi['ARM_SALDO_N2'] = $item->ARM_SALDO_N2;
            $comi['ARM_STOCK_T'] = $item->ARM_STOCK_T;
            $comi['ARM_AJUSTA'] = $item->ARM_AJUSTA;
            array_push($comiT, $comi);
        }
        $Server03Model = new Server03Model();
        $total = $Server03Model->crear_prod_articulo($comiT, $serve);
        return $total;
    }
    public function crea_precios($keyar, $serve)
    {
        $PreciosModel = new PreciosModel();
        $data = $PreciosModel->get_precios($keyar);
        $comiT = array();
        foreach ($data as $item) {
            $comi['PRE_CODCIA'] = $item->PRE_CODCIA;
            $comi['PRE_CODART'] = $item->PRE_CODART;
            $comi['PRE_SECUENCIA'] = $item->PRE_SECUENCIA;
            $comi['PRE_POR1'] = $item->PRE_POR1;
            $comi['PRE_POR2'] = $item->PRE_POR2;
            $comi['PRE_PRE1'] = $item->PRE_PRE1;
            $comi['PRE_PRE2'] = $item->PRE_PRE2;
            $comi['PRE_UNIDAD'] = $item->PRE_UNIDAD;
            $comi['PRE_EQUIV'] = $item->PRE_EQUIV;
            $comi['PRE_FLAG_UNIDAD'] = $item->PRE_FLAG_UNIDAD;
            array_push($comiT, $comi);
        }
        $Server03Model = new Server03Model();
        $total = $Server03Model->crear_prod_precios($comiT, $serve);
        return $total;
    }
    public function crea_lote($keyar, $serve)
    {
        $LoteModel = new LoteModel();
        $data = $LoteModel->get_lotes($keyar);
        $comiT = array();
        foreach ($data as $item) {
            $comi['LOT_CODCIA'] = $item->LOT_CODCIA;
            $comi['LOT_CODART'] = $item->LOT_CODART;
            $comi['LOT_NROLOTE'] = $item->LOT_NROLOTE;
            $comi['LOT_CODCLIE'] = $item->LOT_CODCLIE;
            $comi['LOT_FECHA_VCTO'] = date('d/m/Y');
            $comi['LOT_SALDOS'] = $item->LOT_SALDOS;
            $comi['LOT_CODUSU'] = $item->LOT_CODUSU;
            array_push($comiT, $comi);
        }
        $Server03Model = new Server03Model();
        $total = $Server03Model->crear_prod_lote($comiT, $serve);
        return $total;
    }
    public function get_precios_digemid()
    {
        $key = $this->request->getVar('artkey');
        $PreciosDigemidModel = new PreciosDigemidModel();
        $data = $PreciosDigemidModel->get_precios_digemid($key);
        return json_encode($data);
    }
    public function get_pa()
    {
        $Tablas_model = new TablasModel();
        $selected = array('178', '179');
        $data = $Tablas_model->get_by_tipreg('129', '');
        $ndata = array();
        $i = 0;
        foreach ($data as $val) {
            $ndata[$i]['id'] = $val->TAB_NUMTAB;
            $ndata[$i]['text'] = trim($val->TAB_NOMLARGO);
            if (in_array($val->TAB_NUMTAB, $selected)) {
                $ndata[$i]['selected'] = true;
            }

            $i++;
        }
        return json_encode(array("results" => $ndata));
    }
    public function createpdf()
    {
        set_time_limit(300);
        $session = session();
        $caja = $session->get('caja') ? $session->get('caja') : 1;
        $locales = array('', 'CENTRO', 'JUANJUICILLO', 'ALMACEN');
        $stock = 1;
        $ArticuloModel = new ArticuloModel();
        $data['productos'] = $ArticuloModel->get_stock_articulos($stock, $caja);
        $data['local'] = $locales[$caja];

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('productos/index_pdf', $data));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream();
    }
    public function createpdfsv()
    {
        set_time_limit(300);
        $session = session();
        $caja = $session->get('caja') ? $session->get('caja') : 1;
        $locales = array('', 'CENTRO', 'JUANJUICILLO', 'ALMACEN');
        $stock = 1;
        $ArticuloModel = new ArticuloModel();
        $data['productos'] = $ArticuloModel->get_stock_articulos($stock, $caja);
        $data['local'] = $locales[$caja];

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('productos/index_pdf_sv', $data));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream();
    }

    public function createlistaprecios()
    {
        $session = session();
        $caja = $session->get('caja') ? $session->get('caja') : 1;
        $locales = array('', 'CENTRO', 'JUANJUICILLO', 'ALMACEN');
        $stock = 1;
        $ArticuloModel = new ArticuloModel();
        $data['productos'] = $ArticuloModel->get_stock_articulos($stock, $caja, "articulo", "und");
        $data['local'] = $locales[$caja];

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('productos/index_lista_precios', $data));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream();
    }
    public function get_equiv()
    {
        $keyar = $this->request->getVar('keyval');
        $precio = new PreciosModel();
        $data   = $precio->get_equiv($keyar);
        return json_encode($data);
    }
}
