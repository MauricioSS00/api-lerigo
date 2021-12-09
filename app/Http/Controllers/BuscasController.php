<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BuscasController extends BaseController
{

    use AuthorizesRequests;

    public function artistaDropdown(Request $request): array
    {
        $where = "";
        if ($request->nome) {
            $where = "AND nome like '%{$request->nome}%'";
        }
        $SQL = <<<SQL
SELECT
	u.id value,
	u.nome label
FROM
	usuario_outros_dados uod
JOIN
	users u ON u.id = uod.id_usuario
WHERE
	uod.tipo = 'artista' $where
SQL;

        try {
            return DB::select($SQL);
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    public function produtorDropdown(Request $request): array
    {
        $where = "";
        if ($request->nome) {
            $where = "AND nome like '%{$request->nome}%'";
        }
        $SQL = <<<SQL
SELECT
	u.id value,
	u.nome label
FROM
	usuario_outros_dados uod
JOIN
	users u ON u.id = uod.id_usuario
WHERE
	uod.tipo = 'produtor' $where
SQL;
        try {
            return DB::select($SQL);
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }
}
