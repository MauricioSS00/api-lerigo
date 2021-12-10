<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\EspacoController;
use App\Http\Controllers\BuscasController;

Route::post('/login', [AuthController::class, "login"]);

//Route::middleware('auth:api')->group(function () {
    Route::get('/usuario/{codUsuario}', [UsuarioController::class, "listarUsuario"]);
    Route::get('/usuarios', [UsuarioController::class, "listarUsuarios"]);
    Route::post('/usuario', [UsuarioController::class, "salvarUsuario"]);
    Route::post('/usuario_rapido', [UsuarioController::class, "salvarUsuarioRapido"]);

    Route::get('/evento/{codEvento}', [EventoController::class, "listarEvento"]);
    Route::get('/evento/{codEvento}', function (Request $request, $codEvento) {
        (new EventoController)->listarEvento($codEvento, $request);
    });
    Route::get('/eventos', [EventoController::class, "listarEventos"]);
    Route::post('/evento', [EventoController::class, "salvarEvento"]);

    Route::get('/espaco/{codEspaco}', [EspacoController::class, "listarEspaco"]);
    Route::get('/espacos', [EspacoController::class, "listarEspacos"]);
    Route::post('/espaco', [EspacoController::class, "salvarEspacos"]);

    Route::get('/dropdown/artista', [BuscasController::class, "artistaDropdown"]);
    Route::get('/dropdown/produtor', [BuscasController::class, "produtorDropdown"]);
    Route::get('/dropdown/espaco', [BuscasController::class, "espacoDropdown"]);
//});
