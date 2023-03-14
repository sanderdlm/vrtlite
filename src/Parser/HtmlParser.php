<?php

namespace App\Parser;

use DOMDocument;
use DOMXPath;

class HtmlParser
{
    public function parse(string $content): string
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
            '<table>',
            '<tr>',
            '<td>',
            '<th>',
            '<blockquote>',
            '<img>'
        ]);

        /*
         * Next up, we have a lot of newlines from where we eliminated tags, let's clean it up
         */
        $content = trim(str_replace(["\n", "\r"], '', $content));

        /*
         * A small hack to fix UTF-8 encoding for DOMDocuments
         */
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
        $xpath = '//text()[contains(., "inladen...")]';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node->previousSibling);
            $node->parentNode->removeChild($node);
        }

        // Image copyright mentions TODO: fix the 20 and make it match more copyright mentions
        $xpath = '//text()[contains(., "Copyright 20")]';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node);
        }

        // Image descriptions
        $xpath = '//text()[contains(., "onder de foto")]';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node);
        }

        // Image descriptions
        $xpath = '//text()[contains(., "Beluister het gesprek")]';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node);
        }

        // Image headlines
        $xpath = '//text()[contains(., "Lees voort onder")]';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node);
        }

        // Image headlines
        $xpath = '//text()[contains(., "BEKIJK - ")]';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node);
        }

        // Image headlines
        $xpath = '//text()[contains(., "KIJK - ")]';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node);
        }

        // Teaser links to other articles
        $xpath = '//a/h2/..';
        foreach($xp->query($xpath) as $node) {
            $node->parentNode->removeChild($node);
        }

        $figure = $dom->createElement('figure');
        $caption = $dom->createElement('figcaption');
        $showImageLink = $dom->createElement('a', 'Toon afbeelding');
        $showImageLink->setAttribute('class', 'toggle-image');
        $showImageLink->setAttribute('href', '#');
        $caption->appendChild($showImageLink);
        $figure->appendChild($caption);

        $imageNodes = $dom->getElementsByTagName('img');
        foreach ($imageNodes as $image) {
            $newFigure = $figure->cloneNode(true);

            $image->parentNode->replaceChild($newFigure, $image);

            $image->setAttribute('class', 'hidden');
            $image->setAttribute('loading', 'lazy');

            $newFigure->appendChild($image);
        }

        /*
         * Our DOMDocument wraps HTML content in <html>, <head> and <body> tags, we don't need those
         * so we only select the body tag and then filter it out with str_replace
         */
        $content = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));

        return str_replace(["<body>", "</body>"], '', $content);
    }
}