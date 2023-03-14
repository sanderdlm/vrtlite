<?php

namespace App\Domain;

use DateTime;

class Feed
{
    public function __construct(
        public readonly string $title,
        public readonly DateTime $updated,
        public readonly string $link,
        public array $articles = []
    ) {
    }

    public function addArticle(Article $article): void
    {
        $this->articles[] = $article;
    }
}