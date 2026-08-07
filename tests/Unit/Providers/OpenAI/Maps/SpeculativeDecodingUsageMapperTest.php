<?php

declare(strict_types=1);

use Prism\Prism\Providers\OpenAI\Maps\SpeculativeDecodingUsageMapper;

it('maps speculative decoding usage returned by OpenAI-compatible providers', function (): void {
    $usage = SpeculativeDecodingUsageMapper::map([
        'draft_kind' => 'mtp',
        'draft_rounds' => 4,
        'draft_n' => 12,
        'draft_n_accepted' => 9,
        'draft_acceptance_rate' => 0.75,
        'draft_accepted_per_round' => 2.25,
        'tokens_per_verification_round' => 3.25,
    ]);

    expect($usage)->not->toBeNull()
        ->and($usage->draftKind)->toBe('mtp')
        ->and($usage->draftRounds)->toBe(4)
        ->and($usage->draftTokens)->toBe(12)
        ->and($usage->acceptedDraftTokens)->toBe(9)
        ->and($usage->draftAcceptanceRate)->toBe(0.75)
        ->and($usage->acceptedDraftTokensPerRound)->toBe(2.25)
        ->and($usage->tokensPerVerificationRound)->toBe(3.25);
});

it('returns null when speculative decoding usage is absent', function (mixed $usage): void {
    expect(SpeculativeDecodingUsageMapper::map($usage))->toBeNull();
})->with([
    [null],
    [[]],
    ['invalid'],
]);

it('calculates derived metrics when a provider only exposes counters', function (): void {
    $usage = SpeculativeDecodingUsageMapper::map([
        'draft_kind' => 'mtp',
        'draft_rounds' => 4,
        'draft_n' => 12,
        'draft_n_accepted' => 9,
    ]);

    expect($usage?->draftAcceptanceRate)->toBe(0.75)
        ->and($usage?->acceptedDraftTokensPerRound)->toBe(2.25)
        ->and($usage?->tokensPerVerificationRound)->toBe(3.25);
});
