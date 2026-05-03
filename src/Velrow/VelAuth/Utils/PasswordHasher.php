<?php

declare(strict_types=1);

namespace Velrow\VelAuth\Utils;

final class PasswordHasher {
    private const ALGORITHM = PASSWORD_ARGON2ID;
    private const OPTIONS = [
        'memory_cost' => 65536,
        'time_cost' => 4
    ];

    public static function hash(string $password): string {
        return password_hash($password, self::ALGORITHM, self::OPTIONS);
    }

    public static function verify(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool {
        return password_needs_rehash($hash, self::ALGORITHM, self::OPTIONS);
    }
}
