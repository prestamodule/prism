<?php

declare(strict_types=1);

namespace Prism\Prism\Providers\OpenAI\Concerns;

use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Providers\OpenAI\Maps\FinishReasonMap;

trait MapsFinishReason
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function mapFinishReason(array $data): FinishReason
    {
        $topLevelStatus = data_get($data, 'status', '');

        if ($topLevelStatus === 'incomplete') {
            return match (data_get($data, 'incomplete_details.reason')) {
                'content_filter' => FinishReason::ContentFilter,
                default => FinishReason::Length,
            };
        }

        return FinishReasonMap::map(
            data_get($data, 'output.{last}.status', ''),
            data_get($data, 'output.{last}.type', ''),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function unknownFinishReasonMessage(array $data): string
    {
        return vsprintf('OpenAI: unknown finish reason (status: %s, type: %s)', [
            $this->stringifyFinishReasonValue(data_get($data, 'output.{last}.status')),
            $this->stringifyFinishReasonValue(data_get($data, 'output.{last}.type')),
        ]);
    }

    protected function stringifyFinishReasonValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'missing';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value) ?: get_debug_type($value);
    }
}
