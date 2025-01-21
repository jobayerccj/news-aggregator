<?php

namespace App\DTO;

class ArticleDTO
{
    public function __construct(
        public string $title,
        public string $content,
        public string $newsUrl,
        public string $source,
        public string $author,
        public string $category
    ) {
    }

    /**
     * Create an instance of ArticleDTO from an array.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? '',
            content: $data['content'] ?? '',
            newsUrl: $data['news_url'] ?? '',
            source: $data['source'] ?? '',
            author: $data['author'] ?? '',
            category: $data['category'] ?? ''
        );
    }

    /**
     * Convert the DTO to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'news_url' => $this->newsUrl,
            'source' => $this->source,
            'author' => $this->author,
            'category' => $this->category,
        ];
    }
}
