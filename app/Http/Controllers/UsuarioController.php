<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UsuarioController extends BaseController
{
    use AuthorizesRequests;

    /**
     * @return array
     */
    public function listarUsuarios(): array
    {
        return DB::select("SELECT * FROM users");
    }

    /**
     * @param int $codUsuario
     * @return array|mixed
     */
    public function listarUsuario(int $codUsuario)
    {
        $usuario = DB::select("SELECT * FROM users WHERE id = $codUsuario");
        return is_array($usuario) ? $usuario[0] : [];
    }

    /**
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    public function salvarUsuario(Request $request): bool
    {
        $request = json_decode($request->getContent(), true);
        $endereco = $request["endereco"][0];
        $tipoPessoa = $request["tipoP"] == "cpf" ? "F" : "J";
        $dtNasc = date("d/m/Y", strtotime($request["dtNasc"]));
        $senha = bcrypt($request["user_password"]);
        $request["id"] = empty($request["id"]) ? 0 : $request["id"];
        $SQL = <<<SQL
REPLACE INTO
    users
(
    id, nome, nome_social, email, password, estrangeiro, tipo_pessoa, documento, genero, rg_pass, orgao_rg_pass,
    uf_rg_pass, data_nascimento, telefone, celular, whatsapp, cep, uf, cidade, logradouro,
    bairro, numero, complemento
)
VALUES
(
    {$request["id"]}, '{$request["user_nome_civ"]}', '{$request["user_soc_nome"]}', '{$request["user_email"]}', '$senha', {$request["estrangeiro"]},
    '$tipoPessoa', '{$request["doc"]}', '{$request["genero"]}', '{$request["user_pic_id"]}', '{$request["user_id_doc"]}', '{$request["user_id_state"]}',
    '$dtNasc', '{$request["fone"]}', '{$request["celular"]}', '{$request["user_whats"]}', '{$endereco["cep"]}', '{$endereco["uf"]}', '{$endereco["localidade"]}',
    '{$endereco["logradouro"]}', '{$endereco["bairro"]}', '{$endereco["numero"]}', '{$endereco["complemento"]}'
)
SQL;
        try {
            DB::select($SQL);
            return true;
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param Request $request
     * @return int|null
     * @throws Exception
     */
    public function salvarUsuarioRapido(Request $request): ?int
    {
        $request = json_decode($request->getContent(), true);
        $senha = bcrypt($request["user_password"]);
        $request["id"] = empty($request["id"]) ? 0 : $request["id"];
        $SQL = <<<SQL
INSERT INTO
    users
(
    nome, nome_social, email, password
)
VALUES
(
    '{$request["user_nome_civ"]}', '{$request["user_soc_nome"]}', '{$request["user_email"]}', '$senha'
)
SQL;
        try {
            DB::select($SQL);
            return $this->buscarIdEvento();
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @return int|null
     */
    private function buscarIdEvento(): ?int
    {
        $SQL = <<<SQL
SELECT
    MAX(id) id
FROM
    users;
SQL;
        $result = DB::select($SQL);
        return is_array($result) ? $result[0]->id : null;
    }
}
