<?php

namespace App\Controllers;
use App\Models\RequerimientoModel;
use App\Models\ArticuloModel;
use App\Controllers\BaseController;

class Requerimiento extends BaseController
{
    public function index()
    {
        $data['menu']['p'] = 50;
        $data['menu']['i'] = 51;
        return view('requerimiento/index',$data);
    }
    public function listado($local)
    {
        $data['local']=$local;
        return view('requerimiento/index_listado',$data);
    }
    public function agregar(){
        $data = array(
            'REQ_CODART' => $this->request->getVar('codart'),
            'REQ_LOCAL' => $this->request->getVar('local')
        );
        $Req = new RequerimientoModel();
        $cant = $Req->existe($data);
        
        if($cant==0){
            $a = $Req->agregar($data)?'1|Producto Registrado':'0|No se registro el producto';
        }else{
            $a = '0|Ya existe el producto';
        };
        return $a;
    }
    public function listaReq(){
        $local = $this->request->getVar('local');
        $Req = new RequerimientoModel();
        $movimientos = $Req->listar($local);
        return $this->response->setJSON($movimientos);
    }
    public function actualizaReq(){        
        $tram = $this->request->getVar('tram');
        $data = explode("-", $tram); /* und-val-idprod-local */
        $Art = new ArticuloModel();
        $stkA = $Art->get_Stock($data[2]);        
        $stkR = $data[1]*$data[0];
        $a='';
        if($stkA->ARM_STOCK>=$data[1]){
            $ids = ['REQ_LOCAL'=>$data[3],'REQ_CODART'=>$data[2]];
            $row = ['REQ_CANT'=>$stkR,'REQ_EQUIV'=>$data[0]]; //REQ_CANT,REQ_EQUIV
            $Req = new RequerimientoModel();
            $Req->actualizar($ids,$row);
            $a = '1|Actualizado Correctamente';
        }else{
            $a = '0|Stock Insuficiente. Solo hay '.$stkA->ARM_STOCK.' Unidades';
        }

        return $a;
    }
    public function crea_guia_requerimiento(){
        $local = $this->request->getVar('loc');
        $Req = new RequerimientoModel();
        $a = $Req->crea_guia_requerimiento($local);
        return $this->response->setJSON($a);
    }
    public function crea_guia_requerimiento_ING(){
        $local = $this->request->getVar('loc');
        $Req = new RequerimientoModel();
        $a = $Req->crea_guia_requerimiento_ING($local);
        return $this->response->setJSON($a);
    }     
}
