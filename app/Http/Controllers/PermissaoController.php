<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PermissaoController extends BaseController
{

    use AuthorizesRequests;

    /**
     * @param $idProdutor
     * @param $idArtista
     * @param $solicitante
     * @throws Exception
     */
    public function solicitarPermissaoProdutorArtista($idProdutor, $idArtista, $solicitante)
    {
        $SQL = <<<SQL
INSERT INTO
    permissao_produtor_artista
(
    id_produtor, id_artista, solicitante
)
VALUES
(
    $idProdutor, $idArtista, '$solicitante'
)
SQL;
        try {
            DB::select($SQL);
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro!");
        }
    }

    /**
     * @param $idEvento
     * @param $idEspaco
     * @throws Exception
     */
    public function solicitarPermissaoEventoEspaco($idEvento, $idEspaco)
    {
        $SQL = <<<SQL
INSERT INTO
    permissao_evento_espaco
(
    id_solicitante, id_solicitado
)
VALUES
(
    $idEvento, $idEspaco
)
SQL;
        try {
            DB::select($SQL);
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro!");
        }
    }

    /**
     * @param $idEvento
     * @param $idArtista
     * @throws Exception
     */
    public function solicitarPermissaoEventoArtista($idEvento, $idArtista)
    {
        $SQL = <<<SQL
INSERT INTO
    permissao_evento_artista
(
    id_solicitante, id_solicitado
)
VALUES
(
    $idEvento, $idArtista
)
SQL;
        try {
            DB::select($SQL);
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro!");
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function buscarPermissaoProdutorArtista(Request $request): array
    {
        $filtro = "";
        $campoJoin = "";
        if ($request->produtor) {
            $filtro = "id_produtor = $request->produtor";
            $campoJoin = "id_artista";
            $msg = "Você recebeu uma solicitação do artista {{nome}}";
        }
        if ($request->artista) {
            $filtro = "id_artista = $request->artista";
            $campoJoin = "id_produtor";
            $msg = "Você recebeu uma solicitação do produtor {{nome}}";
        }
        $SQL = <<<SQL
SELECT
    ppa.*,
    usr.nome,
    1 AS 'tipo'
FROM
    permissao_produtor_artista ppa,
    users usr
WHERE
    $filtro
    AND usr.id = ppa.$campoJoin
    AND aprovado = 'N'
SQL;
        try {
            $dados = DB::select($SQL);
            foreach ($dados as $dado) {
                $msg = str_replace("{{nome}}", $dado->nome, $msg);
                $dado->msg = $msg;
            }
            return $dados;
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro!");
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function buscarPermissaoEventoEspaco(Request $request): array
    {
        $filtro = "";
        $tabelaJoin = "";
        $campoJoin = "";
        if ($request->evento) {
            $filtro = "id_solicitante = $request->evento";
            $tabelaJoin = "espaco";
            $campoJoin = "id_solicitado";
            $msg = "Você recebeu uma solicitação do espaço {{nome}}";
        }
        if ($request->espaco) {
            $filtro = "id_solicitado = $request->espaco";
            $tabelaJoin = "evento";
            $campoJoin = "id_solicitante";
            $msg = "Você recebeu uma solicitação do evento {{nome}}";
        }
        $SQL = <<<SQL
SELECT
    pee.*,
    tbl.nome,
    2 AS 'tipo'
FROM
    permissao_evento_espaco pee,
    $tabelaJoin tbl
WHERE
    $filtro
    AND tbl.id = pee.$campoJoin
    AND aprovado = 'N'
SQL;
        try {
            $dados = DB::select($SQL);
            foreach ($dados as $dado) {
                $msg = str_replace("{{nome}}", $dado->nome, $msg);
                $dado->msg = $msg;
            }
            return $dados;
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro!");
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function listarPermissaoEventoArtista(Request $request): array
    {
        $filtro = "";
        $tabelaJoin = "";
        $campoJoin = "";
        if ($request->evento) {
            $filtro = "id_solicitante = $request->evento";
            $tabelaJoin = "users";
            $campoJoin = "id_solicitado";
            $msg = "Você recebeu uma solicitação do artista {{nome}}";
        }
        if ($request->artista) {
            $filtro = "id_solicitado = $request->artista";
            $tabelaJoin = "evento";
            $campoJoin = "id_solicitante";
            $msg = "Você recebeu uma solicitação do evento {{nome}}";
        }
        $SQL = <<<SQL
SELECT
    pea.*,
    tbl.nome,
    3 AS 'tipo'
FROM
    permissao_evento_artista pea,
    $tabelaJoin tbl
WHERE
    $filtro
    AND tbl.id = pea.$campoJoin
    AND aprovado = 'N'
SQL;
        try {
            $dados = DB::select($SQL);
            foreach ($dados as $dado) {
                $msg = str_replace("{{nome}}", $dado->nome, $msg);
                $dado->msg = $msg;
            }
            return $dados;
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro!");
        }
    }

    /**
     * @param Request $request
     * @throws Exception
     */
    public function responderPermissao(Request $request)
    {
        $request = json_decode($request->getContent(), true);
        $tabela = "";
        switch ($request["tipo"]) {
            case 1:
                $tabela = "permissao_produtor_artista";
                break;
            case 2:
                $tabela = "permissao_evento_espaco";
                break;
            case 3:
                $tabela = "permissao_evento_artista";
                break;
        }
        $SQL = <<<SQL
UPDATE
    $tabela
SET
    aprovado = '{$request["status"]}'
WHERE
    id = {$request["idPermissao"]}
SQL;
        try {
            DB::select($SQL);
            if ($request["status"] == 'S') {
                switch ($request["tipo"]) {
                    case 2:
                        $this->inserirRegistroArtista($request["idPermissao"]);
                        break;
                    case 3:
                        $this->inserirRegistroEspaco($request["idPermissao"]);
                        break;
                }
            }
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro!");
        }
    }

    /**
     * @param $idPermissao
     */
    public function inserirRegistroArtista($idPermissao)
    {
        $dados = (array) DB::select("SELECT * FROM permissao_evento_espaco WHERE id = $idPermissao")[0];
        $SQL = <<<SQL
UPDATE
    evento
SET
    espaco = {$dados["id_solicitado"]}
WHERE
    id = {$dados["id_solicitante"]}
SQL;
        DB::select($SQL);
    }

    /**
     * @param $idPermissao
     */
    public function inserirRegistroEspaco($idPermissao)
    {
        $dados = (array) DB::select("SELECT * FROM permissao_evento_artista WHERE id = $idPermissao")[0];
        $SQL = <<<SQL
INSERT INTO
    evento_artista
(
    id_evento, id_artista
)
VALUES
(
    {$dados["id_solicitante"]}, {$dados["id_solicitado"]}
)
SQL;
        DB::select($SQL);
    }
}
