<?php

declare(strict_types=1);

use Prism\Prism\ValueObjects\SpeculativeDecodingUsage;

it('calculates speculative decoding rates from counters', function (): void {
    $usage = SpeculativeDecodingUsage::fromCounters(
        draftKind: 'mtp',
        draftRounds: 4,
        draftTokens: 12,
        acceptedDraftTokens: 9,
    );

    expect($usage->draftAcceptanceRate)->toBe(0.75)
        ->and($usage->acceptedDraftTokensPerRound)->toBe(2.25)
        ->and($usage->tokensPerVerificationRound)->toBe(3.25);
});

it('aggregates counters and recalculates weighted rates', function (): void {
    $usage = SpeculativeDecodingUsage::aggregate([
        SpeculativeDecodingUsage::fromCounters('mtp', 4, 12, 9),
        SpeculativeDecodingUsage::fromCounters('mtp', 2, 4, 1),
    ]);

    expect($usage)->not->toBeNull()
        ->and($usage->draftKind)->toBe('mtp')
        ->and($usage->draftRounds)->toBe(6)
        ->and($usage->draftTokens)->toBe(16)
        ->and($usage->acceptedDraftTokens)->toBe(10)
        ->and($usage->draftAcceptanceRate)->toBe(0.625)
        ->and($usage->acceptedDraftTokensPerRound)->toBe(10 / 6)
        ->and($usage->tokensPerVerificationRound)->toBe(16 / 6);
});

it('marks aggregates using different drafter kinds as mixed', function (): void {
    $usage = SpeculativeDecodingUsage::aggregate([
        SpeculativeDecodingUsage::fromCounters('mtp', 1, 2, 1),
        SpeculativeDecodingUsage::fromCounters('model', 1, 2, 1),
    ]);

    expect($usage?->draftKind)->toBe('mixed');
});

it('serializes with the provider field names', function (): void {
    $usage = SpeculativeDecodingUsage::fromCounters('mtp', 4, 12, 9);

    expect($usage->toArray())->toBe([
        'draft_kind' => 'mtp',
        'draft_rounds' => 4,
        'draft_n' => 12,
        'draft_n_accepted' => 9,
        'draft_acceptance_rate' => 0.75,
        'draft_accepted_per_round' => 2.25,
        'tokens_per_verification_round' => 3.25,
    ]);
});
