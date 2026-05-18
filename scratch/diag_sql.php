<?php
// Script de diagnóstico rápido
try {
    $db = \Config\Database::connect();
    
    echo "Verificando tablas...\n";
    $tables = ['PRECIOS_DIGEMID', 'PRECIOS_DET_DIGEMID_MEDINA'];
    foreach ($tables as $table) {
        $q = $db->query("SELECT TOP 1 * FROM $table");
        if ($q) {
            echo "TABLA $table: OK. Columnas: " . implode(', ', array_keys($q->getRowArray())) . "\n";
        } else {
            echo "TABLA $table: ERROR o NO EXISTE.\n";
        }
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
