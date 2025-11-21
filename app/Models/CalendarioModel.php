<?php

namespace App\Models;

use CodeIgniter\Model;

class CalendarioModel extends Model
{
    protected $db;
    protected $dbpm;
    protected $dbjj;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->dbpm = \Config\Database::connect('pmeza');
        $this->dbjj = \Config\Database::connect('juanjuicillo');
    }

    public function generar_calendario($anio, $server, $codigo_cia = '25')
    {
        // Call the stored procedure directly
        // The SP returns a message in the OUTPUT parameter with format:
        // '1_Success message' or '0_Error message'
        
        try {
            // Build the SQL to call the stored procedure
            // We'll use a simpler approach: call the SP and let it do its work
            // Then query the result from a status table or use a different approach
            
            // Actually, let's use the SP's OUTPUT parameter properly
            // We need to use sqlsrv functions directly if available, or a workaround
            
            // For CodeIgniter with SQL Server, let's try a direct EXEC without the SELECT
            $sql = "DECLARE @msg VARCHAR(500);
                    EXEC sp_generar_calendario_remoto 
                        @servidor_destino = ?, 
                        @anio = ?, 
                        @codigo_cia = ?, 
                        @mensaje_salida = @msg OUTPUT;
                    SELECT @msg as mensaje;";

            log_message('info', "Executing sp_generar_calendario_remoto with params: server=$server, anio=$anio, cia=$codigo_cia");
            
            $query = $this->db->query($sql, [$server, $anio, $codigo_cia]);

            if ($query === false) {
                $error = $this->db->error();
                log_message('error', 'sp_generar_calendario_remoto query failed: Code=' . $error['code'] . ', Message=' . $error['message']);
                return 'Error de base de datos: ' . $error['message'];
            }

            // Try to get the result
            $result = $query->getRow();
            
            if ($result && isset($result->mensaje)) {
                log_message('info', 'sp_generar_calendario_remoto returned: ' . $result->mensaje);
                return $result->mensaje;
            } else {
                log_message('error', 'sp_generar_calendario_remoto: No result or mensaje field missing');
                return 'Error: El procedimiento no retornó un mensaje válido.';
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Exception executing sp_generar_calendario_remoto: ' . $e->getMessage());
            return 'Error de ejecución: ' . $e->getMessage();
        }
    }

    public function cerrar_dia($server, $codigo_cia = '25')
    {
        // Call the stored procedure sp_actualizar_dia_habil_remoto
        // This SP uses GETDATE() internally to determine the current date
        // and updates both CALENDARIO and PARGEN tables
        
        try {
            $sql = "DECLARE @msg VARCHAR(500);
                    EXEC sp_actualizar_dia_habil_remoto 
                        @servidor_destino = ?, 
                        @codigo_cia = ?, 
                        @mensaje_salida = @msg OUTPUT;
                    SELECT @msg as mensaje;";

            log_message('info', "Executing sp_actualizar_dia_habil_remoto with params: server=$server, cia=$codigo_cia");
            
            $query = $this->db->query($sql, [$server, $codigo_cia]);

            if ($query === false) {
                $error = $this->db->error();
                log_message('error', 'sp_actualizar_dia_habil_remoto query failed: Code=' . $error['code'] . ', Message=' . $error['message']);
                return 'Error de base de datos: ' . $error['message'];
            }

            // Try to get the result
            $result = $query->getRow();
            
            if ($result && isset($result->mensaje)) {
                log_message('info', 'sp_actualizar_dia_habil_remoto returned: ' . $result->mensaje);
                return $result->mensaje;
            } else {
                log_message('error', 'sp_actualizar_dia_habil_remoto: No result or mensaje field missing');
                return 'Error: El procedimiento no retornó un mensaje válido.';
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Exception executing sp_actualizar_dia_habil_remoto: ' . $e->getMessage());
            return 'Error de ejecución: ' . $e->getMessage();
        }
    }
}
