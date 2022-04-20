<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GeraisController extends BaseController
{

    use AuthorizesRequests;

    /**
     * @param Request $request
     * @throws Exception
     */
    public function salvarEditarEspacoTipo(Request $request)
    {
        $request = json_decode($request->getContent(), true);
        $request["id"] = empty($request["id"]) ? 0 : $request["id"];
        $request["descricao"] = $request["descricao"] ?? "";
        $request["status"] = $request["status"] ?? "";
        $SQL = <<<SQL
REPLACE INTO
    espaco_tipo
(
    id, descricao, status
)
VALUES
(
    {$request["id"]}, '{$request["descricao"]}', '{$request["status"]}'
)
SQL;
        try {
            DB::select($SQL);
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function buscarEspacoTipos(Request $request): array
    {
        $where = "";
        if (isset($request->status) && !empty($request->status)) {
            $where = "WHERE status = '{$request->status}'";
        }
        if (isset($request->descricao) && !empty($request->descricao)) {
            $where = empty($where) ? "WHERE descricao LIKE '%{$request->descricao}%'" : " AND descricao LIKE '%{$request->descricao}%'";
        }
        $SQL = <<<SQL
SELECT
    *
FROM
    espaco_tipo
$where
SQL;
        try {
            return DB::select($SQL);
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param $codEspacoTipo
     * @return array
     * @throws Exception
     */
    public function buscarEspacoTipo($codEspacoTipo): array
    {
        $SQL = <<<SQL
SELECT
    *
FROM
    espaco_tipo
WHERE
    id = $codEspacoTipo
SQL;
        try {
            return (array)  DB::select($SQL)[0];
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param Request $request
     * @throws Exception
     */
    public function salvarEditarEventoTipo(Request $request)
    {
        $request = json_decode($request->getContent(), true);
        $request["id"] = empty($request["id"]) ? 0 : $request["id"];
        $request["descricao"] = $request["descricao"] ?? "";
        $request["status"] = $request["status"] ?? "";
        $SQL = <<<SQL
REPLACE INTO
    evento_tipo
(
    id, descricao, status
)
VALUES
(
    {$request["id"]}, '{$request["descricao"]}', '{$request["status"]}'
)
SQL;
        try {
            DB::select($SQL);
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function buscarEventoTipos(Request $request): array
    {
        $where = "";
        if (isset($request->status) && !empty($request->status)) {
            $where = "WHERE status = '{$request->status}'";
        }
        if (isset($request->descricao) && !empty($request->descricao)) {
            $where = empty($where) ? "WHERE descricao LIKE '%{$request->descricao}%'" : " AND descricao LIKE '%{$request->descricao}%'";
        }
        $SQL = <<<SQL
SELECT
    *
FROM
    evento_tipo
$where
SQL;
        try {
            return DB::select($SQL);
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param $codEspacoTipo
     * @return array
     * @throws Exception
     */
    public function buscarEventoTipo($codEspacoTipo): array
    {
        $SQL = <<<SQL
SELECT
    *
FROM
    evento_tipo
WHERE
    id = $codEspacoTipo
SQL;
        try {
            return (array)  DB::select($SQL)[0];
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }
}
