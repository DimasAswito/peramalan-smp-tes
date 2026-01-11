<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PeramalanTesController extends Controller
{
    public function index()
    {
        return view('menu.peramalan_tes');
    }
}
