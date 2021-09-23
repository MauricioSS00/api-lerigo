<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UsuarioController extends BaseController
{
    use AuthorizesRequests;

    public function listarEventos()
    {
        return DB::select("SELECT * FROM evento");
    }

    public function salvarEvento(Request $request)
    {
        return $request;
    }
}
