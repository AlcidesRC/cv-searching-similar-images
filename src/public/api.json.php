<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Catalog;
use App\PHash;

$catalog = new Catalog(dirname(__DIR__) . '/config/products.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    $filename = str_replace($domain, __DIR__, $_POST['src']);
    $sourceHash = PHash::getHash($filename, PHash::COMP_METHOD_AVERAGE);

    $catalog->sortCatalogBySimilarity($sourceHash);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($catalog->getProducts());
