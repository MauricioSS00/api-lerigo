<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ColaboradorController extends BaseController
{

    use AuthorizesRequests;

    /**
     * @param Request $request
     * @throws Exception
     */
    public function salvarColaborador(Request $request)
    {
        $request = json_decode($request->getContent(), true);
        $request["id"] = empty($request["id"]) ? 0 : $request["id"];
        $request["nome"] = $request["nome"] ?? "";
        $request["descricao"] = $request["descricao"] ?? "";
        $request["foto"] = $request["foto"] ?? "";
        $request["funcao"] = $request["funcao"] ?? "";
        $request["status"] = $request["status"] ?? "";
        $SQL = <<<SQL
REPLACE INTO
    colaborador
(
    id, nome, descricao, foto, funcao, status
)
VALUES
(
    {$request["id"]}, '{$request["nome"]}', '{$request["descricao"]}', '{$request["foto"]}', '{$request["funcao"]}',
    {$request["status"]}
)
SQL;
        try {
            DB::select($SQL);
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param int $codColaborador
     * @return array
     */
    public function listarColaborador(int $codColaborador): array
    {
        $colaborador = DB::select("SELECT * FROM colaborador WHERE id = $codColaborador");
        if (count($colaborador) > 0) {
            return (array) $colaborador[0];
        }
        return [];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function listarColaboradores(Request $request): array
    {
        $where = "";
        if (isset($request->status) && !empty($request->status)) {
            $where = "WHERE status = {$request->status}";
        }
        return DB::select("SELECT * FROM colaborador $where");
    }

    public function atualizarStatus(Request $request): bool
    {
        $SQL = <<<SQL
UPDATE colaborador SET status = IF(status = 1, 0, 1) WHERE id = {$request->codigo}
SQL;
        try {
            DB::select($SQL);
            return true;
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }
}
