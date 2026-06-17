<?php

declare(strict_types=1);

namespace Tests\Providers\OpenAI;

use Illuminate\Support\Facades\Http;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Tests\Fixtures\FixtureResponse;

test('text throws a PrismRateLimitedException with a 429 response code', function (): void {
    Http::fake([
        '*' => Http::response(
            status: 429,
        ),
    ])->preventStrayRequests();

    Prism::text()
        ->using(Provider::OpenAI, 'fake-model')
        ->withPrompt('Hello world!')
        ->asText();
})->throws(PrismRateLimitedException::class);

test('structured throws a PrismRateLimitedException with a 429 response code', function (): void {
    Http::fake([
        '*' => Http::response(
            status: 429,
        ),
    ])->preventStrayRequests();

    Prism::structured()
        ->using(Provider::OpenAI, 'fake-model')
        ->withSchema(new ObjectSchema('name', 'description', [new StringSchema('name', 'description')]))
        ->withPrompt('Hello world!')
        ->asStructured();
})->throws(PrismRateLimitedException::class);

test('stream throws a PrismRateLimitedException', function (): void {
    FixtureResponse::fakeResponseSequence('v1/responses', 'openai/stream-rate-limited');

    $response = Prism::text()
        ->using('openai', 'gpt-4o')
        ->withPrompt('Who are you?')
        ->asStream();

    foreach ($response as $chunk) {
        // Rector, leave me alone!
    }
})->throws(PrismRateLimitedException::class);

test('text unknown finish reason exception includes the raw openai status and type', function (): void {
    Http::fake([
        'v1/responses' => Http::response([
            'id' => 'resp_unknown',
            'model' => 'fake-model',
            'output' => [[
                'type' => 'backend_message',
                'status' => 'backend_finished',
                'content' => [[
                    'type' => 'output_text',
                    'text' => 'Hello world!',
                ]],
            ]],
        ]),
    ])->preventStrayRequests();

    Prism::text()
        ->using(Provider::OpenAI, 'fake-model')
        ->withPrompt('Hello world!')
        ->asText();
})->throws(
    PrismException::class,
    'OpenAI: unknown finish reason (status: backend_finished, type: backend_message)'
);

test('structured unknown finish reason exception includes the raw openai status and type', function (): void {
    Http::fake([
        'v1/responses' => Http::response([
            'id' => 'resp_unknown',
            'model' => 'fake-model',
            'output' => [[
                'type' => 'backend_message',
                'status' => 'backend_finished',
                'content' => [[
                    'type' => 'output_text',
                    'text' => '{"name":"Ada"}',
                ]],
            ]],
        ]),
    ])->preventStrayRequests();

    Prism::structured()
        ->using(Provider::OpenAI, 'fake-model')
        ->withSchema(new ObjectSchema('name', 'description', [new StringSchema('name', 'description')]))
        ->withPrompt('Hello world!')
        ->asStructured();
})->throws(
    PrismException::class,
    'OpenAI: unknown finish reason (status: backend_finished, type: backend_message)'
);
