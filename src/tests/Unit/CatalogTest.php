<?php

declare(strict_types=1);

namespace Unit;

use App\Catalog;
use App\PHash;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SlopeIt\ClockMock\ClockMock;

/**
 * @phpstan-type DataProviderEntry array{string, string}
 */
#[CoversClass(Catalog::class)]
#[CoversClass(PHash::class)]
final class CatalogTest extends TestCase
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
    #[DataProvider('dataProviderForGetInstance')]
    public function testGetInstance(string $filename, int $expectedTotalProducts): void
    {
        $instance = new Catalog($this->getFixture($filename));

        $this->assertInstanceOf(Catalog::class, $instance);

        $this->assertIsArray($instance->getProducts());
        $this->assertCount($expectedTotalProducts, $instance->getProducts());

        $this->assertNotEmpty($instance->getProducts()[0]['phash']);
        $this->assertNotEmpty($instance->getProducts()[1]['phash']);
    }

    /**
     * @return array<int, DataProviderEntry>
     */
    public static function dataProviderForGetInstance(): array
    {
        return [
            ['products-1.php', 2],
            ['products-2.php', 2],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForSortCatalogBySimilarity')]
    public function testSortCatalogBySimilarity(string $filename, int $expectedTotalProducts, string $sourceHash): void
    {
        $instance = new Catalog($this->getFixture($filename));

        $productsBefore = $instance->getProducts();

        $this->assertIsArray($productsBefore);
        $this->assertCount($expectedTotalProducts, $productsBefore);

        $instance->sortCatalogBySimilarity($sourceHash);

        $productsAfter = $instance->getProducts();

        $this->assertIsArray($productsAfter);
        $this->assertCount($expectedTotalProducts, $productsAfter);

        $this->assertEquals($productsBefore[0]['id'], $productsAfter[0]['id']);
        $this->assertEquals($productsBefore[1]['id'], $productsAfter[1]['id']);
    }

    /**
     * @return array<int, DataProviderEntry>
     */
    public static function dataProviderForSortCatalogBySimilarity(): array
    {
        return [
            ['products-1.php', 2, '1110010010011001100110010011001010010010010011001001001000110010'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderForSortCatalogByDissimilarity')]
    public function testSortCatalogByDissimilarity(string $filename, int $expectedTotalProducts, string $sourceHash): void
    {
        $instance = new Catalog($this->getFixture($filename));

        $productsBefore = $instance->getProducts();

        $this->assertIsArray($productsBefore);
        $this->assertCount($expectedTotalProducts, $productsBefore);

        $instance->sortCatalogByDissimilarity($sourceHash);

        $productsAfter = $instance->getProducts();

        $this->assertIsArray($productsAfter);
        $this->assertCount($expectedTotalProducts, $productsAfter);

        $this->assertNotEquals($productsBefore[0]['id'], $productsAfter[0]['id']);
        $this->assertNotEquals($productsBefore[1]['id'], $productsAfter[1]['id']);
    }

    /**
     * @return array<int, DataProviderEntry>
     */
    public static function dataProviderForSortCatalogByDissimilarity(): array
    {
        return [
            ['products-2.php', 2, '1110110110011001100100100010011010010010010001001001101000100011'],
        ];
    }
}
