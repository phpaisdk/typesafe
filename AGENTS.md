# aisdk/typesafe: guide for coding agents

TypeSafe Jev decision provider for the PHP AI SDK. Part of the framework-agnostic [PHP AI SDK](https://phpaisdk.com); build on `aisdk/core`, keep TypeSafe wire details in this package.

## Using it

```php
use AiSdk\Decisions\Question;
use AiSdk\Generate;
use AiSdk\TypeSafe;

TypeSafe::create(['apiKey' => '...']); // or set TYPESAFE_API_KEY

$answer = Generate::decision(['message' => 'I was charged twice'])
    ->model(TypeSafe::model('jev-latest'))
    ->questions(Question::predicate('urgent', 'Is this urgent?'))
    ->run()
    ->answer('urgent');
```

- Entry point: `AiSdk\TypeSafe` (`create()`, `default()`, `reset()`, `model()`), source in `src/Root/TypeSafe.php`.
- Namespace: `AiSdk\TypeSafe\` in `src/`.
- Only the decision capability exists. Predicates map to `noul`, choices and scores to native criteria.

## Rules for changes

1. **Model ids are opaque strings.** No catalog, no validation, no capability-specific public factories.
2. **Provider wire behavior lives here, portable behavior lives in `aisdk/core`.**
3. **Never fabricate native values.** Probabilities and confidence come from the API. Reject malformed answers with `InvalidResponseException` instead of repairing them.
4. **Unsupported inputs fail before HTTP.** Images and multimodal question instructions are rejected until the asset protocol is mapped and tested.
5. **Credentials come from config or environment** and must not appear in errors or logs.

## Commands

```bash
composer lint      # pint --test
composer analyze   # phpstan level 8
composer test      # pest
```

## Testing

- Default tests never touch the network; use a fake PSR-18 client and `Generate::configure(new Sdk(...))`.
- `tests/LiveTest.php` is skipped unless `AISDK_LIVE_TYPESAFE=1` and `TYPESAFE_API_KEY` are set. It spends credits; run it only when asked.
- Do not edit `vendor/`. If a test needs unreleased core behavior, run it against the local core checkout.

## Changelog

Keep `CHANGELOG.md` current: add short, user-facing entries (what users can do now, dependency compatibility) for every release. Release notes on GitHub should match.
