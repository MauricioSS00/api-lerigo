<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\EspacoController;
use App\Http\Controllers\BuscasController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\PermissaoController;

Route::post('/login', [AuthController::class, "login"]);
Route::get('/logout', [AuthController::class, "logout"]);
Route::get('/refresh', [AuthController::class, "refresh"]);

Route::post('/usuario_rapido', [UsuarioController::class, "salvarUsuarioRapido"]);

Route::get('/usuarios/produtor', [UsuarioController::class, "listarProdutores"]);
Route::get('/usuarios/artista', [UsuarioController::class, "listarArtistas"]);

Route::get('/evento/{codEvento}', function (Request $request, $codEvento) {
    (new EventoController)->listarEvento($codEvento, $request);
});
Route::get('/eventos', [EventoController::class, "listarEventos"]);

Route::get('/espaco/{codEspaco}', [EspacoController::class, "listarEspaco"]);
Route::get('/espacos', [EspacoController::class, "listarEspacos"]);

Route::get('/post/{codEspaco}', [BlogController::class, "buscarPost"]);
Route::get('/posts', [BlogController::class, "buscarPosts"]);

Route::get('/colaboradores', [ColaboradorController::class, "listarColaboradores"]);

Route::middleware('apiJwt')->group(function () {
    Route::get('/usuario/{codUsuario}', [UsuarioController::class, "listarUsuario"]);
    Route::get('/usuarios', [UsuarioController::class, "listarUsuarios"]);

    Route::post('/evento', [EventoController::class, "salvarEvento"]);
    Route::post('/espaco', [EspacoController::class, "salvarEspacos"]);
    Route::post('/espaco/administradores', [EspacoController::class, "salvarAdministradoresEspaco"]);

    Route::post('/post', [BlogController::class, "salvarPost"]);
    Route::put('/post', [BlogController::class, "atualizarStatus"]);

    Route::prefix('/dropdown')->group(function () {
        Route::get('/artista', [BuscasController::class, "artistaDropdown"]);
        Route::get('/produtor', [BuscasController::class, "produtorDropdown"]);
        Route::get('/espaco', [BuscasController::class, "espacoDropdown"]);
    });

    Route::post('/colaborador', [ColaboradorController::class, "salvarColaborador"]);
    Route::put('/colaborador', [ColaboradorController::class, "atualizarStatus"]);
    Route::get('/colaborador/{codColaborador}', [ColaboradorController::class, "listarColaborador"]);

    Route::post('/usuario', [UsuarioController::class, "salvarUsuario"]);

});

Route::prefix('/permissao')->group(function () {
    Route::get('/produtor/artista', [PermissaoController::class, "buscarPermissaoProdutorArtista"]);
    Route::get('/evento/espaco', [PermissaoController::class, "buscarPermissaoEventoEspaco"]);
    Route::get('/evento/artista', [PermissaoController::class, "listarPermissaoEventoArtista"]);
    Route::put('/status', [PermissaoController::class, "responderPermissao"]);
    Route::prefix('solicitar')->group(function () {
        Route::post('/produtor/artista', function (Request $request) {
            $request = json_decode($request->getContent(), true);
            (new PermissaoController)->solicitarPermissaoProdutorArtista($request["produtor"], $request["artista"], $request["solicitante"]);
        });
    });
});
