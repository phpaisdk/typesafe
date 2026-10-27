<?php

declare(strict_types=1);

namespace AiSdk;

use AiSdk\Contracts\Model;
use AiSdk\Support\Sdk;
use AiSdk\TypeSafe\TypeSafeProvider;
use AiSdk\Utils\Support\Env;

final class TypeSafe
{
    private static ?TypeSafeProvider $default = null;
    /** @param array<string, mixed> $config */
    public static function create(array $config = []): TypeSafeProvider
    {
        $key = Env::loadApiKey(is_string($config['apiKey'] ?? null) ? $config['apiKey'] : null, 'TYPESAFE_API_KEY', 'typesafe');

        return self::$default = new TypeSafeProvider($key, is_string($config['baseUrl'] ?? null) ? $config['baseUrl'] : 'https://api.typesafe.ai/v1', ($config['sdk'] ?? null) instanceof Sdk ? $config['sdk'] : null);
    }
    public static function default(): TypeSafeProvider
    {
        return self::$default ?? self::create();
    }
    public static function reset(): void
    {
        self::$default = null;
    }
    public static function model(string $modelId): Model
    {
        return self::default()->model($modelId);
    }
}
