<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class RenderImage extends BaseController
{
    public function index($dir,$imageName)
    {
        //echo WRITEPATH.'uploads\\'.$dir.'\\'.$imageName;
        if(($image = file_get_contents(WRITEPATH.'uploads\\'.$dir.'\\'.$imageName)) === FALSE)
            show_404();

        // choose the right mime type
        $mimeType = 'image/jpg';

        $this->response
            ->setStatusCode(200)
            ->setContentType($mimeType)
            ->setBody($image)
            ->send();

    }
}


