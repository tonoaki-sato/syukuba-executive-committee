<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SafetyController extends Controller
{
    /**
     * Display the security patrol plan page.
     */
    public function index()
    {
        return view('safety.index');
    }
}
