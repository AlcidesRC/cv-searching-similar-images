<?php

declare(strict_types=1);

namespace App;

use Imagick;
use InvalidArgumentException;

final class PHash
{
    public const INVALID_FILENAME = 'File [ {FILENAME} ] does not exists or is not readable.';
    public const INVALID_COMP_METHOD = 'Comparison method [ {METHOD} ] is not supported.';

    public const COMP_METHOD_MEDIAN = 'median';
    public const COMP_METHOD_AVERAGE = 'average';

    private const SIZE_TOP_RELEVANT = 8;
    private const SIZE_DEFAULT = 32;

    public static function getHash(string $filename, string $comparisonMethod = self::COMP_METHOD_MEDIAN): string
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new InvalidArgumentException(strtr(self::INVALID_FILENAME, [
                '{FILENAME}' => $filename,
            ]));
        }

        if (!in_array($comparisonMethod, [self::COMP_METHOD_MEDIAN, self::COMP_METHOD_AVERAGE])) {
            throw new InvalidArgumentException(strtr(self::INVALID_COMP_METHOD, [
                '{METHOD}' => $comparisonMethod,
            ]));
        }

        $img = (new Imagick());
        $img->readImageBlob((string) file_get_contents($filename));

        $img->setImageType(Imagick::IMGTYPE_GRAYSCALE);

        $img->scaleImage(self::SIZE_DEFAULT, self::SIZE_DEFAULT);

        $matrix = $row = $rows = $col = [];

        // DCT by rows
        for ($y = 0; $y < self::SIZE_DEFAULT; $y++) {
            for ($x = 0; $x < self::SIZE_DEFAULT; $x++) {
                $components = $img->getImagePixelColor($x, $y)->getColor();
                $row[$x] = ($components['r'] + $components['g'] + $components['b']) / 3;
            }

            $rows[$y] = self::calculateDCT($row);
        }

        unset($row);

        // DCT by cols
        for ($x = 0; $x < self::SIZE_DEFAULT; $x++) {
            for ($y = 0; $y < self::SIZE_DEFAULT; $y++) {
                $col[$y] = $rows[$y][$x];
            }

            $matrix[$x] = self::calculateDCT($col);
        }

        unset($col);
        unset($rows);

        // Extract the top 8x8 pixels.
        $pixels = [];
        for ($y = 0; $y < self::SIZE_TOP_RELEVANT; $y++) {
            for ($x = 0; $x < self::SIZE_TOP_RELEVANT; $x++) {
                $pixels[] = $matrix[$y][$x];
            }
        }

        unset($matrix);

        // Get the threshold color
        $threshold = match ($comparisonMethod) {
            self::COMP_METHOD_MEDIAN => self::median($pixels),
            self::COMP_METHOD_AVERAGE => self::average($pixels),
        };

        // Calculate hash.
        return implode('', array_map(static function ($pixel) use ($threshold) {
            return ($pixel > $threshold) ? '1' : '0';
        }, $pixels));
    }

    public static function getDistance(string $hash1, string $hash2): int
    {
        return levenshtein($hash1, $hash2);
    }

    /**
     * @param array<int, float> $matrix
     * @return array<int<0, max>, float>
     */
    private static function calculateDCT(array $matrix): array
    {
        $transformed = [];

        $size = count($matrix);

        for ($i = 0; $i < $size; $i++) {
            $sum = 0;

            for ($j = 0; $j < $size; $j++) {
                $sum += $matrix[$j] * cos($i * pi() * ($j + 0.5) / $size);
            }

            $sum *= sqrt(2 / $size);

            if ($i === 0) {
                $sum *= 1 / sqrt(2);
            }

            $transformed[$i] = $sum;
        }

        return $transformed;
    }

    /** @param array<int, float> $pixels */
    protected static function median(array $pixels): float
    {
        \sort($pixels, SORT_NUMERIC);

        $total = count($pixels);

        return ($total % 2 === 0)
            ? ($pixels[$total / 2 - 1] + $pixels[$total / 2]) / 2
            : $pixels[(int) floor($total / 2)];
    }

    /** @param array<int, float> $pixels */
    protected static function average(array $pixels): float
    {
        // Calculate the average value from top 8x8 pixels, except for the first one.
        $n = count($pixels) - 1;

        return array_sum(array_slice($pixels, 1, $n)) / $n;
    }
}
