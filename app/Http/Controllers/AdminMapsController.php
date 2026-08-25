<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminMapsController extends Controller {

    public function officeMapsView() {
        return View("admin.maps-location");
    }
}
