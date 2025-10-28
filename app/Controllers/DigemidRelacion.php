<?php

namespace App\Controllers;

use App\Models\DigemidRelacionModel;
use CodeIgniter\Controller;

class DigemidRelacion extends Controller
{
    protected $relacionModel;

    public function __construct()
    {
        $this->relacionModel = new DigemidRelacionModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Relación Productos DIGEMID',
            'menu' => ['p' => 50, 'i' => 59]
        ];
        
        return view('digemid/relacion', $data);
    }

    public function buscar()
    {
        $termino = $this->request->getPost('termino');
        $productos = $this->relacionModel->buscarProductosDigemid($termino);
        return $this->response->setJSON($productos);
    }

    public function buscarArticulos()
    {
        $termino = $this->request->getPost('termino');
        $articulos = $this->relacionModel->buscarArticulos($termino);
        return $this->response->setJSON($articulos);
    }

    public function relacionar()
    {
        $codProd = $this->request->getPost('cod_prod');
        $preCodeart = $this->request->getPost('pre_codeart');
        
        $resultado = $this->relacionModel->crearRelacion($codProd, $preCodeart);
        return $this->response->setJSON(['success' => $resultado]);
    }

    public function eliminar()
    {
        $codProd = $this->request->getPost('cod_prod');
        $resultado = $this->relacionModel->eliminarRelacion($codProd);
        return $this->response->setJSON(['success' => $resultado]);
    }

    public function relacionados()
    {
        $relacionados = $this->relacionModel->obtenerRelacionados();
        return $this->response->setJSON($relacionados);
    }

    public function sinRelacionarLimitado()
    {
        $productos = $this->relacionModel->obtenerSinRelacionarLimitado();
        return $this->response->setJSON($productos);
    }
}