<?php

namespace Faq;

use PHPUnit\Framework\TestCase;

class FaqRequestTest extends TestCase
{
    private FaqRequest $request;
    private array $originalRequest;
    private array $originalSession;

    protected function setUp(): void
    {
        $this->originalRequest = $_REQUEST;
        $this->originalSession = $_SESSION ?? [];
        $_REQUEST = [];
        $_SESSION = [];
        $this->request = new FaqRequest();
    }

    protected function tearDown(): void
    {
        $_REQUEST = $this->originalRequest;
        $_SESSION = $this->originalSession;
    }

    public function testSanitizeTrimsAndEncodesString(): void
    {
        $result = $this->request->sanitize('  <b>hello</b>  ');

        self::assertSame('&lt;b&gt;hello&lt;/b&gt;', $result);
    }

    public function testSanitizeCastsDigitOnlyStringsToInt(): void
    {
        $result = $this->request->sanitize('12345');

        self::assertSame(12345, $result);
    }

    public function testSanitizeRecursivelySanitizesArraysAndFiltersEmptyValues(): void
    {
        $input = [
            'a' => '  <i>val</i>  ',
            'b' => '',
            'c' => '42',
        ];

        $result = $this->request->sanitize($input);

        self::assertSame('&lt;i&gt;val&lt;/i&gt;', $result['a']);
        self::assertArrayNotHasKey('b', $result);
        self::assertSame(42, $result['c']);
    }

    public function testGetReturnsSanitizedValueOrDefault(): void
    {
        $_REQUEST['key'] = '  <b>test</b>  ';

        self::assertSame('&lt;b&gt;test&lt;/b&gt;', $this->request->get('key'));
        self::assertNull($this->request->get('missing'));
        self::assertSame('fallback', $this->request->get('missing', 'fallback'));
    }

    public function testHistorySeedsSessionWithFaqIndexOnFirstCall(): void
    {
        $result = $this->request->history();

        self::assertSame(['faq_index'], $result);
    }

    public function testHistoryAppendsNewEntryWhenDifferentAndTrimsToMaxTwo(): void
    {
        // First call seeds with faq_index.
        $this->request->history();

        // Second call with a different action appends.
        $_REQUEST['sa'] = 'edit';
        $result = $this->request->history('faq');
        self::assertSame(['faq_index', 'faq_edit'], $result);

        // Third call with yet another action trims to max 2.
        $_REQUEST['sa'] = 'delete';
        $result = $this->request->history('faq');
        self::assertSame(['faq_edit', 'faq_delete'], $result);
    }
}
