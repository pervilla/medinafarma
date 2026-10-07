<?php

if (!function_exists('logo_medinafarma_svg')) {
    /**
     * Devuelve el contenido SVG del logo de Medinafarma.
     * Fuente única: public/dist/img/logo-medinafarma.svg
     *
     * @return string
     */
    function logo_medinafarma_svg()
    {
        $path = FCPATH . 'dist/img/logo-medinafarma.svg';

        return is_file($path) ? file_get_contents($path) : '';
    }
}

if (!function_exists('logo_medinafarma')) {
    /**
     * Devuelve el logo listo para incrustar en HTML/PDF.
     *
     * @param bool $base64 true => data URI (recomendado para Dompdf)
     *
     * @return string
     */
    function logo_medinafarma($base64 = true)
    {
        $svg = logo_medinafarma_svg();

        if ($svg === '') {
            return '';
        }

        return $base64 ? 'data:image/svg+xml;base64,' . base64_encode($svg) : $svg;
    }
}
