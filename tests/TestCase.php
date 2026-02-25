<?php

namespace Tests;

use App\Models\Message;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, config('app.key'));
    }

    protected function createMessage(string $content = 'Hello world', ?string $rawCode = null, ?CarbonInterface $expiresAt = null): array
    {
        $rawCode ??= Str::upper(Str::random(8));

        $message = Message::create([
            'code' => $this->hashCode($rawCode),
            'content' => $content,
            'expires_at' => $expiresAt ?? now()->addHours(24),
        ]);

        return [$message, $rawCode];
    }
}
