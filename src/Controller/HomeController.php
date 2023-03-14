<?php

namespace App\Controller;

use App\RssReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function __invoke(
        RssReader $reader
    ): Response {
        return $this->render('home.html.twig',
            [
                'feed' => $reader->getFeed(),
            ]
        );
    }
}