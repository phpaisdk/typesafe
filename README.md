# PHP AI SDK — TypeSafe

Make structured decisions with TypeSafe Jev through the framework-agnostic [PHP AI SDK](https://phpaisdk.com). Get typed answers with provider-reported probabilities and confidence.

## Installation

```bash
composer require aisdk/typesafe
```

Set `TYPESAFE_API_KEY` in your environment.

## Usage

```php
use AiSdk\Decisions\Question;
use AiSdk\Generate;
use AiSdk\TypeSafe;

$result = Generate::decision(['ticket' => 'I was charged twice. Please help.'])
    ->model(TypeSafe::model('jev-latest'))
    ->questions(Question::predicate('urgent', 'Does this need immediate attention?'))
    ->run();

$urgent = $result->answer('urgent')->yes(0.9);
```

## Documentation

[Read the documentation](https://phpaisdk.com/docs/0.x/providers/typesafe) for configuration, supported features, and more examples.

[Contributing](https://github.com/phpaisdk/.github/blob/main/CONTRIBUTING.md) · [Support](https://github.com/phpaisdk/.github/blob/main/SUPPORT.md)

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
