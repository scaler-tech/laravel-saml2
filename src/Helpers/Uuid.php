<?php

namespace Slides\Saml2\Helpers;

/**
 * Lightweight RFC 9562 UUIDv7 generator.
 *
 * The UUIDv7 monotonic increment strategy is adapted from MIT-licensed
 * implementations in ramsey/uuid and symfony/uid.
 */
class Uuid
{
    /**
     * @var string
     */
    private static $lastTimestampMs = '';

    /**
     * @var string
     */
    private static $lastRandom = '';

    /**
     * @param \DateTimeInterface|null $time
     *
     * @return string
     */
    public static function uuid7(\DateTimeInterface $time = null)
    {
        $timestampMs = self::timestampMilliseconds($time);

        if (self::$lastRandom === '' || $timestampMs !== self::$lastTimestampMs) {
            self::$lastRandom = random_bytes(10);
            self::$lastTimestampMs = $timestampMs;
        } else {
            self::$lastRandom = self::incrementRandomPart(self::$lastRandom);

            if (self::$lastRandom === str_repeat("\x00", 10)) {
                do {
                    usleep(1000);
                    $timestampMs = self::timestampMilliseconds();
                } while ((int) $timestampMs <= (int) self::$lastTimestampMs);

                self::$lastRandom = random_bytes(10);
                self::$lastTimestampMs = $timestampMs;
            }
        }

        $bytes = self::packTimestampMs($timestampMs) . self::$lastRandom;

        // Set version to 7.
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x70);
        // Set variant to RFC 4122/9562.
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return self::formatBytes($bytes);
    }

    /**
     * @param string $bytes
     *
     * @return string
     */
    private static function formatBytes($bytes)
    {
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    /**
     * @param \DateTimeInterface|null $time
     *
     * @return string
     */
    private static function timestampMilliseconds(\DateTimeInterface $time = null)
    {
        if ($time !== null) {
            return $time->format('Uv');
        }

        list($microseconds, $seconds) = explode(' ', microtime());

        return $seconds . substr($microseconds, 2, 3);
    }

    /**
     * @param string $timestampMs
     *
     * @return string
     */
    private static function packTimestampMs($timestampMs)
    {
        $value = (int) $timestampMs;
        $bytes = '';

        for ($i = 5; $i >= 0; --$i) {
            $bytes = chr($value & 0xff) . $bytes;
            $value >>= 8;
        }

        return $bytes;
    }

    /**
     * @param string $random
     *
     * @return string
     */
    private static function incrementRandomPart($random)
    {
        $bytes = $random;

        for ($i = 9; $i >= 0; --$i) {
            $value = ord($bytes[$i]) + 1;
            $bytes[$i] = chr($value & 0xff);

            if ($value <= 0xff) {
                break;
            }
        }

        return $bytes;
    }
}
