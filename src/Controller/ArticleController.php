<?php

namespace App\Controller;

use App\Service\RssService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/a/{article}', name: 'article')]
    public function __invoke(
        string $article,
        RssService $rssService
    ): Response {
        return $this->render('article.html.twig',
            [
                'article' => $rssService->getArticle($article),
            ]
        );
    }
}