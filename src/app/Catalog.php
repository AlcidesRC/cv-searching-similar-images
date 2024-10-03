<?php

declare(strict_types=1);

namespace App;

final class Catalog
{
    /**
     * @var non-empty-array<int,array> $products
     */
    private array $products;

    public function __construct(string $filename)
    {
        $this->products = include($filename);
        $this->ensureProductImagesAreHashed();
    }

    public function sortCatalogBySimilarity(string $sourceHash): void
    {
        $this->sortCatalogBy($sourceHash, SORT_ASC);
    }

    public function sortCatalogByDissimilarity(string $sourceHash): void
    {
        $this->sortCatalogBy($sourceHash, SORT_DESC);
    }

    /**
     * @return non-empty-array<int,array>
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    private function sortCatalogBy(string $sourceHash, int $sortingDirection): void
    {
        // Calculate the distance
        $this->products = array_map(static function ($entry) use ($sourceHash) {
            $entry['_distance'] = PHash::getDistance($entry['phash'], $sourceHash);
            return $entry;
        }, $this->products);

        // Sort by distance (SORT_ASC = similar first, SORT_DESC = different first)
        $column = array_column($this->products, '_distance');
        array_multisort(
            $column,
            $sortingDirection,
            $this->products
        );
    }

    private function ensureProductImagesAreHashed(): void
    {
        $this->products = array_map(static function (array $entry) {
            if (empty($entry['phash'])) {
                $filename = dirname(__DIR__) . '/public/' . $entry['image'];
                $entry['phash'] = PHash::getHash($filename, PHash::COMP_METHOD_AVERAGE);
            }
            return $entry;
        }, $this->products);
    }
}
