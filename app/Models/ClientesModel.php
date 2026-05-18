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

    protected $table = 'CLIENTES';
    protected $primaryKey = 'CLI_CODCLIE';
    protected $returnType = 'array';
    protected $allowedFields = []; // We'll define this if needed, but custom queries are used
    protected $useAutoIncrement = false;
    protected $db;
    protected $dbpm;
    protected $dbjj;
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    /**
     * Búsqueda unificada de personas/clientes con normalización de texto
     * 
     * @param string|null $busqueda Término de búsqueda (nombre, código, RUC)
     * @param int|null $CODCLIE Código específico del cliente
     * @param int|null $CODCIA Código de compañía
     * @param string|null $CLICP Tipo de cliente ('C' = Cliente, 'P' = Proveedor)
     * @param string $formato Formato de retorno: 'completo' o 'simple' (id, text)
     * @param bool $busqueda_avanzada Si es true, usa búsqueda por palabras con normalización de acentos
     * @return array Resultados de la búsqueda
     */
    public function get_personas($busqueda = null, $CODCLIE = null, $CODCIA = null, $CLICP = null, $formato = 'completo', $busqueda_avanzada = true)
    {
        // Determinar campos a seleccionar según el formato
        if ($formato === 'simple') {
            $sql = 'SELECT CLI_CODCLIE AS id, RTRIM(CLI_NOMBRE) AS text ';
        } else {
            $sql = 'SELECT CLI_CODCLIE,CLI_CODCIA,CLI_CP,RTRIM(CLI_NOMBRE) CLI_NOMBRE,RTRIM(CLI_NOMBRE_ESPOSA) CLI_NOMBRE_ESPOSA,CLI_123,';
            $sql .= 'RTRIM(CLI_CASA_DIREC) CLI_CASA_DIREC,CLI_CASA_NUM,CLI_CASA_ZONA,CLI_CASA_SUBZONA,RTRIM(CLI_RUC_ESPOSO) CLI_RUC_ESPOSO,';
            $sql .= 'RTRIM(CLI_RUC_ESPOSA) CLI_RUC_ESPOSA,CLI_ESTADO,CLI_MONEDA,CLI_TIPOCLI,CLI_ZONA_NEW,CLI_TELEF1,CLI_FECHA_NAC, ';
            $sql .= "floor((cast(convert(varchar(8),getdate(),112) as int)-cast(convert(varchar(8),CLI_FECHA_NAC,112) as int)) / 10000) as EDAD ";
        }

        $sql .= "FROM dbo.CLIENTES ";
        $sql .= "WHERE CLI_CODCLIE <> 1 ";

        // Filtros específicos
        $sql .= empty($CODCLIE) ? "" : "AND CLI_CODCLIE = $CODCLIE ";
        $sql .= empty($CODCIA) ? "" : "AND CLI_CODCIA = $CODCIA ";
        $sql .= empty($CLICP) ? "" : "AND CLI_CP = '$CLICP' ";

        // Búsqueda por término
        if (!empty($busqueda)) {
            if ($busqueda_avanzada) {
                // Normalizar búsqueda: convertir a mayúsculas y reemplazar acentos
                $busqueda_normalizada = strtoupper($busqueda);
                $busqueda_normalizada = str_replace(
                    ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
                    ['A', 'E', 'I', 'O', 'U', 'Ñ'],
                    $busqueda_normalizada
                );

                // Limpiar caracteres no alfanuméricos pero conservar espacios y "Ñ"
                $busqueda_normalizada = preg_replace('/[^A-Z0-9 Ñ]/', '', $busqueda_normalizada);

                // Dividir por palabras
                $palabras = array_filter(array_map('trim', explode(' ', $busqueda_normalizada)));

                // Construir condiciones de búsqueda para cada palabra
                $condiciones = [];
                foreach ($palabras as $palabra) {
                    if (is_numeric($palabra)) {
                        // Si es numérico, buscar en código y RUCs
                        $condiciones[] = "(CLI_CODCLIE = $palabra OR CLI_RUC_ESPOSO LIKE '%$palabra%' OR CLI_RUC_ESPOSA LIKE '%$palabra%')";
                    } else {
                        // Si es texto, buscar en nombre
                        $condiciones[] = "CLI_NOMBRE LIKE '%$palabra%'";
                    }
                }

                // Agregar condiciones a la consulta
                if (!empty($condiciones)) {
                    $sql .= "AND (" . implode(' AND ', $condiciones) . ") ";
                }
            } else {
                // Búsqueda simple (original)
                $sql .= "AND (CLI_NOMBRE LIKE '%$busqueda%' OR CLI_CODCLIE = (CASE WHEN ISNUMERIC('$busqueda')=1 THEN '$busqueda' ELSE '0' END)) ";
            }
        }

        // Ordenar resultados
        $sql .= "ORDER BY CLI_NOMBRE";

        // Ejecutar consulta
        $query = $this->db->query($sql);

        // Verificar errores
        if (!$query) {
            $error = $this->db->error();
            log_message('error', 'Database query failed in get_personas: ' . json_encode($error));
            return [];
        }

        return $query->getResult();
    }

    /**
     * Alias para mantener compatibilidad con código existente que usa get_personas2
     * @deprecated Usar get_personas() con $busqueda_avanzada = true
     */
    public function get_personas2($busqueda)
    {
        return $this->get_personas($busqueda, null, null, 'C', 'simple', true);
    }

    /**
     * Alias para mantener compatibilidad con código existente que usa get_personas3
     * @deprecated Usar get_personas() con formato = 'simple'
     */
    public function get_personas3($busqueda)
    {
        return $this->get_personas($busqueda, null, null, 'C', 'simple', false);
    }

    /**
     * Búsqueda de proveedores
     * @param string $busqueda Término de búsqueda
     * @param bool $busqueda_avanzada Si es true, usa búsqueda avanzada con normalización
     * @return array Resultados de la búsqueda
     */
    public function get_proveedores($busqueda, $busqueda_avanzada = true)
    {
        return $this->get_personas($busqueda, null, null, 'P', 'simple', $busqueda_avanzada);
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
    
    /**
     * Obtiene lista de proveedores activos para dropdown
     */
    public function getProveedoresActivos()
    {
        $builder = $this->db->table('CLIENTES');
        $builder->select('CLI_CODCLIE as cli_codclie, CLI_NOMBRE as cli_nombre');
        $builder->where('CLI_CP', 'P'); // Proveedores
        $builder->where('CLI_ESTADO', 'A'); // Activos (asumiendo que 'A' es activo)
        $builder->orderBy('CLI_NOMBRE', 'ASC');
        
        $query = $builder->get();
        return $query->getResultArray();
    }
}
