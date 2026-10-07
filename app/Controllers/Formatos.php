<?php

namespace App\Controllers;

use App\Models\VemaestModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class Formatos extends BaseController
{
    private $locales = ['CENTRO', 'JUANJUICILLO', 'PEÑAMEZA'];

    private $actividadesLimpieza = [
        'Limpieza de Pisos',
        'Limpieza de Mostrador',
        'Limpieza de Anaqueles',
        'Limpieza del Escritorio',
        'Botar la Basura',
        'Limpieza de Extintores',
        'Limpieza Computadoras',
        'Limp. Luces Emergencia',
        'Limpieza de Paredes',
        'Limpieza de Techos',
        'Limpieza de Ventilador',
        'Limpieza de Luminarias',
    ];

    private $frecuenciasLimpieza = [
        '1.- Diaria'  => '1,2,3,4,5',
        '2.- Semanal' => '6,7,8',
        '3.- Mensual' => '9,10,11,12',
    ];

    private $meses = [
        1  => 'ENERO',
        2  => 'FEBRERO',
        3  => 'MARZO',
        4  => 'ABRIL',
        5  => 'MAYO',
        6  => 'JUNIO',
        7  => 'JULIO',
        8  => 'AGOSTO',
        9  => 'SEPTIEMBRE',
        10 => 'OCTUBRE',
        11 => 'NOVIEMBRE',
        12 => 'DICIEMBRE',
    ];

    public function index()
    {
        $data['menu']['p'] = 85;
        $data['menu']['i'] = 86;
        $data['meses'] = $this->meses;
        $data['ambientes'] = ['ALMACÉN', 'VENTAS'];
        $data['locales'] = $this->locales;
        $data['anio_actual'] = (int) date('Y');
        $data['mes_actual'] = (int) date('n');

        $Emp = new VemaestModel();
        $data['empleados'] = $Emp->get_empleado('');

        return view('formatos/index', $data);
    }

    public function temperatura()
    {
        $mes  = (int) $this->request->getGet('mes');
        $anio = (int) $this->request->getGet('anio');
        $area = trim((string) $this->request->getGet('area'));
        $codInst = trim((string) $this->request->getGet('cod_inst'));

        if ($mes < 1 || $mes > 12) {
            $mes = (int) date('n');
        }
        if ($anio < 2000 || $anio > 2100) {
            $anio = (int) date('Y');
        }

        $data = [
            'mes'       => $mes,
            'anio'      => $anio,
            'mesNombre' => $this->meses[$mes],
            'dias'      => (int) date('t', mktime(0, 0, 0, $mes, 1, $anio)),
            'area'      => $area,
            'codInst'   => $codInst,
        ];

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('formatos/pdf/temperatura', $data));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombre = 'Formato_Temperatura_' . $data['mesNombre'] . '_' . $anio . '.pdf';
        $dompdf->stream($nombre, ['Attachment' => false]);
    }

    public function asistencia()
    {
        $mes   = (int) $this->request->getPost('mes');
        $anio  = (int) $this->request->getPost('anio');
        $local = trim((string) $this->request->getPost('local'));
        $seleccionados = $this->request->getPost('empleados');

        if ($mes < 1 || $mes > 12) {
            $mes = (int) date('n');
        }
        if ($anio < 2000 || $anio > 2100) {
            $anio = (int) date('Y');
        }

        if (!is_array($seleccionados) || empty($seleccionados)) {
            session()->setFlashdata('error', 'Seleccione al menos un empleado para generar el formato.');
            return redirect()->to(site_url('formatos'));
        }

        $Emp = new VemaestModel();
        $todos = $Emp->get_empleado('');
        $mapa = [];
        foreach ($todos as $emp) {
            $mapa[(string) $emp->VEM_CODVEN] = trim($emp->VEM_NOMBRE);
        }

        $empleados = [];
        foreach ($seleccionados as $cod) {
            if (isset($mapa[(string) $cod])) {
                $empleados[] = [
                    'codven' => (string) $cod,
                    'nombre' => $mapa[(string) $cod],
                ];
            }
        }

        if (empty($empleados)) {
            session()->setFlashdata('error', 'No se encontraron empleados válidos para el formato.');
            return redirect()->to(site_url('formatos'));
        }

        $data = [
            'mes'       => $mes,
            'anio'      => $anio,
            'mesNombre' => $this->meses[$mes],
            'dias'      => (int) date('t', mktime(0, 0, 0, $mes, 1, $anio)),
            'local'     => $local,
            'empleados' => $empleados,
        ];

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('formatos/pdf/asistencia', $data));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombre = 'Formato_Asistencia_' . $data['mesNombre'] . '_' . $anio . '.pdf';
        $dompdf->stream($nombre, ['Attachment' => false]);
    }

    public function limpieza()
    {
        $mes  = (int) $this->request->getGet('mes');
        $anio = (int) $this->request->getGet('anio');

        if ($mes < 1 || $mes > 12) {
            $mes = (int) date('n');
        }
        if ($anio < 2000 || $anio > 2100) {
            $anio = (int) date('Y');
        }

        $data = [
            'mes'         => $mes,
            'anio'        => $anio,
            'mesNombre'   => $this->meses[$mes],
            'dias'        => (int) date('t', mktime(0, 0, 0, $mes, 1, $anio)),
            'actividades' => $this->actividadesLimpieza,
            'frecuencias' => $this->frecuenciasLimpieza,
        ];

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('formatos/pdf/limpieza', $data));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombre = 'Formato_Limpieza_' . $data['mesNombre'] . '_' . $anio . '.pdf';
        $dompdf->stream($nombre, ['Attachment' => false]);
    }
}
