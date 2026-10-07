<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Impresoras extends BaseConfig
{
    // Nombres de locales
    public $locales = [
        1 => 'CENTRO',
        2 => 'JUANJUICILLO',
        3 => 'PEÑAMEZA',
        4 => 'CONSULTORIO',
    ];

    // Ticketeras (WindowsPrintConnector) por local
    public $ticketeras = [
        1 => 'smb://asesor:159357@ventas2/6-EPSON TM-T20IV Receipt',
        2 => 'smb://asesor:159357@server02/6-EPSON TM-T20II Receipt',
        3 => 'smb://asesor:159357@medinaimpresora/6-EPSON TM-T20II Receipt5',
        4 => 'smb://asesor:159357@vitalia/EPSON TM-T20III Receipt',
    ];

    // Identidad / marca por local (logo y nombre comercial para impresión)
    public $marcas = [
        1 => ['nombre' => 'INVERSIONES SAN MARTIN S.C.R.L.', 'logo' => 'medinafarma-black.jpg'],
        2 => ['nombre' => 'INVERSIONES SAN MARTIN S.C.R.L.', 'logo' => 'medinafarma-black.jpg'],
        3 => ['nombre' => 'INVERSIONES SAN MARTIN S.C.R.L.', 'logo' => 'medinafarma-black.jpg'],
        4 => ['nombre' => 'VITALIA',                          'logo' => 'vitalia-black.jpg'],
    ];
}
