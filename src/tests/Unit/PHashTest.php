<?php

declare(strict_types=1);

namespace UnitTests;

use App\PHash;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SlopeIt\ClockMock\ClockMock;

/**
 * @phpstan-type DataProviderEntry array{string, string}
 */
#[CoversClass(PHash::class)]
final class PHashTest extends TestCase
{
    private readonly string $PATH_FIXTURES;

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->PATH_FIXTURES = dirname(__DIR__) . '/Fixtures/';
    }

    protected function setUp(): void
    {
        ClockMock::freeze(new \DateTime('2023-01-01 00:00:00'));
    }

    protected function tearDown(): void
    {
        ClockMock::reset();
    }

    private function getFixture(string $filename): string
    {
        return $this->PATH_FIXTURES . $filename;
    }

    #[Test]
    #[DataProvider('dataProviderForGetHashWithFilenameException')]
    public function testGetHashWithFilenameException(string $filename, string $expectedExceptionMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        PHash::getHash($filename);
    }

    /**
     * @return array<int, DataProviderEntry>
     */
    public static function dataProviderForGetHashWithFilenameException(): array
    {
        $messageForInvalidFilename = strtr(PHash::INVALID_FILENAME, [
            '{FILENAME}' => '/tmp/does-not-exists.jpg',
        ]);

        return [
            ['/tmp/does-not-exists.jpg', $messageForInvalidFilename],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForGetHashWithComparisonMethodException')]
    public function testGetHashWithComparisonMethodException(string $comparisonMethod, string $expectedExceptionMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        PHash::getHash($this->getFixture('picture-1.jpg'), $comparisonMethod);
    }

    /**
     * @return array<int, DataProviderEntry>
     */
    public static function dataProviderForGetHashWithComparisonMethodException(): array
    {
        $messageForInvalidCompMethod = strtr(PHash::INVALID_COMP_METHOD, [
            '{METHOD}' => 'xxxx',
        ]);

        return [
            ['xxxx', $messageForInvalidCompMethod],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForGetHash')]
    public function testGetHash(string $comparisonMethod, string $filename, string $expectedHash): void
    {
        $hash = PHash::getHash($this->getFixture($filename), $comparisonMethod);

        $this->assertEquals($expectedHash, $hash);
    }

    /**
     * @return array<int, DataProviderEntry>
     */
    public static function dataProviderForGetHash(): array
    {
        // @codingStandardsIgnoreStart
        return [
            [PHash::COMP_METHOD_AVERAGE, 'picture-1.jpg', '1100110101100110001100001001101101110011110011001100111011111011'],
            [PHash::COMP_METHOD_AVERAGE, 'picture-2.jpg', '1001100110001101001101100011011011011001110111110000111111111011'],
            [PHash::COMP_METHOD_AVERAGE, 'picture-3.jpg', '1100111001100011111101000010111111000111100111111110001111110011'],
            [PHash::COMP_METHOD_AVERAGE, 'picture-4.jpg', '1110001101000100011001000011100011000001001100010000100010000110'],
            [PHash::COMP_METHOD_AVERAGE, 'picture-5.jpg', '1100000000111111001111111111101000111111111101100110111111101111'],
            [PHash::COMP_METHOD_AVERAGE, 'picture-6.jpg', '1111000001000110010001101000001110011001100110011000000101100001'],

            [PHash::COMP_METHOD_MEDIAN, 'picture-1.jpg', '1100110101100110001100001001101100110011110011001100110000110011'],
            [PHash::COMP_METHOD_MEDIAN, 'picture-2.jpg', '1001100110001101001101100011011010001001100111110000110110010011'],
            [PHash::COMP_METHOD_MEDIAN, 'picture-3.jpg', '1100011001000001111100000010111011000111000111111100000111110001'],
            [PHash::COMP_METHOD_MEDIAN, 'picture-4.jpg', '1110001111001100011001000011100111001011001100011000100111000111'],
            [PHash::COMP_METHOD_MEDIAN, 'picture-5.jpg', '1100000000111111001011111100101000111101110100000010110101001001'],
            [PHash::COMP_METHOD_MEDIAN, 'picture-6.jpg', '1111000001001110110001101000001110011001100110011100101101100011'],
        ];
        // @codingStandardsIgnoreEnd
    }

    #[Test]
    #[DataProvider('dataProviderForGetDistance')]
    public function testGetDistance(
        string $comparisonMethod,
        string $filename1,
        string $filename2,
        int $expectedDistance
    ): void {
        $hash1 = PHash::getHash($this->getFixture($filename1), $comparisonMethod);
        $hash2 = PHash::getHash($this->getFixture($filename2), $comparisonMethod);

        $distance = PHash::getDistance($hash1, $hash2);

        $this->assertEquals($expectedDistance, $distance);
    }

    /**
     * @return array<int, DataProviderEntry>
     */
    public static function dataProviderForGetDistance(): array
    {
        return [
            [PHash::COMP_METHOD_AVERAGE, 'picture-1.jpg', 'picture-1.jpg', 0],
            [PHash::COMP_METHOD_AVERAGE, 'picture-1.jpg', 'picture-2.jpg', 14],
            [PHash::COMP_METHOD_AVERAGE, 'picture-1.jpg', 'picture-3.jpg', 18],
            [PHash::COMP_METHOD_AVERAGE, 'picture-1.jpg', 'picture-4.jpg', 22],
            [PHash::COMP_METHOD_AVERAGE, 'picture-1.jpg', 'picture-5.jpg', 22],
            [PHash::COMP_METHOD_AVERAGE, 'picture-1.jpg', 'picture-6.jpg', 21],
            [PHash::COMP_METHOD_AVERAGE, 'picture-3.jpg', 'picture-3/scaled.jpg', 0],
            [PHash::COMP_METHOD_AVERAGE, 'picture-3.jpg', 'picture-3/filters.jpg', 0],
            [PHash::COMP_METHOD_AVERAGE, 'picture-3.jpg', 'picture-3/filters-scaled.jpg', 1],
            [PHash::COMP_METHOD_AVERAGE, 'picture-3.jpg', 'picture-3/cropped.jpg', 26],

            [PHash::COMP_METHOD_MEDIAN, 'picture-1.jpg', 'picture-1.jpg', 0],
            [PHash::COMP_METHOD_MEDIAN, 'picture-1.jpg', 'picture-2.jpg', 17],
            [PHash::COMP_METHOD_MEDIAN, 'picture-1.jpg', 'picture-3.jpg', 21],
            [PHash::COMP_METHOD_MEDIAN, 'picture-1.jpg', 'picture-4.jpg', 16],
            [PHash::COMP_METHOD_MEDIAN, 'picture-1.jpg', 'picture-5.jpg', 24],
            [PHash::COMP_METHOD_MEDIAN, 'picture-1.jpg', 'picture-6.jpg', 17],
            [PHash::COMP_METHOD_MEDIAN, 'picture-3.jpg', 'picture-3/scaled.jpg', 0],
            [PHash::COMP_METHOD_MEDIAN, 'picture-3.jpg', 'picture-3/filters.jpg', 4],
            [PHash::COMP_METHOD_MEDIAN, 'picture-3.jpg', 'picture-3/filters-scaled.jpg', 0],
            [PHash::COMP_METHOD_MEDIAN, 'picture-3.jpg', 'picture-3/cropped.jpg', 23],
        ];
    }
}
