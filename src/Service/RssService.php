<?php

namespace App\Service;

use DOMDocument;
use DOMXPath;

class RssService
{
    private const FILTER = [
        'liveblog',
        'in kaart:',
        'het journaal',
        'het weer',
        'terzake',
        'de afspraak',
        'de zevende dag',
        'overzicht:'
    ];

    private RedisService $cache;

    public function __construct(RedisService $cache)
    {
        $this->cache = $cache;
    }

    public function getHeadlines(): array
    {
        $feed = $this->getFeed('https://www.vrt.be/vrtnws/nl.rss.headlines.xml');

        return $this->trimHeadlines($feed);
    }

    public function getArticle(string $articleId): array
    {
        /*
        if ($this->cache->exists($articleId . '_content')) {
            return json_decode($this->cache->get($articleId . '_content'), true);
        }
        */
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

    private function trimHeadlines(array $feed): array
    {
        $timestamp = new \DateTime($feed['updated']);

        $trimmedHeadlines = [
            'title' => $feed['title'],
            'updated' => $timestamp->format('d/m/Y H:i:s'),
            'link' => $feed['id'],
            'headlines' => [],
        ];

        if (isset($feed['entry']) && !empty($feed['entry'])) {
            foreach($feed['entry'] as $article) {
                if (!$this->filterArticle($article)) {
                    $this->cacheArticleLink($article);
                    $trimmedHeadlines['headlines'][] = $article;
                }
            }
        }

        return $trimmedHeadlines;
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

    private function findArticleId(array $article): string
    {
        $array = explode('.', $article['id']);
        return end($array);
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

    private function cacheArticleLink(array $article): void
    {
        $articleId = $this->findArticleId($article);

        $articleFeedLink = $this->findArticleFeedLink($article);

        if ($articleFeedLink !== null) {
            $this->cache->set($articleId, $articleFeedLink);
        }
    }

    private function cacheArticleContent(array $feed): array
    {
        $timestamp = new \DateTime($feed['entry']['updated']);

        $article = [
            'title' => $feed['entry']['title'],
            'updated' => $timestamp->format('d/m/Y H:i:s'),
            'content' => $this->trimContent($feed['entry']['content']),
            'link' => $feed['id'],
        ];

        $articleId = $this->findArticleId($feed['entry']);

        $this->cache->set($articleId . '_content', json_encode($article));

        return $article;
    }

    private function trimContent(string $content): string
    {
        /*
         * First step in the cleaning process: only allow a limited set of HTML tags
         */
        $content = strip_tags($content, [
            '<h1>',
            '<h2>',
            '<h3>',
            '<h4>',
            '<h5>',
            '<h6>',
            '<p>',
            '<ol>',
            '<ul>',
            '<li>',
            '<a>',
            '<blockquote>',
        ]);

        /*
         * Next up, we have a lot of newlines from where we eliminated tags, let's clean it up
         */
        $content = str_replace(array("\n", "\r"), '', $content);

        $content = '<meta charset=utf-8">' . $content;

        /*
         * Now for the specific work, let's load our semi-clean HTML string into a DOMDocument
         */
        $dom = new DOMDocument();
        $dom->loadHTML($content);

        $xp = new DOMXPath($dom);

        /*
         * Now, let's remove a couple of nodes we don't want on our text-only version
         */

        // Video player mentions
        $xpath = '//text()[contains(., "Video player inladen...")]';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node->previousSibling);
            $node->parentNode->removeChild($node);
        }

        // Video player mentions
        $xpath = '//text()[contains(., "Copyright 20")]';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node);
        }

        $xpath = '//text()[contains(., "Lees verder onder de foto")]';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node);
        }

        $xpath = '//text()[contains(., "Lees voort onder")]';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node);
        }

        // Teaser links to other articles
        $xpath = '//a/h2/..';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node);
        }

        /*
         *
         */
        return $dom->saveHTML();
    }
}