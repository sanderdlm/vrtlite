<?php

namespace App\Controller;

use App\RssReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/a/{article}', name: 'article')]
    public function __invoke(
        string $article,
        RssReader $reader
    ): Response {
        $article = $reader->getArticle($article);

        if ($article === null) {
            return $this->redirectToRoute('home');
        }

        return $this->render('article.html.twig',
            [
                'article' => $article
            ]
        );
    }
}