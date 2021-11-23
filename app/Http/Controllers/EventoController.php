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
     * @return array
     */
    public function listarEventos(): array
    {
        $eventos = DB::select("SELECT * FROM evento");
        if (is_array($eventos)) {
            foreach ($eventos as &$evento) {
                $evento->fotosEvento = $this->buscarFotos($evento->id);
            }
        }
        return $eventos;
    }

    /**
     * @param int $codEvento
     * @return array|mixed
     */
    public function listarEvento(int $codEvento)
    {
        $evento = DB::select("SELECT * FROM evento WHERE id = $codEvento");
        if (is_array($evento)) {
            $evento[0]->fotosEvento = $this->buscarFotos($codEvento);
            return $evento[0];
        }
        return [];
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
        $request["id"] = empty($request["id"]) ? 0 : $request["id"];
        $request["data"] = date("Y-m-d", strtotime($request["data"]));
        $request["espaco"] = !is_array($request["espaco"]) ? 0 : $request["espaco"]["value"];
        $SQL = <<<SQL
REPLACE INTO
    evento
(
    id, nome, classificacao, data, hora_ini, hora_fim, descricao, resumo, facebook,
    instagram, site, tipo, espaco, imagem_perfil
)
VALUES
(
    {$request["id"]}, '{$request["nome"]}', '{$request["classificacao"]["code"]}', '{$request["data"]}',
    '{$request["hrIni"]}', '{$request["hrFim"]}', '{$request["descricao"]}', '{$request["resumo"]}',
    '{$request["facebook"]}', '{$request["instagram"]}', '{$request["site"]}', '{$request["tipo"]["value"]}',
    {$request["espaco"]}, '{$request["imagemPerfil"]}'
)
SQL;
        try {
            DB::select($SQL);
            if (is_array($request["artistas"])) {
                $this->salvarArtistas($request["id"], $request["artistas"]);
            }
            if (is_array($request["fotosEvento"]) && count($request["fotosEvento"]) > 0) {
                $this->salvarFotos($request["id"], $request["fotosEvento"]);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param int $idEvento
     * @param array $artistas
     */
    private function salvarArtistas(int $idEvento, array $artistas)
    {
        $idEvento = !$idEvento ? $this->buscarIdEvento() : $idEvento;
        DB::select("DELETE FROM evento_artista WHERE id_evento = $idEvento");
        foreach ($artistas as $artista) {
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
