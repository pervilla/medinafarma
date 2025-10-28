<?php

namespace App\Controllers;

use App\Models\DigemidErroresModel;
use CodeIgniter\Controller;

class DigemidErrores extends Controller
{
    protected $erroresModel;

    public function __construct()
    {
        $this->erroresModel = new DigemidErroresModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Gestión Errores DIGEMID',
            'menu' => ['p' => 50, 'i' => 60]
        ];
        
        return view('digemid/errores', $data);
    }

    public function procesarCsv()
    {
        $archivo = $this->request->getFile('archivo_csv');
        
        if (!$archivo->isValid()) {
            return $this->response->setJSON(['error' => 'Archivo no válido']);
        }

        $contenido = file_get_contents($archivo->getTempName());
        $lineas = explode("\n", $contenido);
        $procesados = 0;
        
        foreach ($lineas as $linea) {
            if (empty(trim($linea)) || strpos($linea, 'CodEstab;') === 0) continue;
            
            $datos = str_getcsv($linea, ';');
            if (count($datos) >= 5) {
                $codProd = $datos[1];
                $observacion = $datos[4];
                
                $this->erroresModel->marcarError($codProd, $observacion);
                $procesados++;
            }
        }
        
        return $this->response->setJSON(['success' => true, 'procesados' => $procesados]);
    }

    public function listarErrores()
    {
        $errores = $this->erroresModel->obtenerErrores();
        return $this->response->setJSON($errores);
    }

    public function sinRelacionar()
    {
        $productos = $this->erroresModel->obtenerSinRelacionar();
        return $this->response->setJSON($productos);
    }

    public function actualizarEstado()
    {
        $codProd = $this->request->getPost('cod_prod');
        $estado = $this->request->getPost('estado');
        $observacion = $this->request->getPost('observacion');
        
        $resultado = $this->erroresModel->actualizarEstado($codProd, $estado, $observacion);
        return $this->response->setJSON(['success' => $resultado]);
    }
}