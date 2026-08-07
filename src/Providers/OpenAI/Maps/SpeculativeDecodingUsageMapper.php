<?php

declare(strict_types=1);

namespace Prism\Prism\Providers\OpenAI\Maps;

use Prism\Prism\ValueObjects\SpeculativeDecodingUsage;

class SpeculativeDecodingUsageMapper
{
    public static function map(mixed $usage): ?SpeculativeDecodingUsage
    {
        if (! is_array($usage) || ! array_key_exists('draft_rounds', $usage)) {
            return null;
        }

        $calculatedUsage = SpeculativeDecodingUsage::fromCounters(
            draftKind: isset($usage['draft_kind']) ? (string) $usage['draft_kind'] : null,
            draftRounds: (int) ($usage['draft_rounds'] ?? 0),
            draftTokens: (int) ($usage['draft_n'] ?? 0),
            acceptedDraftTokens: (int) ($usage['draft_n_accepted'] ?? 0),
        );

        return new SpeculativeDecodingUsage(
            draftKind: $calculatedUsage->draftKind,
            draftRounds: $calculatedUsage->draftRounds,
            draftTokens: $calculatedUsage->draftTokens,
            acceptedDraftTokens: $calculatedUsage->acceptedDraftTokens,
            draftAcceptanceRate: max(0.0, (float) ($usage['draft_acceptance_rate'] ?? $calculatedUsage->draftAcceptanceRate)),
            acceptedDraftTokensPerRound: max(0.0, (float) ($usage['draft_accepted_per_round'] ?? $calculatedUsage->acceptedDraftTokensPerRound)),
            tokensPerVerificationRound: max(0.0, (float) ($usage['tokens_per_verification_round'] ?? $calculatedUsage->tokensPerVerificationRound)),
        );
    }
}
