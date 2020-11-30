<?php

namespace App\Controller;

use App\Service\RssService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/a/{article}", name="article")
     */
    public function __invoke(
        string $article,
        RssService $rssService
    ){
        $article = $rssService->getArticle($article);

        return $this->render('article.html.twig',
            [
                'article' => $article,
            ]
        );
    }
}