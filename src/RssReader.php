<?php

namespace App;

use App\Cache\RedisClient;
use App\Domain\Article;
use App\Domain\Feed;
use App\Parser\HtmlParser;
use DateTime;

class RssReader
{
    private const FILTER = [
        'liveblog',
        'in kaart:',
        'het journaal',
        'het weer',
        'terzake',
        'de afspraak',
        'de zevende dag',
        'overzicht:',
        'bekijk:'
    ];

    public function __construct(
        private readonly RedisClient $cache,
        private readonly HtmlParser $parser
    ) {
    }

    public function getFeed(): Feed
    {
        $rawFeed = $this->getXmlFromUrl('https://www.vrt.be/vrtnws/nl.rss.headlines.xml');

        return $this->buildFeed($rawFeed);
    }

    public function getArticle(string $articleId): ?Article
    {
        if (!$this->cache->exists($articleId)) {
            return null;
        }

        $article = Article::fromArray(json_decode($this->cache->get($articleId), true));

        if (!$article->hasContent()){
            $rawArticle = $this->getXmlFromUrl($article->source);

            $article->updateContent($this->parser->parse($rawArticle['entry']['content']));

            $this->cache->set($article->id, json_encode($article));
        }

        return $article;
    }

    private function buildFeed(array $rawFeed): Feed
    {
        $feed = new Feed(
            $rawFeed['title'],
            new DateTime($rawFeed['updated']),
            $rawFeed['id']
        );

        foreach($rawFeed['entry'] as $article) {
            if ($this->filterArticle($article)) {
                continue;
            }

            $article = new Article(
                substr($article['id'], -9),
                $article['title'],
                new DateTime($article['updated']),
                $article['id'],
                $this->findArticleFeedLink($article)
            );

            $feed->addArticle($article);

            if (!$this->cache->exists($article->id)) {
                $this->cache->set($article->id, json_encode($article));
            }
        }

        return $feed;
    }

    private function filterArticle(array $article): bool
    {
        foreach (self::FILTER as $keyword) {
            if (stripos($article['title'], $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    private function findArticleFeedLink(array $article): ?string
    {
        if (isset($article['link']) && !empty($article['link'])) {
            foreach ($article['link'] as $link) {
                if ($link['@attributes']['rel'] === 'self' && $link['@attributes']['type'] ==='application/atom+xml') {
                    return $link['@attributes']['href'];
                }
            }
        }

        return null;
    }

    private function getXmlFromUrl(string $url): array
    {
        $feed = simplexml_load_file($url);
        $json = json_encode($feed);
        return json_decode($json,true);
    }
}