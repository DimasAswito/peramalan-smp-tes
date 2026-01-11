<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PeramalanSmpController extends Controller
{
    public function index()
    {
        return view('menu.peramalan_smp');
    }
}
