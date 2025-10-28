<?php

namespace App\Libraries;

use Exception;

class SimpleHtmlDom {

    public function __construct() {
        // Cargar la librería Simple HTML DOM
        require_once(APPPATH . 'ThirdParty/simple_html_dom.php');
    }

    public function strGetHtml($htmlContent) {
        return str_get_html($htmlContent);
    }
}
?>