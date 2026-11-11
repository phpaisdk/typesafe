# Changelog

Notable changes to `aisdk/typesafe`. Release notes are also published at https://github.com/phpaisdk/typesafe/releases.

## v0.11.1 — 2026-11-11

- The `aisdk/typesafe` test suite now supports Pest 3, 4, and 5; Pest 5 requires PHP 8.4.

## v0.11.0 — 2026-10-31

- Requires `aisdk/core ^0.11.0`. Upgrade SDK packages together; no other changes in this package.

## v0.10.1 — 2026-10-29

- Raise SDK exceptions instead of native PHP ones.

## v0.10.0 — 2026-10-29

### First release

- Make native Jev decisions with text or JSON evidence through `Generate::decision()`: predicates, choices and scores with native probabilities, confidence and usage.

Image assets are not supported yet.

### Compatibility

- Requires `aisdk/core ^0.10.0`.
