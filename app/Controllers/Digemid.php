<?php

namespace App\Controllers;

use App\Models\DigemidModel;
use CodeIgniter\Controller;

class Digemid extends Controller
{
    protected $digemidModel;
    protected $session;

    public function __construct()
    {
        $this->digemidModel = new DigemidModel();
        $this->session = \Config\Services::session();
    }

    public function index()
    {
        $data = [
            'title' => 'Generar Archivo DIGEMID',
            'menu' => ['p' => 50, 'i' => 58]
        ];
        
        return view('digemid/index', $data);
    }

    public function generar()
    {
        $codEstab = $this->request->getPost('cod_estab');
        $mes = $this->request->getPost('mes');
        $anio = $this->request->getPost('anio');
        
        if (!$codEstab || !$mes || !$anio) {
            return $this->response->setJSON(['error' => 'Faltan parámetros requeridos']);
        }

        try {
            // Obtener datos según el establecimiento
            $datos = $this->digemidModel->obtenerPrecios($codEstab);
            
            if (empty($datos)) {
                return $this->response->setJSON(['error' => 'No se encontraron datos']);
            }

            // Generar nombre del archivo
            $ruc = '20450337839';
            $nombreArchivo = "{$ruc}_{$codEstab}_{$mes}_{$anio}";
            
            // Crear CSV
            $csvContent = "CodEstab;CodProd;Precio 1;Precio 2\n";
            foreach ($datos as $fila) {
                $csvContent .= "{$fila->CodEstab};{$fila->CodProd};{$fila->Precio1};{$fila->Precio2}\n";
            }
            
            // Crear directorio temporal
            $tempDir = WRITEPATH . 'uploads/temp/';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $csvFile = $tempDir . $nombreArchivo . '.csv';
            $zipFile = $tempDir . $nombreArchivo . '.zip';
            
            // Escribir CSV
            file_put_contents($csvFile, $csvContent);
            
            // Crear ZIP
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
                $zip->addFile($csvFile, $nombreArchivo . '.csv');
                $zip->close();
                
                // Eliminar CSV temporal
                unlink($csvFile);
                
                return $this->response->setJSON([
                    'success' => true,
                    'archivo' => $nombreArchivo . '.zip',
                    'registros' => count($datos)
                ]);
            } else {
                return $this->response->setJSON(['error' => 'Error al crear el archivo ZIP']);
            }
            
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function descargar($archivo)
    {
        $filePath = WRITEPATH . 'uploads/temp/' . $archivo;
        
        if (file_exists($filePath)) {
            return $this->response->download($filePath, null)->setFileName($archivo);
        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }
}