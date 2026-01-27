<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanillaDefaultRuleSeeder extends Seeder
{
    public function run()
    {
        // Check if exists
        $exists = $this->db->table('planilla_reglas_comision')->where('tipo', 'DEFAULT')->countAllResults();
        
        if ($exists == 0) {
            $this->db->table('planilla_reglas_comision')->insert([
                'tipo'        => 'DEFAULT',
                'porcentaje'  => 0.00,
                'descripcion' => 'Comisión por Defecto para productos sin regla',
                'created_at'  => date('Ymd H:i:s')
            ]);
        }
    }
}
