<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Importar extends CI_Controller {

    public function __construct() {
        parent::__construct();

        // Cargar la librería Simple_html_dom
        $this->load->library('simple_html_dom');
    }

    public function procesarFactura() {
        // Procesar el archivo HTML subido
        if ($_FILES['factura_html']['error'] == UPLOAD_ERR_OK) {
            $htmlContent = file_get_contents($_FILES['factura_html']['tmp_name']);

            // Extraer datos del HTML
            $data = $this->extraerDatosFactura($htmlContent);

            // Insertar datos en la base de datos
            $this->FacturaModel->insertarFactura($data);

            echo "Factura procesada y almacenada correctamente.";
        } else {
            echo "Error al subir el archivo.";
        }
    }

    private function extraerDatosFactura($htmlContent) {
        // Convertir el contenido HTML en un objeto manipulable
        $html = $this->simple_html_dom->str_get_html($htmlContent);

        // Verificar si el HTML se cargó correctamente
        if (!$html) {
            show_error("No se pudo cargar el HTML.");
        }

        // Extraer datos del comprobante, emisor, cliente, productos y totales
        // (Usa el código que te proporcioné anteriormente)
    }
}
?>