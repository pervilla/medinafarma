<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Description of ArticuloModel
 *
 * @author José Luis
 */
class ClientesModel extends Model
{

    var $table = 'clientes';
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    public function get_personas($CODCLIE, $CODCIA, $CLICP, $busqueda)
    {
        $sql = 'SELECT CLI_CODCLIE,CLI_CODCIA,CLI_CP,RTRIM(CLI_NOMBRE) CLI_NOMBRE,RTRIM(CLI_NOMBRE_ESPOSA) CLI_NOMBRE_ESPOSA,CLI_123,';
        $sql .= 'RTRIM(CLI_CASA_DIREC) CLI_CASA_DIREC,CLI_CASA_NUM,CLI_CASA_ZONA,CLI_CASA_SUBZONA,RTRIM(CLI_RUC_ESPOSO) CLI_RUC_ESPOSO,';
        $sql .= 'RTRIM(CLI_RUC_ESPOSA) CLI_RUC_ESPOSA,CLI_ESTADO,CLI_MONEDA,CLI_TIPOCLI,CLI_ZONA_NEW,CLI_TELEF1,CLI_FECHA_NAC, ';
        $sql .= "floor((cast(convert(varchar(8),getdate(),112) as int)-cast(convert(varchar(8),CLI_FECHA_NAC,112) as int)) / 10000) as EDAD ";
        $sql .= "FROM dbo.CLIENTES ";
        $sql .= "WHERE CLI_CODCLIE<>1 ";
        $sql .= empty($CODCLIE) ? "" : "AND CLI_CODCLIE = $CODCLIE ";
        $sql .= empty($CODCIA) ? "" : "AND CLI_CODCIA = $CODCIA ";
        $sql .= empty($CLICP) ? "" : "AND CLI_CP = '$CLICP' ";
        $sql .= empty($busqueda) ? "" : "AND (CLI_NOMBRE like '%$busqueda%' OR CLI_CODCLIE=(case when ISNUMERIC('$busqueda')=1 then '$busqueda' else '0' end)) ";
        //echo $sql; die();
        $query = $this->db->query($sql);
        return $query->getResult();
    }
    public function get_personas3($busqueda)
    {
        $sql = 'SELECT CLI_CODCLIE as id,RTRIM(CLI_NOMBRE) as text ';
        $sql .= "FROM dbo.CLIENTES ";
        $sql .= "WHERE CLI_CODCLIE<>1 ";
        $sql .= "AND CLI_CP = 'C' ";
        $sql .= empty($busqueda) ? "" : "AND (CLI_NOMBRE like '%$busqueda%' OR CLI_CODCLIE=(case when ISNUMERIC('$busqueda')=1 then '$busqueda' else '0' end)) ";
        //echo $sql; die();
        $query = $this->db->query($sql);
        return $query->getResult();
    }
    public function get_personas2($busqueda)
    {
        // Convertir a mayúsculas y reemplazar caracteres acentuados
        $busqueda = strtoupper($busqueda);
        $busqueda = str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
            ['A', 'E', 'I', 'O', 'U', 'Ñ'],
            $busqueda
        );

        // Limpiar caracteres no alfanuméricos pero conservar espacios y "Ñ"
        $busqueda = preg_replace('/[^A-Z0-9 Ñ]/', '', $busqueda);

        // Eliminar espacios al principio y al final, y dividir por palabras
        $palabras = array_filter(array_map('trim', explode(' ', $busqueda)));

        // Construir condiciones de búsqueda para cada palabra
        $condiciones = [];
        foreach ($palabras as $palabra) {
            if (is_numeric($palabra)) {
                $condiciones[] = "CLI_CODCLIE = $palabra OR CLI_RUC_ESPOSO LIKE '%$palabra%' OR CLI_RUC_ESPOSA LIKE '%$palabra%'";
            } else {
                $condiciones[] = "CLI_NOMBRE LIKE '%$palabra%'";
            }
        }

        // Validar si hay condiciones
        $condiciones_str = !empty($condiciones) ? implode(' AND ', $condiciones) : '1=1';

        // Construir consulta SQL
        $sql = 'SELECT CLI_CODCLIE AS id, RTRIM(CLI_NOMBRE) AS text ';
        $sql .= 'FROM dbo.CLIENTES ';
        $sql .= "WHERE CLI_CODCLIE <> 1 ";
        $sql .= "AND CLI_CP = 'C' ";
        $sql .= "AND $condiciones_str ";
        $sql .= 'ORDER BY CLI_NOMBRE';

        // Depuración del SQL generado
        log_message('debug', "Consulta generada: $sql");

        // Ejecutar la consulta
        $query = $this->db->query($sql);

        // Verificar si la consulta falló
        if (!$query) {
            log_message('error', 'Database query failed: ' . $this->db->error());
            return [];
        }

        return $query->getResult();
    }

    public function get_proveedores($busqueda)
    {
        $sql = 'SELECT CLI_CODCLIE as id,RTRIM(CLI_NOMBRE) as text ';
        $sql .= "FROM dbo.CLIENTES ";
        $sql .= "WHERE CLI_CODCLIE<>1 ";
        $sql .= "AND CLI_CP = 'P' ";
        $sql .= empty($busqueda) ? "" : "AND (CLI_NOMBRE like '%$busqueda%' OR CLI_CODCLIE=(case when ISNUMERIC('$busqueda')=1 then '$busqueda' else '0' end)) ";
        //echo $sql; die();
        $query = $this->db->query($sql);
        return $query->getResult();
    }
    public function get_max_id()
    {
        $sql = ' SELECT MAX(CLI_CODCLIE)+1 as CLI_CODCLIE FROM CLIENTES ';
        $sql .= "WHERE CLI_CP = 'C' ";
        $sql .= "AND CLI_CODCIA = 25 ";
        $query =  $this->db->query($sql);
        return $query->getRow()->CLI_CODCLIE;
    }
    public function get_pos_id($ruc, $tip)
    {
        $sql = ' SELECT top 1 CLI_CODCLIE  FROM CLIENTES ';
        $sql .= "WHERE ";
        $sql .= $tip == 1 ? "CLI_RUC_ESPOSA='$ruc' " : "CLI_RUC_ESPOSO='$ruc' ";
        $sql .= "AND CLI_CODCIA = 25 ";
        $query =  $this->db->query($sql);
        if (is_null($query->getRow())) {
            return false;
        } else {
            return $query->getRow()->CLI_CODCLIE;
        }
    }
    public function set_persona($data)
    {
        $clientes =  $this->db->table('CLIENTES')->insert($data);
        return $clientes;
    }
    public function set_dir_persona($data)
    {
        $clientes =  $this->db->table('DIRCLI')->insert($data);
        return $clientes;
    }
    public function editar_persona($data, $server)
    {
        $sql = 'UPDATE DBO.CLIENTES SET ';
        $sql .= " CLI_NOMBRE = '" . strtoupper($data['CLI_NOMBRE']) . "',";
        $sql .= " CLI_NOMBRE_ESPOSO = '" . strtoupper($data['CLI_NOMBRE']) . "',";
        $sql .= " CLI_TELEF1 = '" . $data['CLI_TELEF1'] . "',";
        $sql .= " CLI_CASA_DIREC = '" . $data['CLI_CASA_DIREC'] . "',";
        $sql .= " CLI_TRAB_DIREC = '" . $data['CLI_TRAB_DIREC'] . "',";
        $sql .= " CLI_RUC_ESPOSO = '" . $data['CLI_RUC_ESPOSO'] . "',";
        $sql .= " CLI_RUC_ESPOSA = '" . $data['CLI_RUC_ESPOSA'] . "',";
        $sql .= " CLI_FECHA_NAC = '" . $data['CLI_FECHA_NAC'] . "'";
        $sql .= " WHERE CLI_CODCLIE = " . $data['CLI_CODCLIE'];
        $sql .= " AND CLI_CODCIA = '25'";
        $sql .= " AND CLI_CP = '" . $data['CLI_CP'] . "'";

        //echo $sql;
        return $query =  $this->db->simpleQuery($sql);
    }
    public function eliminar_persona($id, $server)
    {
        if ($server == 2) {
            $builder =  $this->dbjj->table('CLIENTES');
        } elseif ($server == 3) {
            $builder =  $this->dbpm->table('CLIENTES');
        } else {
            $builder =  $this->db->table('CLIENTES');
        }
        return $builder->delete(['CLI_CODCLIE' => $id]);
    }
}
