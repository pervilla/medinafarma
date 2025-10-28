<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Controllers\BaseController;
use App\Models\TablasModel;
use App\Models\AllogModel;
use App\Models\ArtiModel;
use App\Models\ArticuloModel;
use App\Models\FacartModel;
use App\Models\PreciosModel;
use App\Models\Server03Model;
use App\Models\Server03PruebaModel;
use App\Models\RecetaImagenModel;
use Exception;



/**
 * Description of Operaciones
 *
 * @author José Luis
 */
class Operaciones extends BaseController
{
    protected $helpers = ['form'];
    public function index()
    {
        $Tablas_model = new TablasModel();
        $operacion = $Tablas_model->get_by_tipreg('4', '1');
        $data['operacion'] = $operacion;
        $data['operaciones'] = array();
        $data['menu']['p'] = 30;
        $data['menu']['i'] = 31;
        return view('operaciones/index', $data);
    }

    public function exportacion_guias()
    {

        $session = session();
        if ($session->get('caja')) {
            $local = $session->get('caja');
            switch ($local) {
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
            $local = 1;
            $data['color'] = 'success';
        }
        $data['local'] = $local;
        $smes = $session->get('mes_caja') ? $session->get('mes_caja') : date('m');
        $session->set('mes_caja', $smes);
        $data['menu']['p'] = 30;
        $data['menu']['i'] = 32;
        return view('operaciones/index_exportacion', $data);
    }

    public function guias_entrada()
    {
        $session = session();
        if ($session->get('caja')) {
            $local = $session->get('caja');
            switch ($local) {
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
            $local = 1;
            $data['color'] = 'success';
        }
        $data['local'] = $local;
        $smes = $session->get('mes_caja') ? $session->get('mes_caja') : date('m');
        $session->set('mes_caja', $smes);
        $data['menu']['p'] = 30;
        $data['menu']['i'] = 32;
        return view('operaciones/index_guiaentrada', $data);
    }

    public function ventas()
    {
        $session = session();
        if ($session->get('caja')) {
            $local = $session->get('caja');
            switch ($local) {
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
            $local = 1;
            $data['color'] = 'success';
        }
        $data['local'] = $local;
        $smes = $session->get('mes_caja') ? $session->get('mes_caja') : date('m');
        $session->set('mes_caja', $smes);
        $data['menu']['p'] = 30;
        $data['menu']['i'] = 33;
        return view('operaciones/index_ventas', $data);
    }

    public function receta()
    {
        $local = $this->request->getUri()->getSegment(3);
        $tipmov = $this->request->getUri()->getSegment(4);
        $numser = $this->request->getUri()->getSegment(5);
        $numfac = $this->request->getUri()->getSegment(6);
        $fecha = $this->request->getUri()->getSegment(7);
        $facart = new FacartModel();
        $data['facart'] = $facart->get_comprobante($numser, $numfac, $tipmov, date('d/m/Y', strtotime($fecha)), $local);
        $data['local'] = $local;
        $data['numser'] = $numser;
        $data['numfac'] = $numfac;
        $data['tipmov'] = $tipmov;
        $data['path'] = (site_url('operaciones/receta_print/' . $local . '/' . $tipmov . '/' . $numser . '/' . $numfac . '/' . $fecha));
        return view('operaciones/index_upload', $data);
    }
    public function receta_print()
    {
        $local = $this->request->getUri()->getSegment(3);
        $tipmov = $this->request->getUri()->getSegment(4);
        $numser = $this->request->getUri()->getSegment(5);
        $numfac = $this->request->getUri()->getSegment(6);
        $fecha = $this->request->getUri()->getSegment(7);
        $facart = new FacartModel();
        $data['facart'] = $facart->get_comprobante($numser, $numfac, $tipmov, date('d/m/Y', strtotime($fecha)), $local);
        $param = array(
            'REC_LOCAL' => $local,
            'REC_CODCIA' => 25,
            'REC_NUMSER' => $numser,
            'REC_NUMFAC' => $numfac
        );
        $RecImg = new RecetaImagenModel();
        $data['imagenes'] = $RecImg->get_lista_imagenes($param);
        //var_export($data); die();
        return view('operaciones/index_print_receta', $data);
    }
    function subirimagen()
    {
        $local = $this->request->getVar('local');
        $serie = $this->request->getVar('serie');
        $bolet = $this->request->getVar('boleta');
        $tipmo = $this->request->getVar('tipmov');
        $dpa = $this->request->getVar('dpa');
        $dcant = $this->request->getVar('dcant');
        $date = date('dmYhis');
        $this->validate([
            'file' => 'uploaded[userfile]|max_size[userfile,100]'
                . '|mime_in[userfile,image/png,image/jpg,image/gif]'
                . '|ext_in[userfile,png,jpg,gif]|max_dims[userfile,1024,768]',
        ]);

        $file = $this->request->getFile('file');

        if (! $path = $file->store()) {
            return ['error' => 'upload failed'];
        }

        \Config\Services::image()
            ->withFile(WRITEPATH . 'uploads\\' . $path)
            ->resize(800, 800, true, 'width')
            ->save(WRITEPATH . 'uploads\\' . $path);

        $dato = array(
            'REC_LOCAL' => $local,
            'REC_CODCIA' => 25,
            'REC_NUMSER' => $serie,
            'REC_NUMFAC' => $bolet,
            'REC_TIPMOV' => $tipmo,
            'REC_CODART' => '',
            'REC_PA' => $dpa,
            'REC_CANTIDAD' => $dcant,
            'REC_IMG' => $path
        );
        $RecImg = new RecetaImagenModel();
        $output = $RecImg->insertar_imagen($dato);
        // Response
        $rpta['success'] = 1;
        $rpta['message'] = 'Uploaded Successfully!';

        return $this->response->setJSON($rpta);
    }


    public function listar_imagenes()
    {
        $data = array(
            'REC_LOCAL' => $this->request->getPost('local'),
            'REC_CODCIA' => 25,
            'REC_NUMSER' => $this->request->getPost('serie'),
            'REC_NUMFAC' => $this->request->getPost('bolet')
        );

        $RecImg = new RecetaImagenModel();
        $imagenes = $RecImg->get_lista_imagenes($data);
        //var_export($imagenes);
        return $this->response->setJSON($imagenes);
    }

    public function get_operaciones()
    {
        $fecha = $this->request->getVar('fecha');
        $opera = $this->request->getVar('operacion');
        $factu = $this->request->getVar('factura');
        $porciones = explode("-", $fecha);
        $fecha01 = date('d/m/Y', strtotime($porciones[0]));
        $fecha02 = date('d/m/Y', strtotime($porciones[1]));

        $Allog_model = new AllogModel();
        $operaciones = $Allog_model->get_operaciones(0, 0, 0, $fecha01, $fecha02, $opera, $factu);
        //var_export($operaciones);
        return $this->response->setJSON($operaciones);
    }

    public function get_guias()
    {
        $session = session();
        $local = $session->get('caja');
        $request = service('request');
        $fecha = $request->getPost('fecha');
        $factu = $this->request->getVar('factura');
        $porciones = explode("-", $fecha);
        $fecha01 = date('d/m/Y', strtotime($porciones[0]));
        $fecha02 = date('d/m/Y', strtotime($porciones[1]));
        $opcion = $request->getPost('operacion');
        $Allog_model = new AllogModel();
        $operaciones = $Allog_model->get_lista_ventas($fecha01, $fecha02, $opcion, $factu, $local);
        //var_export($operaciones);
        return $this->response->setJSON($operaciones);
    }

    public function migrate_guias()
    {
        $request = service('request');
        $fecha = date('d/m/Y', strtotime($request->getPost('fecha')));
        $serie = trim($request->getPost('serie'));
        $factu = trim($request->getPost('factura'));
        $serve = trim($request->getPost('server'));
        $TxtLo = $serve == 3 ? 'PEÑAMEZA' : 'JUANJUICILLO';
        $serv03 = new Server03Model();
        $allog = $serv03->get_max_numfac($serve);
        $FAR_NUMFAC = intval($allog->FAR_NUMFAC);
        $FAR_NUMOPER = intval($allog->FAR_NUMOPER);
        $facart = new FacartModel();
        $guia = $facart->get_guia($fecha, $serie, $factu);
        $fec_hoy = date('d/m/Y');
        $comiT = array();
        $c = 0;
        foreach ($guia as $detalle) {
            $comi['FAR_TIPMOV'] = $all['ALL_TIPMOV'] = $act['TIPMOV'] = 6;
            $comi['FAR_CODCIA'] = $all['ALL_CODCIA'] = $act['CODCIA'] = "25";
            $comi['FAR_NUMSER'] = $all['ALL_NUMSER'] = $act['NUMSER'] = "1";
            $comi['FAR_FBG'] = ' ';
            $comi['FAR_NUMFAC'] = $all['ALL_NUMFAC'] = $act['NUMFAC'] = $FAR_NUMFAC + 1;
            $comi['FAR_NUMSEC'] = $detalle->FAR_NUMSEC;
            $comi['FAR_FECHA'] = $all['ALL_FECHA_DIA'] = $act['FECHA'] = $fec_hoy;
            $comi['FAR_NUMOPER'] = $all['ALL_NUMOPER'] = $act['NUMOPER'] = $FAR_NUMOPER + 1;
            $comi['FAR_CODCLIE'] = 0;
            $comi['FAR_CODART'] = $detalle->FAR_CODART;
            $comi['FAR_ESTADO'] = "N";
            $comi['FAR_DIAS'] = 0;
            $comi['FAR_SIGNO_ARM'] = $all['ALL_SIGNO_ARM'] = 1;
            $comi['FAR_PRECIO'] = $detalle->FAR_PRECIO;
            $comi['FAR_STOCK'] = 0; //CALCULAR DESPUES DE INGRESAR
            $comi['FAR_COSPRO'] = $detalle->FAR_COSPRO;
            $comi['FAR_IMPTO'] = 0;
            $comi['FAR_TOT_DESCTO'] = 0;
            $comi['FAR_DESCTO'] = 0;
            $comi['FAR_GASTOS'] = 0;
            $comi['FAR_BRUTO'] = $all['ALL_IMPORTE_AMORT'] = $all['ALL_NETO'] = $all['ALL_BRUTO'] = $detalle->FAR_BRUTO;
            $comi['FAR_EQUIV'] = $detalle->FAR_EQUIV;
            $comi['FAR_PORDESCTO1'] = 0;
            $comi['FAR_TIPO_CAMBIO'] = 0;
            $comi['FAR_NUMSER_C'] = $all['ALL_NUMSER_C'] = 0;
            $comi['FAR_NUMFAC_C'] = $all['ALL_NUMFAC_C'] = 0;
            $comi['FAR_NUMDOC'] = 0;
            $comi['FAR_LIMCRE_ANT'] = 0;
            $comi['FAR_LIMCRE_ACT'] = 0;
            $comi['FAR_KEY_DIRCLI'] = 0;
            $comi['FAR_PRECIO_NETO'] = 0;
            $comi['FAR_CODVEN'] = 0;
            $comi['FAR_UNIDADES'] = 0;
            $comi['FAR_LITRO'] = 0;
            $comi['FAR_FECHA_COMPRA'] = $all['ALL_FECHA_VCTO'] = $fec_hoy;
            $comi['FAR_NUM_LOTE'] = $detalle->FAR_NUM_LOTE;
            $comi['FAR_CANTIDAD'] = $detalle->FAR_CANTIDAD;
            $comi['FAR_SIGNO_LOT'] = 0;
            $comi['FAR_CONCEPTO'] = $all['ALL_CONCEPTO'] = "INGRESO A MEDINAFARMA - $TxtLo G/R " . $serie . "-" . $factu;
            $comi['FAR_COD_SUNAT'] = 3;
            $comi['FAR_DESCRI'] = $detalle->FAR_DESCRI;
            $comi['FAR_PESO'] = $detalle->FAR_PESO;
            $comi['FAR_EX_IGV'] = "A";
            $comi['FAR_NUM_PRECIO'] = 0;
            $comi['FAR_SUBTRA'] = $all['ALL_SUBTRA'] = "MIGRACION WEB";
            $comi['FAR_CODUSU'] = $all['ALL_CODUSU'] = "WEB";
            $comi['FAR_COSPRO_ANT'] = $detalle->FAR_COSPRO_ANT;
            $comi['FAR_HORA'] = $detalle->FAR_HORA;
            $comi['FAR_CANTIDAD_P'] = $detalle->FAR_CANTIDAD_P;
            $comi['FAR_TURNO'] = $detalle->FAR_TURNO;
            $comi['FAR_ESTADO2'] = "N";
            $comi['FAR_FLAG_SO'] = "X";
            $comi['FAR_NUMOPER2'] = $all['ALL_NUMOPER2'] = $FAR_NUMOPER + 1;
            $comi['FAR_FECHA_PRO'] = $all['ALL_FECHA_PRO'] = $all['ALL_FECHA_SUNAT'] = $fec_hoy;
            $comi['FAR_FECHA_CAN'] = $all['ALL_FECHA_CAN'] = $fec_hoy;
            $comi['FAR_SUBTOTAL'] = $detalle->FAR_SUBTOTAL;
            $comi['FAR_CODLOT'] = "(*)";
            $comi['FAR_TRANSITO'] = " ";
            $all['ALL_CODTRA'] = $all['ALL_CODTRA_EXT'] = 2403;
            $all['ALL_CHESER'] = 'i_c';
            $all['ALL_CODSUNAT'] = 3;
            $all['ALL_CODUSU'] = 'ADMIN';
            $all['ALL_CANTIDAD'] = $c + $detalle->FAR_CANTIDAD;
            $all['ALL_AUTOCON'] = "MIGRACION WEB : INGRESO A MEDINAFARMA - $TxtLo G/R " . trim($serie) . "-" . $factu;
            array_push($comiT, $comi);
        }
        $new = $FAR_NUMFAC + 1;
        $allog2 = $serv03->crear_guia_allog($all, $serve);
        if ($allog2) {
            $facart = $serv03->crear_guia_facart($comiT, $serve);
            if ($facart > 0) {
                $serv03->actualiza_stock($act, $serve);
                $Allog_model = new AllogModel();
                $Allog_model->set_guia_migrada($fecha, $serie, $factu, $new);
            } else {
                $msn = "No se actualizo el stock";
            }
            $msn = "1_" . $new;
        } else {
            $msn = "No se ingreso la guia";
        }
        echo $msn;
    }

    public function get_operacion()
    {
        $session = session();
        $fecha = $this->request->getVar('fecha');
        $serie = $this->request->getVar('serie');
        $factu = $this->request->getVar('factura');
        $opera = $this->request->getVar('operacion');
        $local = $session->get('caja');
        $ArtiModel = new ArtiModel();
        $operacion = $ArtiModel->get_operacion($opera, 0, $serie, $factu, $fecha, $local);
        //var_export($operaciones);
        return $this->response->setJSON($operacion);
    }

    public function get_precios_edit()
    {
        $key = $this->request->getVar('key');

        $PreciosModel = new PreciosModel();
        $operacion = $PreciosModel->get_precios_edit($key);
        //var_export($operaciones);
        //echo json_encode($operacion);
        return $this->response->setJSON($operacion);
    }
    public function set_precios()
    {
        try {
            // 🔍 Validar que los datos existan
            $codi = $this->request->getVar('codi');
            $dat1 = $this->request->getVar('dat1');
            $dat2 = $this->request->getVar('dat2');

            // Validación básica
            if (empty($codi)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Código de artículo requerido'
                ]);
            }

            if (empty($dat1) && empty($dat2)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Datos de precios requeridos'
                ]);
            }

            $PreciosModel = new PreciosModel();
            $resultados = [];
            $errores = [];

            // 📊 Procesar primera secuencia (dat1)
            if (!empty($dat1)) {
                parse_str($dat1, $datos1);

                // Validar que existan los campos requeridos
                $campos_requeridos = ['PRE_POR1_1', 'PRE_PRE1_1'];
                $datos_validos = true;

                foreach ($campos_requeridos as $campo) {
                    if (!isset($datos1[$campo])) {
                        $datos_validos = false;
                        break;
                    }
                }

                if ($datos_validos) {
                    $resultado1 = $PreciosModel->set_precios(
                        $codi,
                        0,
                        $datos1['PRE_POR1_1'] ?? 0,
                        $datos1['PRE_POR2_1'] ?? 0,
                        $datos1['PRE_POR3_1'] ?? 0,
                        $datos1['PRE_POR4_1'] ?? 0,
                        $datos1['PRE_POR5_1'] ?? 0,
                        $datos1['PRE_PRE1_1'] ?? 0,
                        $datos1['PRE_PRE2_1'] ?? 0,
                        $datos1['PRE_PRE3_1'] ?? 0,
                        $datos1['PRE_PRE4_1'] ?? 0,
                        $datos1['PRE_PRE5_1'] ?? 0
                    );

                    if ($resultado1['success']) {
                        $resultados[] = 'Secuencia 1 actualizada';
                    } else {
                        $errores[] = 'Error en secuencia 1: ' . $resultado1['message'];
                    }
                }
            }

            // 📊 Procesar segunda secuencia (dat2)
            if (!empty($dat2)) {
                parse_str($dat2, $datos2);

                // Solo actualizar si PRE_PRE1_2 > 0
                if (isset($datos2['PRE_PRE1_2']) && $datos2['PRE_PRE1_2'] > 0) {
                    $resultado2 = $PreciosModel->set_precios(
                        $codi,
                        1,
                        $datos2['PRE_POR1_2'] ?? 0,
                        $datos2['PRE_POR2_2'] ?? 0,
                        $datos2['PRE_POR3_2'] ?? 0,
                        $datos2['PRE_POR4_2'] ?? 0,
                        $datos2['PRE_POR5_2'] ?? 0,
                        $datos2['PRE_PRE1_2'] ?? 0,
                        $datos2['PRE_PRE2_2'] ?? 0,
                        $datos2['PRE_PRE3_2'] ?? 0,
                        $datos2['PRE_PRE4_2'] ?? 0,
                        $datos2['PRE_PRE5_2'] ?? 0
                    );

                    if ($resultado2['success']) {
                        $resultados[] = 'Secuencia 2 actualizada';
                    } else {
                        $errores[] = 'Error en secuencia 2: ' . $resultado2['message'];
                    }
                }
            }

            // 📤 Preparar respuesta JSON
            if (empty($errores)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Precios actualizados correctamente',
                    'details' => $resultados,
                    'codigo' => $codi
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Errores al actualizar precios',
                    'errors' => $errores,
                    'successes' => $resultados
                ]);
            }
        } catch (Exception $e) {
            // 📝 Log del error para debugging
            log_message('error', 'Error en set_precios: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error_detail' => $e->getMessage()
            ]);
        }
    }
    public function set_costo()
    {
        $key = $this->request->getVar('key');
        $costo = $this->request->getVar('costo');
        $ArticuloModel = new ArticuloModel();
        $operacion = $ArticuloModel->set_costo($key, $costo);
        return $operacion;
    }

    public function migrar_guia_optimizado()
    {
        // Obtener parámetros del request
        $servidor_origen = $this->request->getPost('servidor_origen');
        $servidor_destino = $this->request->getPost('servidor_destino');
        $fecha_origen = $this->request->getPost('fecha_origen');
        $serie_origen = $this->request->getPost('serie_origen');
        $factura_origen = $this->request->getPost('factura_origen');

        // Validar parámetros requeridos
        if (empty($servidor_origen) || empty($servidor_destino) || empty($fecha_origen)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Faltan parámetros requeridos'
            ]);
        }

        // Cargar el modelo
        $facartModel = new FacartModel();

        // Llamar al método del modelo
        $result = $facartModel->migrar_guia(
            $servidor_origen,
            $servidor_destino,
            $fecha_origen,
            $serie_origen,
            $factura_origen
        );

        // Devolver respuesta JSON
        return $this->response->setJSON($result);
    }

    public function eliminar_item()
    {
        // Obtener parámetros del request
        $fecha = $this->request->getPost('fecha');
        $numero_factura = $this->request->getPost('numero_factura');
        $codigo_articulo = $this->request->getPost('codigo_articulo');
        $server = $this->request->getPost('server');

        // Validar parámetros requeridos
        if (
            empty($fecha) || empty($numero_factura) ||
            empty($codigo_articulo) || empty($server)
        ) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Faltan parámetros requeridos'
            ]);
        }

        // Validar servidor válido (1, 2 o 3)
        if (!in_array($server, [1, 2, 3])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Servidor inválido. Use 1, 2 o 3'
            ]);
        }

        // Cargar el modelo
        $facartModel = new FacartModel();

        // Llamar al método del modelo
        $result = $facartModel->eliminar_item_facart(
            $fecha,
            $numero_factura,
            $codigo_articulo,
            $server
        );

        // Devolver respuesta JSON
        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Item eliminado correctamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar el item'
            ]);
        }
    }
    public function aplicarPrecios()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Acceso no permitido'
            ]);
        }

        $porcentaje = (float)$this->request->getPost('porcentaje');
        
        // Validación
        if ($porcentaje <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El porcentaje debe ser mayor que cero'
            ]);
        }

        try {
            $preciosModel = new PreciosModel();
            $resultado = $preciosModel->aplicarPreciosTemporales($porcentaje);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => sprintf(
                    'Precios actualizados con éxito. %d registros modificados con %.2f%% de ganancia.',
                    $resultado['filas_afectadas'],
                    $resultado['porcentaje']
                )
            ]);
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al procesar la solicitud'
            ]);
        }
    }

    /**
     * Procesa la eliminación de precios temporales (AJAX)
     */
    public function eliminarPrecios()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Acceso no permitido'
            ]);
        }

        try {
            $preciosModel = new PreciosModel();
            $resultado = $preciosModel->eliminarPreciosTemporales();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => sprintf(
                    'Precios temporales eliminados. %d registros modificados.',
                    $resultado['filas_afectadas']
                )
            ]);
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al eliminar los precios temporales'
            ]);
        }
    }
}
