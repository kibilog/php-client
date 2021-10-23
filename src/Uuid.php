<?php

namespace Kibilog\SimpleClient;

use InvalidArgumentException;

class Uuid
{
    public const TYPE = 4;

    public static function v4(string $uuid = null)
    {
        if (null === $uuid) {
            $uuid = random_bytes(16);
            $uuid[6] = $uuid[6] & "\x0F" | "\x4F";
            $uuid[8] = $uuid[8] & "\x3F" | "\x80";
            $uuid = bin2hex($uuid);

            return substr($uuid, 0, 8).'-'.substr($uuid, 8, 4).'-'.substr($uuid, 12, 4).'-'.substr($uuid, 16, 4).'-'.substr($uuid, 20, 12);
        } else {
            $type = preg_match('{^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$}Di', $uuid) ? (int)$uuid[14] : false;

            if (false === $type || (static::TYPE ? : $type) !== $type) {
                throw new InvalidArgumentException(sprintf('Invalid UUID%s: "%s".', static::TYPE ? 'v'.static::TYPE : '', $uuid));
            }

            return strtolower($uuid);
        }
    }
}