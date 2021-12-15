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
        $request["img"] = $request["img"] ?? "";
        $SQL = <<<SQL
REPLACE INTO
    post_blog
(
    id, html, imagem
)
VALUES
(
    {$request["id"]}, '{$request["html"]}', '{$request["img"]}'
)
SQL;
        try {
            DB::select($SQL);
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function buscarPosts(): array
    {
        $SQL = <<<SQL
SELECT * FROM post_blog ORDER BY data_publicacao DESC
SQL;
        try {
            $posts = DB::select($SQL);
            foreach ($posts as &$post) {
                $post = (array)$post;
                $post["html"] = $post["imagem"] . $post["html"];
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
        $SQL = <<<SQL
SELECT * FROM post_blog WHERE id = $codPost
SQL;
        try {
            $post = DB::select($SQL);
            $post = $post[0];
            $post = (array)$post;
            $post["html"] = $post["imagem"] . $post["html"];
            return $post;
        } catch (Exception $e) {
            throw new Exception("Ocorreu um erro");
        }
    }
}
