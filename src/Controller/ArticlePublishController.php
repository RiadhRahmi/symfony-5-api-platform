<?php

namespace App\Controller;

use App\Entity\Article;

class ArticlePublishController
{

    public function __invoke(Article $data): Article
    {
        $data->setIsPublished(true);
        return $data;
    }
}
