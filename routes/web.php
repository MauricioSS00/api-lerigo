<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EventoController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, "login"]);
Route::get('/usuario/{codUsuario}', [UsuarioController::class, "listarUsuario"]);
Route::get('/usuarios', [UsuarioController::class, "listarUsuarios"]);
Route::post('/usuario', [UsuarioController::class, "salvarUsuario"]);

Route::get('/evento/{codEvento}', [EventoController::class, "listarEvento"]);
Route::get('/eventos', [EventoController::class, "listarEventos"]);
Route::post('/evento', [EventoController::class, "salvarEvento"]);
