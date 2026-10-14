<?php

namespace Faq\Repositories;

use Faq\Entities\FaqEntity;
use Faq\FaqAdmin;
use Faq\FaqConfig;
use PHPUnit\Framework\TestCase;

/**
 * Concrete test double for the abstract BaseRepository.
 *
 * Exposes the protected buildLimitQuery() so it can be asserted directly, and
 * lets tests inject a FaqEntity without going through the DB layer.
 */
class TestBaseRepository extends BaseRepository
{
    public function __construct(FaqEntity $entity, FaqConfig $config)
    {
        $this->entity = $entity;

        parent::__construct($config);
    }

    public function buildLimitQueryPublic(int $start): string
    {
        return $this->buildLimitQuery($start);
    }
}

class BaseRepositoryTest extends TestCase
{
    private TestBaseRepository $repository;
    private \PHPUnit\Framework\MockObject\MockObject $config;

    protected function setUp(): void
    {
        $GLOBALS['faq_test_calls'] = [];
        $GLOBALS['faq_pagination_limit'] = 0;

        $this->config = $this->createMock(FaqConfig::class);
        $this->config->method('setting')->willReturnCallback(
            static function (string $key, $default = false) {
                if ($key === FaqAdmin::SETTINGS_PAGINATION) {
                    return $GLOBALS['faq_pagination_limit'] ?? $default;
                }

                return $default;
            }
        );

        $this->repository = new TestBaseRepository(new FaqEntity(), $this->config);
    }

    public function testBuildPaginationReturnsEmptyWhenLimitIsNotConfigured(): void
    {
        $GLOBALS['faq_pagination_limit'] = 0;

        $result = $this->repository->buildPagination(0, 'http://example.com/faq', 25);

        self::assertSame('', $result);
        self::assertArrayNotHasKey('constructPageIndex', array_column($GLOBALS['faq_test_calls'], 'function'));
    }

    public function testBuildPaginationReturnsEmptyWhenTotalIsZero(): void
    {
        $GLOBALS['faq_pagination_limit'] = 10;

        $result = $this->repository->buildPagination(0, 'http://example.com/faq', 0);

        self::assertSame('', $result);
        self::assertArrayNotHasKey('constructPageIndex', array_column($GLOBALS['faq_test_calls'], 'function'));
    }

    public function testBuildPaginationCallsConstructPageIndexWithCorrectArgs(): void
    {
        $GLOBALS['faq_pagination_limit'] = 10;
        $url = 'http://example.com/faq';
        $start = 20;
        $total = 45;

        $this->repository->buildPagination($start, $url, $total);

        $calls = array_values(array_filter(
            $GLOBALS['faq_test_calls'],
            static fn (array $call): bool => $call['function'] === 'constructPageIndex'
        ));

        self::assertCount(1, $calls);
        self::assertSame([$url, $start, $total, 10], array_slice($calls[0]['arguments'], 0, 4));
    }

    public function testBuildLimitQueryReturnsEmptyWhenNoPaginationConfigured(): void
    {
        $GLOBALS['faq_pagination_limit'] = 0;

        self::assertSame('', $this->repository->buildLimitQueryPublic(0));
    }

    public function testBuildLimitQueryReturnsLimitStringWhenConfigured(): void
    {
        $GLOBALS['faq_pagination_limit'] = 10;

        self::assertSame('LIMIT 20, 10', $this->repository->buildLimitQueryPublic(20));
    }
}
