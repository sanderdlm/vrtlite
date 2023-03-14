<?php

namespace App\Domain;

use DateTime;
use JsonSerializable;

class Article implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly DateTime $updated,
        public readonly string $link,
        public readonly string $source,
        private ?string $content = null,
    ) {
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function hasContent(): bool
    {
        return $this->content !== null;
    }

    public function updateContent(string $content): void
    {
        $this->content = $content;
    }
    
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['title'],
            new DateTime($data['updated']),
            $data['link'],
            $data['source'],
            $data['content'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'updated' => $this->updated->format('Y-m-d H:i:s'),
            'link' => $this->link,
            'source' => $this->source,
            'content' => $this->content,
        ];
    }
}