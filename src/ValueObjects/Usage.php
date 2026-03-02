<?php

declare(strict_types=1);

namespace Prism\Prism\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class Usage implements Arrayable
{
    public function __construct(
        public int $promptTokens,
        public int $completionTokens,
        public ?int $cacheWriteInputTokens = null,
        public ?int $cacheReadInputTokens = null,
        public ?int $thoughtTokens = null,
        public ?int $generationTokensPerSecond = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'prompt_tokens' => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
            'cache_write_input_tokens' => $this->cacheWriteInputTokens,
            'cache_read_input_tokens' => $this->cacheReadInputTokens,
            'thought_tokens' => $this->thoughtTokens,
            'generation_tokens_per_second' => $this->$generationTokensPerSecond,
        ];
    }
}
