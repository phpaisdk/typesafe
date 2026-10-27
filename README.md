# aisdk/typesafe

Native Jev decisions for framework-agnostic PHP 8.3+. This package is prepared for the SDK's stable 0.10.0 line and has not been published.

```php
use AiSdk\Generate;
use AiSdk\TypeSafe;
use AiSdk\Decisions\Question;

TypeSafe::create(['apiKey' => $apiKey]); // or TYPESAFE_API_KEY
$result = Generate::decision(['ticket' => 'Please refund my duplicate charge'])
    ->model(TypeSafe::model('jev-latest'))
    ->questions(
        Question::predicate('urgent', 'Does this need immediate attention?'),
        Question::choice('team', 'Choose a team', ['billing' => 'Payments', 'support' => 'Product usage']),
        Question::score('severity', 'Rate severity', ['Low', 'Medium', 'High']),
    )->run();

$urgent = $result->answer('urgent')->yes(0.9);
```

Maps portable predicates to TypeSafe `noul`, and choices/scores to native criteria. Values, probabilities, confidence, usage and raw response are preserved. No model catalog, generated-JSON fallback, framework, or third-party AI SDK dependency. Images and multimodal question instructions are rejected before HTTP in this first adapter; text and JSON evidence are supported.

Configuration accepts `apiKey`, `baseUrl`, and an optional core `Sdk` containing a PSR-18 client and PSR factories. `TypeSafe::create()` returns an independently configured provider as well as setting the convenience default. Use a provider instance to avoid global configuration in multi-account applications.

Source contracts: [TypeSafe introduction](https://docs.typesafe.ai/introduction), [quick start and native HTTP examples](https://docs.typesafe.ai/introduction/quickstart). Model ids are opaque; the provider determines availability.

Run `composer test:all` after installing dependencies. Until core 0.10.0 is published, use a local Composer path repository for `aisdk/core` with version `0.10.0`.
