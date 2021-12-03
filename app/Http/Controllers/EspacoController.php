<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EspacoController extends BaseController
{

    use AuthorizesRequests;

    /**
     * @return array
     */
    public function listarEspacos(): array
    {
        $espacos = DB::select("SELECT * FROM espaco");
        if (is_array($espacos)) {
            foreach ($espacos as &$espaco) {
                $espaco->fotosEspaco = $this->buscarFotos($espaco->id);
                $espaco->turnoEspaco = $this->buscarTurno($espaco->id);
            }
        }
        return $espacos;
    }

    /**
     * @param int $codEspaco
     * @return array|mixed
     */
    public function listarEspaco(int $codEspaco)
    {
        $espaco = DB::select("SELECT * FROM espaco WHERE id = $codEspaco");
        if (is_array($espaco)) {
            $espaco[0]->fotosEspaco = $this->buscarFotos($codEspaco);
            $espaco[0]->turnoEspaco = $this->buscarTurno($codEspaco);
            return $espaco[0];
        }
        return [];
    }

    /**
     * @param int $codEspaco
     * @return array
     */
    private function buscarFotos(int $codEspaco): array
    {
        return DB::select("SELECT foto FROM espaco_foto WHERE id_espaco = $codEspaco");
    }

    /**
     * @param int $codEspaco
     * @return array
     */
    private function buscarTurno(int $codEspaco): array
    {
        return DB::select("SELECT * FROM espaco_turno WHERE id_espaco = $codEspaco");
    }

    /**
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    public function salvarEspacos(Request $request): bool
    {
        $request = json_decode($request->getContent(), true);
        $request["nome"] = $request["nome"] ?? "";
        $request["razao"] = $request["razao"] ?? "";
        $request["logo"] = $request["logo"] ?? "";
        $request["CNPJ"] = $request["CNPJ"] ?? "";
        $request["email"] = $request["email"] ?? "";
        $request["fone"] = $request["fone"] ?? "";
        $request["space_whats"] = $request["space_whats"] ?? "";
        $request["celular"] = $request["celular"] ?? "";
        $request["abertura"] = $request["abertura"] ?? date("c");
        $request["acess"] = $request["acess"] ?? 0;
        $request["estac"] = $request["estac"] ?? 0;
        $request["descricao"] = $request["descricao"] ?? "";
        $this->validarEndereco($request);
        $request["lotMax"] = $request["lotMax"] ?? 0;
        $request["facebook"] = $request["facebook"] ?? "";
        $request["instagram"] = $request["instagram"] ?? "";
        $request["twitter"] = $request["twitter"] ?? "";
        $request["site"] = $request["site"] ?? "";
        $request["programacao"] = $request["programacao"] ?? 1;
        $request["tipo"] = $request["tipo"] ?? 1;
        $request["horario"] = $request["horario"] ?? [];
        $request["fotosEspaco"] = $request["fotosEspaco"] ?? [];
        $request["id"] = empty($request["id"]) ? 0 : $request["id"];
        $request["abertura"] = date("Y-m-d", strtotime($request["abertura"]));

        $SQL = <<<SQL
REPLACE INTO
    espaco
(
    id, nome, razao, logo, cnpj, email, telefone, whatsapp, celular, data_abertura, acessivel, estacionamento,
    descricao, bairro, cep, complemento, uf, cidade, logradouro, numero_endereco, lotacao_maxima, facebook,
    instagram, twitter, site, programacao, tipo
)
VALUES
(
    {$request["id"]}, '{$request["nome"]}', '{$request["razao"]}', '{$request["logo"]}',
    '{$request["CNPJ"]}', '{$request["email"]}', '{$request["fone"]}', '{$request["space_whats"]}',
    '{$request["celular"]}', '{$request["abertura"]}', '{$request["acess"]}', '{$request["estac"]}',
    '{$request["descricao"]}', '{$request["endereco"]["bairro"]}', '{$request["endereco"]["cep"]}',
    '{$request["endereco"]["complemento"]}', '{$request["endereco"]["uf"]}', '{$request["endereco"]["localidade"]}',
    '{$request["endereco"]["logradouro"]}', '{$request["endereco"]["numero"]}', '{$request["lotMax"]}',
    '{$request["facebook"]}', '{$request["instagram"]}', '{$request["twitter"]}', '{$request["site"]}',
    '{$request["programacao"]}', '{$request["tipo"]}'
)
SQL;
        try {
            DB::select($SQL);
            if (is_array($request["horario"])) {
                $this->salvarTurno($request["id"], $request["horario"]);
            }
            if (is_array($request["fotosEspaco"]) && count($request["fotosEspaco"]) > 0) {
                $this->salvarFotos($request["id"], $request["fotosEspaco"]);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function validarEndereco(&$dados)
    {
        if (!isset($dados["endereco"])) {
            $dados["endereco"] = [
                "bairro" => "",
                "cep" => "",
                "complemento" => "",
                "uf" => "",
                "localidade" => "",
                "logradouro" => "",
                "numero" => 0
            ];
        } else {
            $dados["endereco"]["bairro"] = $dados["endereco"]["bairro"] ?? "";
            $dados["endereco"]["cep"] = $dados["endereco"]["cep"] ?? "";
            $dados["endereco"]["complemento"] = $dados["endereco"]["complemento"] ?? "";
            $dados["endereco"]["uf"] = $dados["endereco"]["uf"] ?? "";
            $dados["endereco"]["localidade"] = $dados["endereco"]["localidade"] ?? "";
            $dados["endereco"]["logradouro"] = $dados["endereco"]["logradouro"] ?? "";
            $dados["endereco"]["numero"] = $dados["endereco"]["numero"] ?? 0;
        }
    }

    /**
     * @param int $idEspaco
     * @param array $fotos
     */
    private function salvarFotos(int $idEspaco, array $fotos)
    {
        $idEspaco = !$idEspaco ? $this->buscarIdEspaco() : $idEspaco;
        DB::select("DELETE FROM espaco_foto WHERE id_espaco = $idEspaco");
        foreach ($fotos as $foto) {
            $SQL = <<<SQL
INSERT INTO
    espaco_foto
(
    id_espaco, foto
)
VALUES
(
  $idEspaco, '$foto'
)
SQL;
            DB::select($SQL);
        }
    }

    /**
     * @param int $idEspaco
     * @param array $turnos
     */
    private function salvarTurno(int $idEspaco, array $turnos)
    {
        $idEspaco = !$idEspaco ? $this->buscarIdEspaco() : $idEspaco;
        DB::select("DELETE FROM espaco_turno WHERE id_espaco = $idEspaco");
        foreach ($turnos as $turno) {
            $SQL = <<<SQL
INSERT INTO
    espaco_turno
(
    id_espaco, dia, turno_inicial_1, turno_final_1, turno_inicial_2, turno_final_2
)
VALUES
(
  $idEspaco, '{$turno["id"]}', '{$turno["ini1"]}', '{$turno["fim1"]}', '{$turno["ini2"]}', '{$turno["fim2"]}'
)
SQL;
            DB::select($SQL);
        }
    }

    private function buscarIdEspaco(): ?int
    {
        $SQL = <<<SQL
SELECT
    MAX(id) id
FROM
    espaco;
SQL;
        $result = DB::select($SQL);
        return is_array($result) ? $result[0]->id : null;
    }
}
