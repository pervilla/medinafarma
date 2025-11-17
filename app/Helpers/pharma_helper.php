<?php

if (!function_exists('normalizar_producto')) {
    function normalizar_producto($texto) {
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = preg_replace('/[áàäâ]/u', 'a', $texto);
        $texto = preg_replace('/[éèëê]/u', 'e', $texto);
        $texto = preg_replace('/[íìïî]/u', 'i', $texto);
        $texto = preg_replace('/[óòöô]/u', 'o', $texto);
        $texto = preg_replace('/[úùüû]/u', 'u', $texto);
        $texto = preg_replace('/[^a-z0-9\s\.]/u', ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }
}

if (!function_exists('extraer_concentracion')) {
    function extraer_concentracion($texto) {
        $patron = '/(\d+(?:\.\d+)?)\s*(mg|g|ml|l|mcg|ui|%)/i';
        preg_match_all($patron, $texto, $matches);
        return !empty($matches[0]) ? implode(' ', $matches[0]) : '';
    }
}

if (!function_exists('extraer_forma_farmaceutica')) {
    function extraer_forma_farmaceutica($texto) {
        $config = config('AIMatching');
        $texto_norm = normalizar_producto($texto);
        
        foreach ($config->formasFarmaceuticas as $forma) {
            if (strpos($texto_norm, $forma) !== false) {
                return $forma;
            }
        }
        return '';
    }
}

if (!function_exists('similitud_levenshtein')) {
    function similitud_levenshtein($str1, $str2) {
        $str1 = normalizar_producto($str1);
        $str2 = normalizar_producto($str2);
        
        $len1 = mb_strlen($str1);
        $len2 = mb_strlen($str2);
        $maxLen = max($len1, $len2);
        
        if ($maxLen == 0) return 1.0;
        
        $distance = levenshtein($str1, $str2);
        return 1 - ($distance / $maxLen);
    }
}

if (!function_exists('similitud_jaccard')) {
    function similitud_jaccard($str1, $str2) {
        $words1 = array_unique(explode(' ', normalizar_producto($str1)));
        $words2 = array_unique(explode(' ', normalizar_producto($str2)));
        
        $intersection = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));
        
        return $union > 0 ? $intersection / $union : 0;
    }
}

if (!function_exists('calcular_score_matching')) {
    function calcular_score_matching($producto1, $producto2) {
        $nombre1 = $producto1['nombre'] ?? '';
        $nombre2 = $producto2['nombre'] ?? '';
        $conc1 = $producto1['concentracion'] ?? '';
        $conc2 = $producto2['concentracion'] ?? '';
        $forma1 = $producto1['forma_farmaceutica'] ?? '';
        $forma2 = $producto2['forma_farmaceutica'] ?? '';
        
        // Similitud de nombres (peso 50%)
        $simNombre = (similitud_levenshtein($nombre1, $nombre2) + similitud_jaccard($nombre1, $nombre2)) / 2;
        
        // Similitud de concentración (peso 30%)
        $simConc = 0;
        if (!empty($conc1) && !empty($conc2)) {
            $simConc = similitud_levenshtein($conc1, $conc2);
        }
        
        // Similitud de forma farmacéutica (peso 20%)
        $simForma = 0;
        if (!empty($forma1) && !empty($forma2)) {
            $simForma = $forma1 === $forma2 ? 1.0 : 0.5;
        }
        
        return ($simNombre * 0.5) + ($simConc * 0.3) + ($simForma * 0.2);
    }
}

if (!function_exists('aplicar_sinonimos')) {
    function aplicar_sinonimos($texto) {
        $config = config('AIMatching');
        $texto_norm = normalizar_producto($texto);
        
        foreach ($config->synonyms as $principal => $sinonimos) {
            foreach ($sinonimos as $sinonimo) {
                $texto_norm = str_replace($sinonimo, $principal, $texto_norm);
            }
        }
        
        return $texto_norm;
    }
}
