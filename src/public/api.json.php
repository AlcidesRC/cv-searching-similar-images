<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\PHash;

final class Catalog
{
    private array $products;

    public function __construct(string $filename)
    {
        $this->products = include($filename);
        $this->ensureProductImagesAreHashed();
    }

    public function sortCatalogBySimilarity(string $source): void
    {
        $this->sortCatalogBy($source, SORT_ASC);
    }

    public function sortCatalogByDissimilarity(string $source): void
    {
        $this->sortCatalogBy($source, SORT_DESC);
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    private function sortCatalogBy(string $source, int $sortingDirection): void
    {
        $submittedHash = $this->getSubmittedImageHash($source);

        // Calculate the distance
        $this->products = array_map(static function ($entry) use ($submittedHash) {
            $entry['_distance'] = PHash::getDistance($entry['phash'], $submittedHash);
            return $entry;
        }, $this->products);

        // Sort by distance (SORT_ASC = similar first, SORT_DESC = different first)
        array_multisort(
            array_column($this->products, '_distance'),
            $sortingDirection,
            $this->products
        );
    }

    private function ensureProductImagesAreHashed(?bool $force = false): void
    {
        $this->products = array_map(static function (array $entry) use ($force) {
            if ($force || !isset($entry['phash']) || empty($entry['phash'])) {
                $filename = dirname(__DIR__) . '/public/' . $entry['image'];
                $entry['phash'] = PHash::getHash($filename, PHash::COMP_METHOD_AVERAGE);
            }
            return $entry;
        }, $this->products);
    }

    private function getSubmittedImageHash(string $source): string
    {
        $domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        $filename = str_replace($domain, __DIR__, $source);
        return PHash::getHash($filename, PHash::COMP_METHOD_AVERAGE);
    }
}

//---------------------------------------------------------------------------------------------------------------------

$catalog = new Catalog(dirname(__DIR__) . '/config/products.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catalog->sortCatalogBySimilarity($_POST['src']);
    //$catalog->sortCatalogByDissimilarity($_POST['src']);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($catalog->getProducts());
