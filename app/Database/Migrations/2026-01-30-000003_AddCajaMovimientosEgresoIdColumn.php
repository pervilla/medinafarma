<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCajaMovimientosEgresoIdColumn extends Migration
{
    /**
     * List of server connections
     */
    private $connections = [
        'default' => 'default',
        'juanjuicillo' => 'juanjuicillo',
        'pmeza' => 'pmeza'
    ];

    public function up()
    {
        foreach ($this->connections as $name => $group) {
            $db = \Config\Database::connect($group);
            
            // Check if column already exists
            $tableName = 'CAJA_MOVIMIENTOS';
            $fields = $db->getFieldNames($tableName);
            
            if (!in_array('CMV_EGRESO_ID', $fields)) {
                $this->forge = \Config\Database::forge($group);
                
                $this->forge->addColumn($tableName, [
                    'CMV_EGRESO_ID' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => true,
                        'after' => 'CMV_MONTO',
                        'comment' => 'Referencia al egreso creado en EGRESOS.EGR_ID'
                    ]
                ]);
                
                echo "Added CMV_EGRESO_ID column to {$tableName} on {$name} server\n";
            } else {
                echo "Column CMV_EGRESO_ID already exists in {$tableName} on {$name} server\n";
            }
        }
    }

    public function down()
    {
        foreach ($this->connections as $name => $group) {
            $db = \Config\Database::connect($group);
            $tableName = 'CAJA_MOVIMIENTOS';
            $fields = $db->getFieldNames($tableName);
            
            if (in_array('CMV_EGRESO_ID', $fields)) {
                $this->forge = \Config\Database::forge($group);
                $this->forge->dropColumn($tableName, 'CMV_EGRESO_ID');
                
                echo "Dropped CMV_EGRESO_ID column from {$tableName} on {$name} server\n";
            } else {
                echo "Column CMV_EGRESO_ID does not exist in {$tableName} on {$name} server\n";
            }
        }
    }
}