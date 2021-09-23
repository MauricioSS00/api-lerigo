<?php

use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/eventos', [UsuarioController::class, "listarEventos"]);
Route::post('/eventos', [UsuarioController::class, "salvarEvento"]);
