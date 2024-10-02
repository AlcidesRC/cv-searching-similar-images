<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\PHash;

function getDatabase(string $filename, string $separator = ",", string $enclosure = "\"", string $escape = "\\"): array
{
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Remove headers
    array_shift($lines);

    return array_map(function($line) use ($separator, $enclosure, $escape) {
        [$id, $image, $name, $category, $models, $price, $phash] = str_getcsv($line, $separator, $enclosure, $escape);

        return [
          'id' => $id,
          'image' => $image,
          'name' => $name,
          'category' => $category,
          'models' => $models,
          'price' => $price,
          'hash' => $phash,
        ];
    }, $lines);
}

$catalog = getDatabase(dirname(__DIR__, 1) . '/db/database.csv');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = str_replace('https://' . $_SERVER['HTTP_HOST'], __DIR__, $_POST['src']);
    $hash = PHash::getHash($filename, PHash::COMP_METHOD_AVERAGE);

    // Calculate the distance
    $catalog = array_map(function ($entry) use ($hash) {
        $entry['_distance'] = PHash::getDistance($entry['hash'], $hash);
        return $entry;
    }, $catalog);

    // Sort by distance (SORT_ASC = similar first, SORT_DESC = different first)
    array_multisort(array_column($catalog, '_distance'), SORT_ASC, $catalog);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($catalog);
