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
        $stats = $this->relacionModel->obtenerEstadisticas();
        $huerfanas = $this->relacionModel->contarHuerfanas();

        $data = [
            'title'     => 'Administrar Relación DIGEMID',
            'menu'      => ['p' => 50, 'i' => 59],
            'stats'     => $stats,
            'huerfanas' => $huerfanas,
        ];
        return view('digemid/relacion', $data);
    }

    // ── Búsquedas ──────────────────────────────────────

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

    // ── CRUD Relación ───────────────────────────────────

    public function relacionar()
    {
        try {
            $codProd    = $this->request->getPost('cod_prod');
            $preCodeart = $this->request->getPost('pre_codeart');
            
            if (!$codProd || !$preCodeart) {
                return $this->response->setJSON(['success' => false, 'message' => 'Faltan datos requeridos']);
            }

            $resultado  = $this->relacionModel->crearRelacion($codProd, $preCodeart);
            return $this->response->setJSON([
                'success' => $resultado, 
                'message' => $resultado ? 'Vínculo creado' : 'Error en el modelo al guardar'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function eliminar()
    {
        $codProd   = $this->request->getPost('cod_prod');
        $resultado = $this->relacionModel->eliminarRelacion($codProd);
        return $this->response->setJSON(['success' => $resultado]);
    }

    // ── Listados paginados ──────────────────────────────

    public function relacionados()
    {
        $busqueda  = $this->request->getPost('busqueda') ?? '';
        $pagina    = (int)($this->request->getPost('pagina') ?? 1);
        $porPagina = 50;

        $datos = $this->relacionModel->obtenerRelacionados($busqueda, $pagina, $porPagina);
        $total = $this->relacionModel->contarRelacionados($busqueda);

        return $this->response->setJSON([
            'datos'      => $datos,
            'total'      => $total,
            'pagina'     => $pagina,
            'porPagina'  => $porPagina,
            'totalPags'  => ceil($total / $porPagina),
        ]);
    }

    public function sinRelacionar()
    {
        $busqueda     = $this->request->getPost('busqueda');
        $pagina       = $this->request->getPost('pagina') ?: 1;
        $soloConStock = $this->request->getPost('solo_con_stock') == 'true';
        $porPagina    = 15;

        $datos = $this->relacionModel->obtenerSinRelacionar($busqueda, $pagina, $porPagina, $soloConStock);
        $total = $this->relacionModel->contarSinRelacionar($busqueda, $soloConStock);

        return $this->response->setJSON([
            'datos' => $datos,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPags' => ceil($total / $porPagina)
        ]);
    }

    // ── Huérfanas ───────────────────────────────────────

    public function huerfanas()
    {
        $datos = $this->relacionModel->obtenerHuerfanas();
        $total = count($datos);
        return $this->response->setJSON(['datos' => $datos, 'total' => $total]);
    }

    public function eliminarHuerfanas()
    {
        $eliminadas = $this->relacionModel->eliminarHuerfanas();
        if ($eliminadas >= 0) {
            return $this->response->setJSON(['success' => true, 'eliminadas' => $eliminadas]);
        }
        return $this->response->setJSON(['success' => false, 'error' => 'Error al eliminar']);
    }

    // ── Estadísticas ────────────────────────────────────

    public function estadisticas()
    {
        $stats    = $this->relacionModel->obtenerEstadisticas();
        $huerfanas = $this->relacionModel->contarHuerfanas();
        return $this->response->setJSON([
            'total_digemid'    => $stats->total_digemid,
            'total_relacionados' => $stats->total_relacionados,
            'sin_relacionar'   => $stats->total_digemid - $stats->total_relacionados,
            'huerfanas'        => $huerfanas,
        ]);
    }

    // Compatibilidad con código anterior
    public function sinRelacionarLimitado()
    {
        return $this->sinRelacionar();
    }
}
