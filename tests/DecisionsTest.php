<?php

declare(strict_types=1);

use AiSdk\Decisions\Question;
use AiSdk\Generate;
use AiSdk\Support\Sdk;
use AiSdk\TypeSafe;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

it('maps portable questions to Jev and preserves native answer probabilities', function () {
    $http = new class implements ClientInterface {
        public ?RequestInterface $request = null;
        public function sendRequest(RequestInterface $request): ResponseInterface
        {
            $this->request = $request;

            return new Response(200, [], json_encode(['answers' => [
                'urgent' => ['type' => 'noul', 'noul' => 0.9],
                'team' => ['type' => 'choice', 'choice' => 'sales', 'confidence' => 0.8, 'probabilities' => ['sales' => 0.9, 'billing' => 0.1]],
                'severity' => ['type' => 'score', 'score' => 0.6, 'confidence' => 0.5, 'probabilities' => [0.4, 0.6]],
            ], 'usage' => ['input_tokens' => 10, 'output_tokens' => 2]]));
        }
    };
    $factory = new Psr17Factory();
    TypeSafe::create(['apiKey' => 'test', 'sdk' => new Sdk($http, $factory, $factory)]);
    $result = Generate::decision(['message' => 'Hi'])->model(TypeSafe::model('opaque'))
        ->questions(Question::predicate('urgent', 'Urgent?'), Question::choice('team', 'Route', ['sales' => 'Sales', 'billing' => 'Billing']), Question::score('severity', 'Severity', ['Low', 'High']))->run();
    $body = json_decode((string) $http->request->getBody(), true);
    expect($http->request->getUri()->getPath())->toBe('/v1/systemone')->and($body['model'])->toBe('opaque')
        ->and($body['questions']['urgent']['type'])->toBe('noul')->and($body['state'])->toBe(['message' => 'Hi'])
        ->and($result->answer('urgent')->yes())->toBeTrue()->and($result->answer('severity')->value)->toBe(0.6);
    TypeSafe::reset();
});

it('rejects unsupported image assets before making an HTTP request', function () {
    $http = new class implements ClientInterface {
        public int $calls = 0;
        public function sendRequest(RequestInterface $request): ResponseInterface
        {
            $this->calls++;

            return new Response(500);
        }
    };
    $factory = new Psr17Factory();
    TypeSafe::create(['apiKey' => 'test', 'sdk' => new Sdk($http, $factory, $factory)]);
    expect(fn() => Generate::decision('image')->model(TypeSafe::model('opaque'))->images(\AiSdk\Content::image('https://example.test/a.png'))->questions(Question::predicate('q', 'What?'))->run())->toThrow(AiSdk\Exceptions\InvalidArgumentException::class);
    expect($http->calls)->toBe(0);
    TypeSafe::reset();
});
