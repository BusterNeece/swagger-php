<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests;

class ConstantsTest extends OpenApiTestCase
{
    public const URL = 'http://example.com';

    private static $counter = 0;

    public function testConstant(): void
    {
        self::$counter++;
        $const = 'OPENAPI_TEST_' . self::$counter;
        $this->assertFalse(defined($const));
        $this->assertOpenApiLogEntryContains("[Semantical Error] Couldn't find constant " . $const);
        $this->annotationsFromDocBlockParser('@OA\Contact(email=' . $const . ')');

        define($const, 'me@domain.org');
        $annotations = $this->annotationsFromDocBlockParser('@OA\Contact(email=' . $const . ')');
        $this->assertSame('me@domain.org', $annotations[0]->email);
    }

    public function testFQCNConstant(): void
    {
        $annotations = $this->annotationsFromDocBlockParser('@OA\Contact(url=OpenApi\Tests\ConstantsTest::URL)');
        $this->assertSame('http://example.com', $annotations[0]->url);

        $annotations = $this->annotationsFromDocBlockParser('@OA\Contact(url=\OpenApi\Tests\ConstantsTest::URL)');
        $this->assertSame('http://example.com', $annotations[0]->url);
    }

    public function testInvalidClass(): void
    {
        $this->assertOpenApiLogEntryContains("[Semantical Error] Couldn't find constant ConstantsTest::URL");
        $this->annotationsFromDocBlockParser('@OA\Contact(url=ConstantsTest::URL)');
    }

    public function testAutoloadConstant(): void
    {
        if (class_exists('AnotherNamespace\Annotations\Constants', false)) {
            $this->markTestSkipped();
        }
        $annotations = $this->annotationsFromDocBlockParser('@OA\Contact(name=AnotherNamespace\Annotations\Constants::INVALID_TIMEZONE_LOCATION)');
        $this->assertSame('invalidTimezoneLocation', $annotations[0]->name);
    }
}
