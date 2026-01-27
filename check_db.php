<?php
// Simple script to check table existence without OFFSET issues
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require __DIR__ . '/app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
exit(CodeIgniter\Boot::bootWeb($paths));

use Config\Database;

$db = Database::connect();
$exists = $db->tableExists('planilla_descuentos');

echo "Table planilla_descuentos exists: " . ($exists ? 'YES' : 'NO') . "\n";

if ($exists) {
    // Try simple count
    try {
        $count = $db->table('planilla_descuentos')->countAllResults();
        echo "Row count: " . $count . "\n";
    } catch (\Throwable $e) {
        echo "Count failed: " . $e->getMessage() . "\n";
    }
}
