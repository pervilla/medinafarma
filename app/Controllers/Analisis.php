<?php

namespace App\Controllers;
use App\Models\FacartModel;

use App\Controllers\BaseController;

class Analisis extends BaseController
{
    public function index()
    {
        //
    }
    public function compras(){
        $data['menu']['p'] = 70;
        $data['menu']['i'] = 71;
        return view('analisis/index_compras',$data);
    }

    public function get_compras(){
        $CODCLIE = $this->request->getVar('codcli');
        $CODART = $this->request->getVar('art');
        $this->Facart_model = new FacartModel();
        $compras = $this->Facart_model->get_compras($CODCLIE, $CODART);
        return $this->response->setJSON($compras);
    }
    public function get_cuadro(){
        $CODART = $this->request->getVar('art');
        $this->Facart_model = new FacartModel();
        $compras = $this->Facart_model->get_ing_sal($CODART);
        return $this->response->setJSON($compras);
    }
}
