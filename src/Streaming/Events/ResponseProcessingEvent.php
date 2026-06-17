<?php

declare(strict_types=1);

namespace Prism\Prism\Streaming\Events;

use Prism\Prism\Enums\StreamEventType;

readonly class ResponseProcessingEvent extends StreamEvent
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        string $id,
        int $timestamp,
        public string $eventName,
        public ?string $requestId,
        public ?int $sequenceNumber,
        public int|float|null $progress,
        public array $data,
    ) {
        parent::__construct($id, $timestamp);
    }

    public function type(): StreamEventType
    {
        return match ($this->eventName) {
            'response.processing.start' => StreamEventType::ResponseProcessingStart,
            'response.processing.progress' => StreamEventType::ResponseProcessingProgress,
            'response.processing.end' => StreamEventType::ResponseProcessingEnd,
            default => StreamEventType::ResponseProcessingProgress,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'timestamp' => $this->timestamp,
            'type' => $this->eventName,
            'request_id' => $this->requestId,
            'sequence_number' => $this->sequenceNumber,
            'progress' => $this->progress,
            'data' => $this->data,
        ];
    }
}
