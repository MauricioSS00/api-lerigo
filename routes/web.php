<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\EspacoController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, "login"]);

Route::get('/usuario/{codUsuario}', [UsuarioController::class, "listarUsuario"]);
Route::get('/usuarios', [UsuarioController::class, "listarUsuarios"]);
Route::post('/usuario', [UsuarioController::class, "salvarUsuario"]);
Route::post('/usuario_rapido', [UsuarioController::class, "salvarUsuarioRapido"]);

Route::get('/evento/{codEvento}', [EventoController::class, "listarEvento"]);
Route::get('/eventos', [EventoController::class, "listarEventos"]);
Route::post('/evento', [EventoController::class, "salvarEvento"]);

Route::get('/espaco/{codEspaco}', [EspacoController::class, "listarEspaco"]);
Route::get('/espacos', [EspacoController::class, "listarEspacos"]);
Route::post('/espaco', [EspacoController::class, "salvarEspacos"]);
