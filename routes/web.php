<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, "login"]);
Route::get('/usuarios', [UsuarioController::class, "listarUsuarios"]);
Route::post('/usuario', [UsuarioController::class, "salvarUsuario"]);
Route::get('/usuario/{codUsuario}', [UsuarioController::class, "listarUsuario"]);
