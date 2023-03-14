<?php

namespace App\Controller;

use App\Service\RssService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function __invoke(
        RssService $rssService
    ): Response {
        return $this->render('home.html.twig',
            [
                'content' => $rssService->getHeadlines(),
            ]
        );
    }
}