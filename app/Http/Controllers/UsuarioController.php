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
        $usuarios = DB::select("SELECT * FROM users");
        foreach ($usuarios as &$usuario) {
            $usuario = (array)$usuario;
            $outrosDados = $this->buscarOutrosDados($usuario["id"]);
            foreach ($outrosDados as $outroDado) {
                $usuario[$outroDado["tipo"]] = $outroDado;
            }
        }
        return $usuarios;
    }

    /**
     * @param int $codUsuario
     * @return array
     */
    public function listarUsuario(int $codUsuario)
    {
        $usuario = DB::select("SELECT * FROM users WHERE id = $codUsuario");
        if (count($usuario) > 0) {
            $usuario = (array)$usuario[0];
            $outrosDados = $this->buscarOutrosDados($codUsuario);
            foreach ($outrosDados as $outroDado) {
                $usuario[$outroDado["tipo"]] = $outroDado;
            }
            return $usuario;
        }
        return [];
    }

    private function buscarOutrosDados(int $codUsuario)
    {
        $outrosDados = DB::select("SELECT * FROM usuario_outros_dados WHERE id_usuario = $codUsuario");
        foreach ($outrosDados as &$outroDado) {
            $outroDado = (array)$outroDado;
            $outroDado["fotos"] = DB::select("SELECT * FROM usuario_foto WHERE id_usuario = $codUsuario AND tipo = '{$outroDado["tipo"]}'");
        }
        return $outrosDados;
    }

    /**
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    public function salvarUsuario(Request $request): bool
    {
        $request = json_decode($request->getContent(), true);
        $request["id"] = empty($request["id"]) ? 0 : $request["id"];
        $request["user_nome_civ"] = $request["user_nome_civ"] ?? "";
        $request["user_soc_nome"] = $request["user_soc_nome"] ?? "";
        $request["estrangeiro"] = $request["estrangeiro"] ?? "";
        $request["doc"] = $request["doc"] ?? "";
        $request["genero"] = $request["genero"] ?? "";
        $request["user_pic_id"] = $request["user_pic_id"] ?? "";
        $request["user_id_doc"] = $request["user_id_doc"] ?? "";
        $request["user_id_state"] = $request["user_id_state"] ?? "";
        $request["fone"] = $request["fone"] ?? "";
        $request["celular"] = $request["celular"] ?? "";
        $request["user_whats"] = $request["user_whats"] ?? "";
        $request["imagemPerfil"] = $request["imagemPerfil"] ?? "";
        $request["estrangeiro"] = (!isset($request["estrangeiro"]) ? 0 : ($request["estrangeiro"] ? 1 : 0));
        $this->validarEndereco($request);
        $endereco = $request["endereco"];
        $tipoPessoa = $request["tipoP"] == "cpf" ? "F" : "J";
        $dtNasc = date("d/m/Y", strtotime($request["dtNasc"]));
        $senha = bcrypt($request["user_password"]);

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
    {$request["id"]}, '{$request["user_nome_civ"]}', '{$request["user_soc_nome"]}', '{$request["user_email"]}', '$senha', '{$request["estrangeiro"]}',
    '$tipoPessoa', '{$request["doc"]}', '{$request["genero"]}', '{$request["user_pic_id"]}', '{$request["user_id_doc"]}', '{$request["user_id_state"]}',
    '$dtNasc', '{$request["fone"]}', '{$request["celular"]}', '{$request["user_whats"]}', '{$endereco["cep"]}', '{$endereco["uf"]}', '{$endereco["localidade"]}',
    '{$endereco["logradouro"]}', '{$endereco["bairro"]}', '{$endereco["numero"]}', '{$endereco["complemento"]}'
)
SQL;
        try {
            DB::select($SQL);
            if (isset($request["artista"])) {
                $this->salvarOutrosDadosArtista($request["id"], $request);
            }
            if (isset($request["produtor"])) {
                $this->salvarOutrosDadosProdutor($request["id"], $request);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param $dados
     */
    private function validarEndereco(&$dados)
    {
        if (!isset($dados["endereco"])) {
            $dados["endereco"] = [
                "cep" => "",
                "uf" => "",
                "localidade" => "",
                "logradouro" => "",
                "bairro" => "",
                "complemento" => "",
                "numero" => 0
            ];
        } else {
            $dados["endereco"]["cep"] = $dados["endereco"]["cep"] ?? "";
            $dados["endereco"]["uf"] = $dados["endereco"]["uf"] ?? "";
            $dados["endereco"]["localidade"] = $dados["endereco"]["localidade"] ?? "";
            $dados["endereco"]["logradouro"] = $dados["endereco"]["logradouro"] ?? "";
            $dados["endereco"]["bairro"] = $dados["endereco"]["bairro"] ?? "";
            $dados["endereco"]["complemento"] = $dados["endereco"]["complemento"] ?? "";
            $dados["endereco"]["numero"] = $dados["endereco"]["numero"] ?? 0;
        }
    }

    /**
     * @param int $idUsuario
     * @param array $dados
     * @throws Exception
     */
    private function salvarOutrosDadosArtista(int $idUsuario, array $dados)
    {
        $idUsuario = !$idUsuario ? $this->buscarIdUsuario() : $idUsuario;
        $dados['artista']["instagram"] = $dados['artista']["instagram"] ?? "";
        $dados['artista']["facebook"] = $dados['artista']["facebook"] ?? "";
        $dados['artista']["twitter"] = $dados['artista']["twitter"] ?? "";
        $dados['artista']["site"] = $dados['artista']["site"] ?? "";
        $dados['artista']["spotify"] = $dados['artista']["spotify"] ?? "";
        $dados['artista']["amzMusic"] = $dados['artista']["amzMusic"] ?? "";
        $dados['artista']["apple"] = $dados['artista']["apple"] ?? "";
        $dados['artista']["deezer"] = $dados['artista']["deezer"] ?? "";
        $dados['artista']["soundcloud"] = $dados['artista']["soundcloud"] ?? "";
        $dados['artista']["descricao"] = $dados['artista']["descricao"] ?? "";
        $dados['artista']["genero"] = $dados['artista']["genero"] ?? "";
        $dados['artista']["tipo"] = $dados['artista']["tipo"] ?? "";

        $SQL = <<<SQL
INSERT INTO
    usuario_outros_dados
(
    tipo, id_usuario, instagram, facebook, twitter, site, spotify, amazon_music, apple, deezer, soundcloud, descicao,
    genero, tipo_genero
)
VALUES
(
    'artista', $idUsuario, '{$dados['artista']["instagram"]}', '{$dados['artista']["facebook"]}', '{$dados['artista']["twitter"]}',
    '{$dados['artista']["site"]}', '{$dados['artista']["spotify"]}', '{$dados['artista']["amzMusic"]}', '{$dados['artista']["apple"]}',
    '{$dados['artista']["deezer"]}', '{$dados['artista']["soundcloud"]}', '{$dados['artista']["descricao"]}', '{$dados['artista']["genero"]}',
    '{$dados['artista']["tipo"]}'
)
SQL;
        try {
            DB::select("DELETE FROM usuario_outros_dados WHERE id_usuario = $idUsuario AND tipo = 'artista'");
            DB::select($SQL);
            if (is_array($dados['artista']["fotos"]) && count($dados['artista']["fotos"]) > 0) {
                $this->salvarFotos($idUsuario, $dados['artista']["fotos"], 'artista');
            }
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param int $idUsuario
     * @param array $dados
     * @throws Exception
     */
    private function salvarOutrosDadosProdutor(int $idUsuario, array $dados)
    {
        $idUsuario = !$idUsuario ? $this->buscarIdUsuario() : $idUsuario;
        $dados['produtor']["instagram"] = $dados['produtor']["instagram"] ?? "";
        $dados['produtor']["facebook"] = $dados['produtor']["facebook"] ?? "";
        $dados['produtor']["twitter"] = $dados['produtor']["twitter"] ?? "";
        $dados['produtor']["site"] = $dados['produtor']["site"] ?? "";
        $dados['produtor']["spotify"] = $dados['produtor']["spotify"] ?? "";
        $dados['produtor']["amzMusic"] = $dados['produtor']["amzMusic"] ?? "";
        $dados['produtor']["apple"] = $dados['produtor']["apple"] ?? "";
        $dados['produtor']["deezer"] = $dados['produtor']["deezer"] ?? "";
        $dados['produtor']["soundcloud"] = $dados['produtor']["soundcloud"] ?? "";
        $dados['produtor']["descricao"] = $dados['produtor']["descricao"] ?? "";
        $dados['produtor']["genero"] = $dados['produtor']["genero"] ?? "";
        $dados['produtor']["tipo"] = $dados['produtor']["tipo"] ?? "";

        $SQL = <<<SQL
INSERT INTO
    usuario_outros_dados
(
    tipo, id_usuario, instagram, facebook, twitter, site, spotify, amazon_music, apple, deezer, soundcloud, descicao,
    genero, tipo_genero
)
VALUES
(
    'produtor', $idUsuario, '{$dados['produtor']["instagram"]}', '{$dados['produtor']["facebook"]}', '{$dados['produtor']["twitter"]}',
    '{$dados['produtor']["site"]}', '{$dados['produtor']["spotify"]}', '{$dados['produtor']["amzMusic"]}', '{$dados['produtor']["apple"]}',
    '{$dados['produtor']["deezer"]}', '{$dados['produtor']["soundcloud"]}', '{$dados['produtor']["descricao"]}', '{$dados['produtor']["genero"]}',
    '{$dados['produtor']["tipo"]}'
)
SQL;
        try {
            DB::select("DELETE FROM usuario_outros_dados WHERE id_usuario = $idUsuario AND tipo = 'produtor'");
            DB::select($SQL);
            if (is_array($dados['produtor']["fotos"]) && count($dados['produtor']["fotos"]) > 0) {
                $this->salvarFotos($idUsuario, $dados['produtor']["fotos"], 'produtor');
            }
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param int $idUsuario
     * @param array $fotos
     * @param string $tipo
     * @throws Exception
     */
    private function salvarFotos(int $idUsuario, array $fotos, string $tipo)
    {
        DB::select("DELETE FROM usuario_foto WHERE id_usuario = $idUsuario AND tipo = '$tipo'");
        foreach ($fotos as $foto) {
            $SQL = <<<SQL
INSERT INTO
    usuario_foto
(
    id_usuario, tipo, foto
)
VALUES
(
  $idUsuario, '$tipo', '$foto'
)
SQL;
            try {
                DB::select($SQL);
            } catch (Exception $e) {
                throw new Exception("Ocorreu um erro");
            }
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
            return $this->buscarIdUsuario();
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @return int|null
     */
    private function buscarIdUsuario(): ?int
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
