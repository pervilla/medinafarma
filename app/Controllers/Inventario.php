<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ArticuloModel;
use App\Models\ArtiModel;
use App\Models\PreciosModel;
use App\Models\VemaestModel;
use App\Models\InventarioModel;
class Inventario extends BaseController
{
    public function index()
    {
        return redirect()->to('inventario/dashboard');
    }
    
    public function index_old()
    {
        $data['menu']['p'] = 50;
        $data['menu']['i'] = 57;
        $uri = $this->request->getUri();
        $data['id_local'] = 1;// $uri->getSegment(3)?$uri->getSegment(3):1;
        $Inv = new InventarioModel();
        $data['inventarios'] = $Inv->get_inventarios(1);   
        $data['responsables'] = $Inv->get_responsables_avn(1,0);           
        $Emp = new VemaestModel();
        $data['empleados'] = $Emp->get_empleado('');    
        
        return view('inventario/index_inventario_ct',$data);
    }
    public function lista($id_loc = 1, $id_inv = null, $id_ven = null, $to_reg = 0){
        $data['id_loc'] = $id_loc;
        $data['id_inv'] = $id_inv;
        $data['id_ven'] = $id_ven;
        $data['to_reg'] = $to_reg;
        
        // Si no hay vendedor, obtener el primero disponible
        if (!$id_ven && $id_inv) {
            $Inv = new InventarioModel();
            $responsables = $Inv->get_responsables($id_loc, $id_inv);
            if (!empty($responsables)) {
                $data['id_ven'] = $responsables[0]->vem_codven;
                $data['to_reg'] = $responsables[0]->inr_cantidad;
            }
        }
        
        return view('inventario/index_listado', $data);
    }
    
    public function lista_productos(){
        $id_loc = $this->request->getVar('idlocal')?$this->request->getVar('idlocal'):1;
        $id_inv = $this->request->getVar('id_inv');
        $id_ven = $this->request->getVar('id_ven');

        $Inv = new InventarioModel();
        $data = $Inv->get_productos($id_loc,$id_inv,$id_ven,0);
        return $this->response->setJSON($data);
    
    }
    public function producto(){
        $uri = $this->request->getUri();
        $data['id_loc'] = $id_loc = $uri->getSegment(3)?$uri->getSegment(3):1;
        $data['id_inv'] = $id_inv = $uri->getSegment(4);
        $data['id_ven'] = $id_ven = $uri->getSegment(5);
        $data['to_reg'] = $uri->getSegment(6);
        $pg_reg = $uri->getSegment(7);
        $Inv = new InventarioModel();
        $data['producto'] = $Inv->get_producto($id_loc,$id_inv,$id_ven,$pg_reg);
        return view('inventario/index_producto',$data);
    
    }
    public function crear_inventario(){
        $id_local = $this->request->getVar('idlocal');
        $data = $this->request->getVar('inv_descripcion');
        $Inv = new InventarioModel();
        $Inv->crear_inventario($id_local,$data);
        return redirect()->back();
    }
    public function listar_responsables(){
        $Inv = new InventarioModel();
        $inv_id = $this->request->getVar('inv_id');
        $data = $Inv->get_responsables(1, $inv_id);
        
        // Asegurar que siempre devuelva un array
        if (!$data) {
            $data = [];
        }
        
        return $this->response->setJSON($data);
    }
    public function agregar_responsables(){        
        $id_local = $this->request->getVar('invlc');
        $data['inv_id'] = $this->request->getVar('idinv');
        $data['vem_codven'] = $this->request->getVar('vem_codven');
        $Inv = new InventarioModel();
        $Inv->crear_responsable($id_local,$data);
        return "ok";
    }
    public function actualizaDistribucion(){
        $server = $this->request->getVar('inv_lc');
        $inv = $this->request->getVar('inv_id');
        $ven = $this->request->getVar('inv_cv');
        $prop = $this->request->getVar('inv_vl');
        $treg = $this->request->getVar('inv_ti');
        $Inv = new InventarioModel();
        $Inv->actualizaDistribucion($server,$inv,$ven,$treg,$prop);
        return "ok";
    }
    
    public function conteo($id_local = 1, $id_inv = null, $id_ven = null){
        $data['id_local'] = $id_local;
        $data['id_inv'] = $id_inv;
        $data['id_ven'] = $id_ven;
        $Inv = new InventarioModel();
        
        try {
            $data['productos'] = $Inv->get_productos($id_local, $id_inv, $id_ven, 0) ?: [];
            $data['responsable'] = $Inv->get_responsable_info($id_local, $id_ven);
        } catch (Exception $e) {
            $data['productos'] = [];
            $data['responsable'] = (object)['VEM_NOMBRE' => 'Usuario'];
        }
        
        return view('inventario/conteo', $data);
    }
    
    public function actualizar_conteo(){
        $id_local = $this->request->getVar('id_local');
        $art_key = $this->request->getVar('art_key');
        $inv_id = $this->request->getVar('inv_id');
        $vem_codven = $this->request->getVar('vem_codven');
        $stock_fisico = $this->request->getVar('stock_fisico');
        $observaciones = $this->request->getVar('observaciones') ?? '';
        
        // Validar parámetros requeridos
        if (empty($art_key)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Código de artículo requerido']);
        }
        
        if (!is_numeric($stock_fisico)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Stock físico inválido']);
        }
        
        try {
            $Inv = new InventarioModel();
            $result = $Inv->actualizar_conteo($id_local, $art_key, $stock_fisico, $observaciones, $inv_id, $vem_codven);
            return $this->response->setJSON(['success' => true, 'message' => 'Conteo actualizado']);
        } catch (Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }
    
    public function buscar_producto(){
        $id_local = $this->request->getVar('id_local');
        $id_inv = $this->request->getVar('id_inv');
        $id_ven = $this->request->getVar('id_ven');
        $busqueda = $this->request->getVar('busqueda');
        
        $Inv = new InventarioModel();
        $productos = $Inv->buscar_producto($id_local, $id_inv, $id_ven, $busqueda);
        
        return $this->response->setJSON($productos);
    }
    
    public function dashboard(){
        $data['menu']['p'] = 50;
        $data['menu']['i'] = 57;
        $id_local = 1;
        $Inv = new InventarioModel();
        $data['inventarios'] = $Inv->get_inventarios_dashboard($id_local);
        $Emp = new VemaestModel();
        $data['empleados'] = $Emp->get_empleado('');
        return view('inventario/dashboard', $data);
    }
    
    public function distribuir_automatico(){
        $id_local = $this->request->getVar('id_local');
        $inv_id = $this->request->getVar('inv_id');
        
        $Inv = new InventarioModel();
        $result = $Inv->distribuir_productos_automatico($id_local, $inv_id);
        
        return $this->response->setJSON(['success' => true, 'message' => 'Productos distribuidos automáticamente']);
    }
    
    public function cerrar_inventario(){
        $id_local = $this->request->getVar('id_local');
        $inv_id = $this->request->getVar('inv_id');
        
        try {
            $Inv = new InventarioModel();
            $result = $Inv->cerrar_inventario($id_local, $inv_id);
            return $this->response->setJSON(['success' => true, 'message' => 'Inventario cerrado correctamente']);
        } catch (Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al cerrar inventario: ' . $e->getMessage()]);
        }
    }
    
    public function generar_guias(){
        $id_local = $this->request->getVar('id_local');
        $inv_id = $this->request->getVar('inv_id');
        $tipo = $this->request->getVar('tipo'); // 'ingreso', 'salida', 'ambas'
        
        try {
            $Inv = new InventarioModel();
            $resultado = [];
            
            if ($tipo == 'ingreso' || $tipo == 'ambas') {
                $guia_ingreso = $Inv->generar_guia_ingreso($id_local, $inv_id);
                if ($guia_ingreso) {
                    $resultado['ingreso'] = "Guía de Ingreso: 1-{$guia_ingreso->numfac}";
                }
            }
            
            if ($tipo == 'salida' || $tipo == 'ambas') {
                $guia_salida = $Inv->generar_guia_salida($id_local, $inv_id);
                if ($guia_salida) {
                    $resultado['salida'] = "Guía de Salida: 1-{$guia_salida->numfac}";
                }
            }
            
            return $this->response->setJSON(['success' => true, 'guias' => $resultado]);
        } catch (Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al generar guías: ' . $e->getMessage()]);
        }
    }
}
