<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BlogController extends BaseController
{

    use AuthorizesRequests;

    /**
     * @param Request $request
     * @throws Exception
     */
    public function salvarPost(Request $request)
    {
        $request = json_decode($request->getContent(), true);
        $request["id"] = empty($request["id"]) ? 0 : $request["id"];
        $request["html"] = $request["html"] ?? "";
        $request["imgHtml"] = $request["imgHtml"] ?? "";
        $request["img64"] = $request["img64"] ?? "";
        $request["hyperlink"] = $request["hyperlink"] ?? "";
        $request["textoAlt"] = $request["textoAlt"] ?? "";
        $request["rodape"] = $request["rodape"] ?? "";
        $SQL = <<<SQL
REPLACE INTO
    post_blog
(
    id, html, imagem_html, imagem_base64, hyperlink, texto_alternativo, rodape
)
VALUES
(
    {$request["id"]}, '{$request["html"]}', '{$request["imgHtml"]}', '{$request["img64"]}', '{$request["hyperlink"]}',
    '{$request["textoAlt"]}', '{$request["rodape"]}'
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
    public function buscarPosts(Request $request): array
    {
        $where = "";
        if ($request->status == 1 || $request->status == 0) {
            $where = "WHERE status = {$request->status}";
        }
        $SQL = <<<SQL
SELECT * FROM post_blog $where ORDER BY data_publicacao DESC
SQL;
        try {
            $posts = DB::select($SQL);
            foreach ($posts as &$post) {
                $post = (array)$post;
                $post["htmlIMG"] = $post["imagem_html"] . $post["html"];
            }
            return $posts;
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param int $codPost
     * @return array
     * @throws Exception
     */
    public function buscarPost(int $codPost): array
    {
        $where = "";
        $SQL = <<<SQL
SELECT * FROM post_blog WHERE id = $codPost $where
SQL;
        try {
            $post = DB::select($SQL);
            $post = $post[0];
            $post = (array)$post;
            $post["htmlIMG"] = $post["imagem_html"] . $post["html"];
            return $post;
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    public function atualizarStatus(Request $request): bool
    {
        $SQL = <<<SQL
UPDATE post_blog SET status = IF(status = 1, 0, 1) WHERE id = {$request->codigo}
SQL;
        try {
            DB::select($SQL);
            return true;
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }
}
