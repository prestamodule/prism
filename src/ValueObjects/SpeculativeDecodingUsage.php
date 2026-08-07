<?php

declare(strict_types=1);

namespace Prism\Prism\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class SpeculativeDecodingUsage implements Arrayable
{
    public function __construct(
        public ?string $draftKind,
        public int $draftRounds,
        public int $draftTokens,
        public int $acceptedDraftTokens,
        public float $draftAcceptanceRate,
        public float $acceptedDraftTokensPerRound,
        public float $tokensPerVerificationRound,
    ) {}

    public static function fromCounters(
        ?string $draftKind,
        int $draftRounds,
        int $draftTokens,
        int $acceptedDraftTokens,
    ): self {
        $draftRounds = max(0, $draftRounds);
        $draftTokens = max(0, $draftTokens);
        $acceptedDraftTokens = max(0, min($draftTokens, $acceptedDraftTokens));

        return new self(
            draftKind: $draftKind,
            draftRounds: $draftRounds,
            draftTokens: $draftTokens,
            acceptedDraftTokens: $acceptedDraftTokens,
            draftAcceptanceRate: $draftTokens > 0 ? $acceptedDraftTokens / $draftTokens : 0.0,
            acceptedDraftTokensPerRound: $draftRounds > 0 ? $acceptedDraftTokens / $draftRounds : 0.0,
            tokensPerVerificationRound: $draftRounds > 0 ? ($acceptedDraftTokens + $draftRounds) / $draftRounds : 0.0,
        );
    }

    /**
     * @param  array<int, self|null>  $usages
     */
    public static function aggregate(array $usages): ?self
    {
        $usages = array_values(array_filter($usages, static fn (?self $usage): bool => $usage instanceof self));

        if ($usages === []) {
            return null;
        }

        $draftKinds = array_values(array_unique(array_filter(
            array_map(static fn (self $usage): ?string => $usage->draftKind, $usages),
            static fn (?string $draftKind): bool => $draftKind !== null && $draftKind !== ''
        )));

        return self::fromCounters(
            draftKind: count($draftKinds) === 1 ? $draftKinds[0] : (count($draftKinds) > 1 ? 'mixed' : null),
            draftRounds: array_sum(array_map(static fn (self $usage): int => $usage->draftRounds, $usages)),
            draftTokens: array_sum(array_map(static fn (self $usage): int => $usage->draftTokens, $usages)),
            acceptedDraftTokens: array_sum(array_map(static fn (self $usage): int => $usage->acceptedDraftTokens, $usages)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'draft_kind' => $this->draftKind,
            'draft_rounds' => $this->draftRounds,
            'draft_n' => $this->draftTokens,
            'draft_n_accepted' => $this->acceptedDraftTokens,
            'draft_acceptance_rate' => $this->draftAcceptanceRate,
            'draft_accepted_per_round' => $this->acceptedDraftTokensPerRound,
            'tokens_per_verification_round' => $this->tokensPerVerificationRound,
        ];
    }
}
