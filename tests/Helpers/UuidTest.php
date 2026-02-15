<?php

namespace Slides\Saml2\Tests;

use PHPUnit\Framework\TestCase;
use Slides\Saml2\Helpers\Uuid;

class UuidTest extends TestCase
{
    public function testUuid7GeneratesValidVersionAndVariantBits()
    {
        $uuid = Uuid::uuid7();

        $matches = preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid
        );

        $this->assertSame(1, $matches, 'Generated UUIDv7 must match RFC format/version/variant');
    }

    public function testUuid7IsMonotonicWithinSameMillisecond()
    {
        $time = \DateTimeImmutable::createFromFormat('U.u', '1735689600.123000');
        $this->assertInstanceOf(\DateTimeImmutable::class, $time);

        $first = Uuid::uuid7($time);
        $second = Uuid::uuid7($time);

        $this->assertNotSame($first, $second);
        $this->assertGreaterThan(0, strcmp($second, $first));
    }

    public function testUuid7UsesProvidedTimestamp()
    {
        $time = \DateTimeImmutable::createFromFormat('U.u', '1735689600.456000');
        $this->assertInstanceOf(\DateTimeImmutable::class, $time);

        $uuid = Uuid::uuid7($time);
        $hex = str_replace('-', '', $uuid);
        $timestampHex = substr($hex, 0, 12);

        $this->assertSame(sprintf('%012x', (int) $time->format('Uv')), $timestampHex);
    }

    public function testUuid7IsTimeSortableAcrossDifferentTimestamps()
    {
        $timeA = \DateTimeImmutable::createFromFormat('U.u', '1735689600.100000');
        $timeB = \DateTimeImmutable::createFromFormat('U.u', '1735689600.200000');
        $timeC = \DateTimeImmutable::createFromFormat('U.u', '1735689600.300000');

        $this->assertInstanceOf(\DateTimeImmutable::class, $timeA);
        $this->assertInstanceOf(\DateTimeImmutable::class, $timeB);
        $this->assertInstanceOf(\DateTimeImmutable::class, $timeC);

        $uuidA = Uuid::uuid7($timeA);
        $uuidB = Uuid::uuid7($timeB);
        $uuidC = Uuid::uuid7($timeC);

        $this->assertGreaterThan(0, strcmp($uuidB, $uuidA));
        $this->assertGreaterThan(0, strcmp($uuidC, $uuidB));
    }
}
