<?php

declare(strict_types=1);

use AiSdk\Decisions\Question;
use AiSdk\Generate;
use AiSdk\TypeSafe;

/** Explicit opt-in: this test calls the real TypeSafe API and consumes credits. */
beforeEach(function () {
    if (getenv('AISDK_LIVE_TYPESAFE') !== '1' || (string) getenv('TYPESAFE_API_KEY') === '') {
        $this->markTestSkipped('Set AISDK_LIVE_TYPESAFE=1 and TYPESAFE_API_KEY to run the billable live test.');
    }
});

it('returns native predicate, choice and score answers from Jev', function () {
    $result = Generate::decision(['message' => 'I was charged twice and need a refund today!'])
        ->model(TypeSafe::model('jev-latest'))
        ->questions(
            Question::predicate('urgent', 'Is the customer asking for urgent action?'),
            Question::choice('team', 'Which team should handle this?', ['billing' => 'Payments and invoices', 'support' => 'Technical issues']),
            Question::score('anger', 'How upset is the customer?', ['calm', 'annoyed', 'angry']),
        )
        ->run();

    expect($result->answer('team')->value)->toBe('billing')
        ->and($result->answer('urgent')->yes(0.5))->toBeTrue()
        ->and($result->answer('anger')->value)->toBeGreaterThanOrEqual(0.0)
        ->and($result->usage->inputTokens)->toBeGreaterThan(0);
});
