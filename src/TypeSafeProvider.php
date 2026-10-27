<?php

declare(strict_types=1);

namespace AiSdk\TypeSafe;

use AiSdk\Contracts\BaseProvider;
use AiSdk\Contracts\DecisionModelInterface;
use AiSdk\Support\Sdk;
use AiSdk\TypeSafe\Models\TypeSafeDecisionModel;

final class TypeSafeProvider extends BaseProvider
{
    public function __construct(private readonly string $apiKey, private readonly string $baseUrl = 'https://api.typesafe.ai/v1', private readonly ?Sdk $sdk = null) {}
    public function name(): string
    {
        return 'typesafe';
    }
    protected function decisionModel(string $modelId): DecisionModelInterface
    {
        return new TypeSafeDecisionModel($modelId, $this->apiKey, $this->baseUrl, $this->sdk);
    }
}
