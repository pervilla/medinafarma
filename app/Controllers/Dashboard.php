<?php

namespace App\Controllers;

use App\Models\AllogModel;
use App\Models\Server03Model;

class Dashboard extends BaseController {

    public function index() {
        return view('dashboard/index');
    }

}
