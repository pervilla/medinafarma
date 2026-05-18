<?php
require 'system/bootstrap.php';
$db = \Config\Database::connect();
$query = $db->query("SELECT TOP 1 * FROM PRECIOS_DIGEMID");
$row = $query->getRowArray();
print_r(array_keys($row));
