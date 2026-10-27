<?php

declare(strict_types=1);

namespace AiSdk\TypeSafe\Models;

use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\DecisionModelInterface;
use AiSdk\Decisions\Answer;
use AiSdk\Exceptions\InvalidResponseException;
use AiSdk\Requests\DecisionModelRequest;
use AiSdk\Responses\DecisionModelResponse;
use AiSdk\Support\Sdk;
use AiSdk\Support\Usage;

final class TypeSafeDecisionModel extends BaseModel implements DecisionModelInterface
{
    public function __construct(private readonly string $id, private readonly string $apiKey, private readonly string $baseUrl, private readonly ?Sdk $sdk = null) {}
    public function provider(): string
    {
        return 'typesafe';
    }
    public function modelId(): string
    {
        return $this->id;
    }
    public function decide(DecisionModelRequest $request): DecisionModelResponse
    {
        if ($request->images !== []) {
            throw new \InvalidArgumentException('This TypeSafe adapter currently supports text and JSON evidence only.');
        }
        $questions = [];
        foreach ($request->questions as $question) {
            if (! is_string($question->instructions)) {
                throw new \InvalidArgumentException('This TypeSafe adapter requires text question instructions.');
            }
            $item = ['type' => $question->kind === 'predicate' ? 'noul' : $question->kind, 'instructions' => $question->instructions];
            if ($question->kind === 'choice') {
                $item['criteria'] = $question->choices;
            }
            if ($question->kind === 'score') {
                $item['criteria'] = $question->levels;
            }
            $questions[$question->name] = $item;
        }
        foreach (['model', 'state', 'questions'] as $reserved) {
            if (array_key_exists($reserved, $request->providerOptions)) {
                throw new \InvalidArgumentException('Provider options cannot replace decision model, state or questions.');
            }
        }
        $payload = $this->runner($this->sdk)->postJson(rtrim($this->baseUrl, '/') . '/systemone', ['model' => $this->id, 'state' => $request->input, 'questions' => (object) $questions] + $request->providerOptions, ['Authorization' => 'Bearer ' . $this->apiKey], $this->provider());
        if (! is_array($payload['answers'] ?? null)) {
            $this->invalid();
        }
        $answers = [];
        foreach ($payload['answers'] as $name => $item) {
            if (! is_array($item)) {
                $this->invalid();
            }
            $type = $item['type'] ?? null;
            $kind = match ($type) {
                'noul' => 'predicate', 'choice', 'score' => $type, default => $this->invalid(),
            };
            $value = $kind === 'choice' ? ($item['choice'] ?? null) : $this->number($item[$type] ?? null);
            if ($kind === 'choice' && ! is_string($value)) {
                $this->invalid();
            }
            $probabilities = [];
            if ($kind !== 'predicate') {
                if (! is_array($item['probabilities'] ?? null)) {
                    $this->invalid();
                }
                foreach ($item['probabilities'] as $option => $probability) {
                    $probabilities[$option] = $this->number($probability);
                }
            }
            $answers[(string) $name] = new Answer($kind, $value, isset($item['confidence']) ? $this->number($item['confidence']) : null, $probabilities);
        }
        $usage = is_array($payload['usage'] ?? null) ? $payload['usage'] : [];
        foreach (['input_tokens', 'output_tokens'] as $key) {
            if (isset($usage[$key]) && (! is_int($usage[$key]) || $usage[$key] < 0)) {
                $this->invalid();
            }
        }

        return new DecisionModelResponse($answers, new Usage($usage['input_tokens'] ?? 0, $usage['output_tokens'] ?? 0), $payload);
    }
    private function number(mixed $value): float
    {
        if (! is_int($value) && ! is_float($value)) {
            $this->invalid();
        }

        return (float) $value;
    }
    private function invalid(): never
    {
        throw InvalidResponseException::forProvider('typesafe', 'TypeSafe returned malformed decision answers.');
    }
}
