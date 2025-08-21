<?php

namespace Joltiy\RidanProductFetcher\Models;

class Product
{
    private string $article;

    /** @var array<array{0: string, 1: string}> */
    private array $files;

    /** @var array<string> */
    private array $images;

    /** @var array<string, string> */
    private array $specification;

    /** @var array<string, string> */
    private array $mainSpecification;

    /**
     * @param array<array{0: string, 1: string}> $files
     * @param array<string> $images
     * @param array<string, string> $specification
     * @param array<string, string> $mainSpecification
     */
    public function __construct(
        string $article,
        array $files = [],
        array $images = [],
        array $specification = [],
        array $mainSpecification = []
    ) {
        $this->article = $article;
        $this->files = $files;
        $this->images = $images;
        $this->specification = $specification;
        $this->mainSpecification = $mainSpecification;
    }

    /**
     * @param array{
     *     article: string,
     *     files: array<array{0: string, 1: string}>,
     *     images: array<string>,
     *     specification: array<string, string>,
     *     main_specification: array<string, string>
     * } $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['article'] ?? '',
            $data['files'] ?? [],
            $data['images'] ?? [],
            $data['specification'] ?? [],
            $data['main_specification'] ?? []
        );
    }

    /**
     * @return array{
     *     article: string,
     *     files: array<array{0: string, 1: string}>,
     *     images: array<string>,
     *     specification: array<string, string>,
     *     main_specification: array<string, string>
     * }
     */
    public function toArray(): array
    {
        return [
            'article' => $this->article,
            'files' => $this->files,
            'images' => $this->images,
            'specification' => $this->specification,
            'main_specification' => $this->mainSpecification
        ];
    }

    // Getters
    public function getArticle(): string
    {
        return $this->article;
    }

    /**
     * @return array<array{0: string, 1: string}>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return array<string>
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @return array<string, string>
     */
    public function getSpecification(): array
    {
        return $this->specification;
    }

    /**
     * @return array<string, string>
     */
    public function getMainSpecification(): array
    {
        return $this->mainSpecification;
    }

    // Helper methods
    public function hasDocuments(): bool
    {
        return !empty($this->files);
    }

    public function hasImages(): bool
    {
        return !empty($this->images);
    }

    /**
     * @return array<string>
     */
    public function getDocumentUrls(): array
    {
        return array_column($this->files, 0);
    }

    /**
     * @return array<string>
     */
    public function getDocumentNames(): array
    {
        return array_column($this->files, 1);
    }
}
