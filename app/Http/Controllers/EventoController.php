<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EventoController extends BaseController
{
    use AuthorizesRequests;

    /**
     * @param Request $request
     * @return array
     */
    public function listarEventos(Request $request): array
    {
        $where = $this->condicaoFiltroEvento($request);
        $eventos = DB::select("SELECT * FROM evento $where");
        if (count($eventos) > 0) {
            foreach ($eventos as &$evento) {
                $evento->fotosEvento = $this->buscarFotos($evento->id);
            }
        }
        return $eventos;
    }

    /**
     * @param int $codEvento
     * @param Request $request
     * @return array|mixed
     */
    public function listarEvento(int $codEvento, Request $request)
    {
        $where = $this->condicaoFiltroEvento($request, true);
        $evento = DB::select("SELECT * FROM evento WHERE id = $codEvento $where");
        if (count($evento) > 0) {
            $evento[0]->fotosEvento = $this->buscarFotos($codEvento);
            return $evento[0];
        }
        return [];
    }

    /**
     * @param Request $request
     * @param bool $maisCondicoes
     * @return string
     */
    private function condicaoFiltroEvento(Request $request, bool $maisCondicoes = false): string {
        $where = "";
        $maisCondicoes = $maisCondicoes ? "AND" : "WHERE";
        if ($request->data1) {
            $request->data1 = date("Y-m-d", strtotime($request->data1));
            $where = "$maisCondicoes data = '{$request->data1}'";
        }
        if ($request->data1 && $request->data2) {
            $request->data1 = date("Y-m-d", strtotime($request->data1));
            $request->data2 = date("Y-m-d", strtotime($request->data2));
            $where = "$maisCondicoes data BETWEEN '{$request->data1}' AND '{$request->data2}'";
        }
        return $where;
    }

    /**
     * @param int $codEvento
     * @return array
     */
    private function buscarFotos(int $codEvento): array
    {
        return DB::select("SELECT foto FROM evento_foto WHERE id_evento = $codEvento");
    }

    /**
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    public function salvarEvento(Request $request): bool
    {
        $request = json_decode($request->getContent(), true);
        $request["nome"] = $request["nome"] ?? "";
        $request["classificacao"] = $request["classificacao"] ?? ["code" => 0];
        $request["data"] = $request["data"] ?? "";
        $request["hrIni"] = $request["hrIni"] ?? "";
        $request["hrFim"] = $request["hrFim"] ?? "";
        $request["descricao"] = $request["descricao"] ?? "";
        $request["resumo"] = $request["resumo"] ?? "";
        $request["facebook"] = $request["facebook"] ?? "";
        $request["instagram"] = $request["instagram"] ?? "";
        $request["site"] = $request["site"] ?? "";
        $request["tipo"] = $request["tipo"] ?? ["value" => 0];
        $request["espaco"] = $request["espaco"] ?? "";
        $request["imagemPerfil"] = $request["imagemPerfil"] ?? "";
        $request["id"] = empty($request["id"]) ? 0 : $request["id"];
        $request["data"] = date("Y-m-d", strtotime($request["data"]));
        $request["espaco"] = !is_array($request["espaco"]) ? 0 : $request["espaco"]["value"];
        $podeInserir = $this->permissaoEspaco($request["espaco"], $request["id"], $request["usuarioCriador"]);
        $campoEspaco = "";
        $valorEspaco = "";
        if ($podeInserir) {
            $campoEspaco = ", espaco";
            $valorEspaco = ", {$request["espaco"]}";
        }
        $SQL = <<<SQL
REPLACE INTO
    evento
(
    id, nome, classificacao, data, hora_ini, hora_fim, descricao, resumo, facebook,
    instagram, site, tipo, imagem_perfil $campoEspaco
)
VALUES
(
    {$request["id"]}, '{$request["nome"]}', '{$request["classificacao"]["code"]}', '{$request["data"]}',
    '{$request["hrIni"]}', '{$request["hrFim"]}', '{$request["descricao"]}', '{$request["resumo"]}',
    '{$request["facebook"]}', '{$request["instagram"]}', '{$request["site"]}', '{$request["tipo"]["value"]}',
    '{$request["imagemPerfil"]}' $valorEspaco
)
SQL;
        try {
            DB::select($SQL);
            if (isset($request["artistas"]) && is_array($request["artistas"])) {
                $this->salvarArtistas($request["id"], $request["artistas"], $request["usuarioCriador"]);
            }
            if (isset($request["artistas"]) && is_array($request["fotosEvento"]) && count($request["fotosEvento"]) > 0) {
                $this->salvarFotos($request["id"], $request["fotosEvento"]);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $idEspaco
     * @param $idEvento
     * @param $criadorEvento
     * @return bool
     * @throws Exception
     */
    private function permissaoEspaco($idEspaco, $idEvento, $criadorEvento): bool
    {
        $idEvento = !$idEvento ? $this->buscarIdEvento() : $idEvento;
        if (!$this->administradorEspaco($idEspaco, $criadorEvento)) {
            (new PermissaoController())->solicitarPermissaoEventoEspaco($idEvento, $idEspaco);
            return false;
        }
        return true;
    }

    /**
     * @param int $idEvento
     * @param array $artistas
     * @param $criadorEvento
     * @throws Exception
     */
    private function salvarArtistas(int $idEvento, array $artistas, $criadorEvento)
    {
        $idEvento = !$idEvento ? $this->buscarIdEvento() : $idEvento;
        DB::select("DELETE FROM evento_artista WHERE id_evento = $idEvento");
        foreach ($artistas as $artista) {
            if (!$this->produtorArtista($artista["value"], $criadorEvento)) {
                (new PermissaoController())->solicitarPermissaoEventoArtista($idEvento, $artista["value"]);
                continue;
            }
            $SQL = <<<SQL
INSERT INTO
    evento_artista
(
    id_evento, id_artista
)
VALUES
(
  $idEvento, {$artista["value"]}
)
SQL;
            DB::select($SQL);
        }
    }

    /**
     * @param int $idEvento
     * @param array $fotos
     */
    private function salvarFotos(int $idEvento, array $fotos)
    {
        $idEvento = !$idEvento ? $this->buscarIdEvento() : $idEvento;
        DB::select("DELETE FROM evento_foto WHERE id_evento = $idEvento");
        foreach ($fotos as $foto) {
            $SQL = <<<SQL
INSERT INTO
    evento_foto
(
    id_evento, foto
)
VALUES
(
  $idEvento, '$foto'
)
SQL;
            DB::select($SQL);
        }
    }

    /**
     * @param $idEspaco
     * @param $criadorEvento
     * @return bool
     */
    private function administradorEspaco($idEspaco, $criadorEvento): bool {
        $SQL = <<<SQL
SELECT
    *
FROM
    espaco_administrador
WHERE
    id_espaco = $idEspaco
SQL;
        $administradores = DB::select($SQL);
        $idAdministradores = explode(",", implode(",", array_column($administradores, "id_usuario")));
        return in_array($criadorEvento, $idAdministradores);
    }

    /**
     * @param $idArtista
     * @param $idProdutor
     * @return bool
     */
    private function produtorArtista($idArtista, $idProdutor): bool {
        $SQL = <<<SQL
SELECT
    *
FROM
    permissao_produtor_artista
WHERE
    id_artista = $idArtista
    AND aprovado = 'S'
SQL;
        $produtores = DB::select($SQL);
        $idProdutores = explode(",", implode(",", array_column($produtores, "id")));
        return in_array($idProdutor, $idProdutores);
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
    evento;
SQL;
        $result = DB::select($SQL);
        return is_array($result) ? $result[0]->id : null;
    }
}
