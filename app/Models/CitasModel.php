<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Description of CitasModel
 *
 * @author José Luis
 */
class CitasModel extends Model {

    var $table = 'citas';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct() {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    public function get_citas($camp,$estado=null){
        $sql = 'SELECT *, ';
        $sql.= "floor((cast(convert(varchar(8),getdate(),112) as int)-cast(convert(varchar(8),t2.CLI_FECHA_NAC,112) as int)) / 10000) as EDAD, ";
        $sql.= "(SELECT COUNT(CIT_CODCAMP) FROM dbo.CITAS AS T3 WHERE T1.CIT_CODCLIE=CIT_CODCLIE AND CIT_ESTADO>0 AND CIT_CODCAMP IN(SELECT CAM_CODCAMP FROM dbo.CAMPANIA WHERE CAM_ID=T4.CAM_ID)) AS ATENCIONES ";
        $sql.= 'FROM dbo.CITAS AS T1 ';
        $sql.= "INNER JOIN dbo.CLIENTES AS T2 ON(T1.CIT_CODCLIE=T2.CLI_CODCLIE AND CLI_CP = 'C' ) ";
        $sql.= "INNER JOIN dbo.CAMPANIA AS T4 ON(T4.CAM_CODCAMP=".$camp.")";
        $sql.= 'WHERE ';
        $sql.= 'CIT_CODCAMP = '.$camp;
        $sql.= is_null($estado)?' AND CIT_ESTADO IN(1,2,3)':'';
        
        //echo $sql; die();
        $query =  $this->db->query($sql);       
        
        return $query->getResult();
    }
    
    public function get_personas_citas($param){
        $sql = 'SELECT DISTINCT CLI_CODCLIE,CLI_NOMBRE,CLI_TELEF1,CLI_RUC_ESPOSA, CLI_FECHA_NAC,';
        $sql.= "floor((cast(convert(varchar(8),getdate(),112) as int)-cast(convert(varchar(8),t2.CLI_FECHA_NAC,112) as int)) / 10000) as EDAD ";
        $sql.= 'FROM dbo.CITAS AS T1 ';
        $sql.= "INNER JOIN dbo.CLIENTES AS T2 ON(T1.CIT_CODCLIE=T2.CLI_CODCLIE AND CLI_CP = 'C' ) ";
        $sql.= 'WHERE ';
        if(is_numeric($param)){
            $sql.= "CLI_CODCLIE = $param or CLI_RUC_ESPOSA = $param ";
        }else{
            $sql.= "CLI_NOMBRE like '%".$param."%'";
        }
        
        //echo $sql; die();
        $query =  $this->db->query($sql);
        return $query->getResult();
    }

    public function get_atenciones($codcliente){
        $sql = 'SELECT distinct CIT_CODCIT, CIT_CODCLIE, CIT_CODCAMP, CIT_ESTADO,  ';
        $sql.= "CAM_DESCRIP,CAM_FEC_INI,";
        $sql.= "HIS_CODHIS,HIS_CODCIT,HIS_CODCLI,HIS_CODCAMP, ";
        $sql.= "HISR_CODHIS  ";
        $sql.= "FROM ";
        $sql.= "dbo.CITAS as CIT ";
        $sql.= "INNER JOIN dbo.CAMPANIA AS CAM ON(CIT.CIT_CODCAMP=CAM.CAM_CODCAMP) ";
        $sql.= "LEFT JOIN dbo.HISTORIA AS HIS ON(CIT.CIT_CODCIT = HIS.HIS_CODCIT) ";
        $sql.= "LEFT JOIN dbo.HISTORIA_RECETA AS HISR ON(HIS.HIS_CODHIS = HISR.HISR_CODHIS) ";
        $sql.= "WHERE ";
        $sql.= "CIT_CODCLIE = $codcliente ";
        $query =  $this->db->query($sql);
        return $query->getResult();
    }

    public function set_citas($data){ 
        $citas =  $this->db->table('CITAS')->insert($data);
        return $citas;
    }

    /**
     * Obtener cita con información extendida para servicios y pagos
     */
    public function getCitaExtendida($citaId)
    {
        $sql = "SELECT 
                    c.CIT_CODCIT,
                    c.CIT_CODCLIE,
                    c.CIT_CODCAMP,
                    c.CIT_ORD,
                    c.CIT_ORD_ATENCION,
                    c.CIT_CODMEDIO,
                    c.CIT_ESTADO,
                    c.CIT_FECHA,
                    c.CIT_HORA,
                    c.CIT_OBSERVACIONES,
                    c.CIT_LOCAL_ORIGEN,
                    c.CIT_TOTAL,
                    c.CIT_SALDO,
                    cli.CLI_NOMBRE,
                    cli.CLI_RUC_ESPOSA,
                    cli.CLI_TELEF1,
                    cli.CLI_FECHA_NAC,
                    cam.CAM_DESCRIP,
                    FLOOR((CAST(CONVERT(VARCHAR(8),GETDATE(),112) AS INT)-CAST(CONVERT(VARCHAR(8),cli.CLI_FECHA_NAC,112) AS INT)) / 10000) as EDAD
                FROM CITAS c
                INNER JOIN CLIENTES cli ON c.CIT_CODCLIE = cli.CLI_CODCLIE
                LEFT JOIN CAMPANIA cam ON c.CIT_CODCAMP = cam.CAM_CODCAMP
                WHERE c.CIT_CODCIT = ?";
        
        $query = $this->db->query($sql, [$citaId]);
        return $query->getRow();
    }

    /**
     * Crear nueva cita con campos extendidos
     */
    public function crearCitaExtendida($data)
    {
        // Asegurar valores por defecto
        $defaults = [
            'CIT_FECHA' => date('Y-m-d'),
            'CIT_HORA' => date('H:i:s'),
            'CIT_LOCAL_ORIGEN' => '01',
            'CIT_TOTAL' => 0,
            'CIT_SALDO' => 0,
            'CIT_ESTADO' => 0 // 0 = INSCRITO
        ];

        $data = array_merge($defaults, $data);
        
        return $this->db->table('CITAS')->insert($data);
    }

    /**
     * Actualizar totales de la cita
     */
    public function actualizarTotales($citaId, $total, $saldo)
    {
        $data = [
            'CIT_TOTAL' => $total,
            'CIT_SALDO' => $saldo
        ];
        
        return $this->db->table('CITAS')
                       ->where('CIT_CODCIT', $citaId)
                       ->update($data);
    }

    /**
     * Obtener citas con servicios y saldos
     */
    public function getCitasConServicios($campaniaId = null, $estado = null, $fechaInicio = null, $fechaFin = null)
    {
        $sql = "SELECT 
                    c.CIT_CODCIT,
                    c.CIT_CODCLIE,
                    c.CIT_CODCAMP,
                    c.CIT_ORD,
                    c.CIT_ESTADO,
                    c.CIT_FECHA,
                    c.CIT_HORA,
                    c.CIT_TOTAL,
                    c.CIT_SALDO,
                    cli.CLI_NOMBRE,
                    cli.CLI_RUC_ESPOSA,
                    cli.CLI_TELEF1,
                    cam.CAM_DESCRIP,
                    FLOOR((CAST(CONVERT(VARCHAR(8),GETDATE(),112) AS INT)-CAST(CONVERT(VARCHAR(8),cli.CLI_FECHA_NAC,112) AS INT)) / 10000) as EDAD,
                    ISNULL(servicios.CANT_SERVICIOS, 0) as CANT_SERVICIOS,
                    ISNULL(pagos.TOTAL_PAGADO, 0) as TOTAL_PAGADO,
                    CASE 
                        WHEN c.CIT_SALDO <= 0 THEN 'PAGADO'
                        WHEN ISNULL(pagos.TOTAL_PAGADO, 0) > 0 THEN 'PARCIAL'
                        ELSE 'PENDIENTE'
                    END AS ESTADO_PAGO
                FROM CITAS c
                INNER JOIN CLIENTES cli ON c.CIT_CODCLIE = cli.CLI_CODCLIE AND cli.CLI_CP = 'C'
                LEFT JOIN CAMPANIA cam ON c.CIT_CODCAMP = cam.CAM_CODCAMP
                LEFT JOIN (
                    SELECT CNS_CODCIT, COUNT(*) as CANT_SERVICIOS
                    FROM CITAS_SERVICIOS 
                    GROUP BY CNS_CODCIT
                ) servicios ON c.CIT_CODCIT = servicios.CNS_CODCIT
                LEFT JOIN (
                    SELECT CIT_CODCIT, SUM(CTP_MONTO) as TOTAL_PAGADO
                    FROM CITAS_PAGOS 
                    WHERE CTP_ESTADO = 1
                    GROUP BY CIT_CODCIT
                ) pagos ON c.CIT_CODCIT = pagos.CIT_CODCIT
                WHERE 1=1";

        $params = [];

        if ($campaniaId) {
            $sql .= " AND c.CIT_CODCAMP = ?";
            $params[] = $campaniaId;
        }

        if (!is_null($estado)) {
            $sql .= " AND c.CIT_ESTADO = ?";
            $params[] = $estado;
        }

        // Comentado: filtros de fecha no se usan en este consultorio
        // if ($fechaInicio) {
        //     $sql .= " AND c.CIT_FECHA >= ?";
        //     $params[] = $fechaInicio;
        // }

        // if ($fechaFin) {
        //     $sql .= " AND c.CIT_FECHA <= ?";
        //     $params[] = $fechaFin;
        // }

        $sql .= " ORDER BY c.CIT_FECHA DESC, c.CIT_HORA DESC";

        $query = $this->db->query($sql, $params);
        return $query->getResult();
    }

    public function upd_cita($id,$update_rows){
		$result = $this->db->table('CITAS')->update($update_rows,"CIT_CODCIT = $id");	
		return $result;
    }

    public function get_orden($campania){
            $sql = ' SELECT MAX(CIT_ORD)+1 as CIT_ORD  FROM CITAS ';
            $sql.= "WHERE CIT_CODCAMP = $campania ";
            //echo $sql; die();
            $query =  $this->db->query($sql);
            return $query->getRow()->CIT_ORD;
    }
    public function get_orden_atencion($campania,$cita){
        $sql = " SELECT MAX(CIT_ORD_ATENCION)+1 as CIT_ORD_ATENCION, (SELECT CIT_ORD_ATENCION FROM CITAS WHERE CIT_CODCIT=$cita) AS ORD FROM CITAS ";
        $sql.= "WHERE CIT_CODCAMP = $campania ";
        //echo $sql; die();
        $query =  $this->db->query($sql);
        return $query->getRow();
    }
    public function set_orden_atencion($id,$ord){
        $update_rows = array('CIT_ORD_ATENCION' => $ord ); 
        $result = $this->db->table('CITAS')->update($update_rows,"CIT_CODCIT = $id");	
    return $result;
}
public function confirma_cita($cit,$update_rows){
    $result = $this->db->table('CITAS')->update($update_rows, ['CIT_CODCIT' => $cit]);	
		return $result;
}

public function elimina_cita($cit,$update_rows){
    $result = $this->db->table('CITAS')->where('CIT_CODCIT', $cit)->delete();

		return $result;
}
public function actualizar_personas($CLIENTE1,$CLIENTE2){
    $sql = 'UPDATE DBO.CITAS SET CIT_CODCLIE = '.$CLIENTE1;
    $sql.= ' WHERE CIT_CODCLIE = '.$CLIENTE2;
    return $this->db->simpleQuery($sql);
}

}