<?php


namespace App\Controller;


use App\Entity\Article;
use Symfony\Component\HttpFoundation\Request;

class ArticleImageController
{

    public function __invoke(Request $request)
    {
        $article = $request->attributes->get('data');
        if (!($article instanceof Article)) {
            throw new \RuntimeException('Article entendu');
        }
        $article->setFile($request->files->get('file'));
        $article->setUpdatedAt(new \DateTime());
        return $article;
    }
}
