<?php

namespace App\Service;

class RssService
{
    private RedisService $cache;

    public function __construct(RedisService $cache)
    {
        $this->cache = $cache;
    }

    public function getHeadlines()
    {
        $feed = $this->getFeed('https://www.vrt.be/vrtnws/nl.rss.headlines.xml');

        $this->cacheArticleLinks($feed);

        return $feed;
    }

    public function getArticle(string $articleId)
    {
        if ($this->cache->exists($articleId . '_content')) {
            return json_decode($this->cache->get($articleId . '_content'));
        }

        $articleLink = $this->cache->get($articleId);

        $feed = $this->getFeed($articleLink);

        return $this->cacheArticleContent($feed);
    }

    private function getFeed(string $url): array
    {
        $feed = simplexml_load_file($url);
        $json = json_encode($feed);
        return json_decode($json,true);
    }

    private function cacheArticleLinks(array $feed): void
    {
        if (isset($feed['entry']) && !empty($feed['entry'])) {
            foreach($feed['entry'] as $article) {

                $articleId = $this->findArticleId($article);

                $articleFeedLink = $this->findArticleFeedLink($article);

                if ($articleFeedLink !== null) {
                    $this->cache->set($articleId, $articleFeedLink);
                }
            }
        }
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

    private function findArticleId(array $article): string
    {
        $array = explode('.', $article['id']);
        return end($array);
    }

    private function cacheArticleContent(array $feed): array
    {
        $article = [
            'title' => $feed['entry']['title'],
            'updated' => $feed['entry']['updated'],
            'content' => $feed['entry']['content'],
        ];

        $articleId = $this->findArticleId($feed['entry']);

        $this->cache->set($articleId . '_content', json_encode($article));

        return $article;
    }
}