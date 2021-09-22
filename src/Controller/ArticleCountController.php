<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;

class ArticleCountController
{

    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function __invoke(Request $request): int
    {
        $onlineQuery = $request->get('is_published');
        $conditions = [];
        if ($onlineQuery !== null) {
            $conditions = ['isPublished' => $onlineQuery === '1' ? true : false];
        }
        return $this->articleRepository->count($conditions);
    }
}
