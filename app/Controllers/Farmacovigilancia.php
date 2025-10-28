<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Farmacovigilancia extends BaseController
{
    public function index()
    {
        return view("farmacovigilancia/index");
    }
}
