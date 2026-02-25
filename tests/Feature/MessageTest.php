<?php

use App\Models\Message;
use Illuminate\Support\Facades\DB;

it('renders the home page', function () {
    $this->get(route('home'))->assertOk()->assertInertia(fn ($page) => $page->component('Home'));
});

describe('store', function () {
    it('stores an encrypted message with a hashed code and 24h expiry', function () {
        $this->post(route('messages.store'), ['content' => 'Secret'])->assertSessionHas('code');

        $message = Message::first();
        $rawCode = session('code');

        expect(Message::count())->toBe(1)
            ->and($message->content)->toBe('Secret')
            ->and($message->code)->toBe($this->hashCode($rawCode))->not->toBe($rawCode)
            ->and($message->expires_at->diffInSeconds(now()->addHours(24)))->toBeLessThan(5);
    });

    it('validates content', function (array $data, string $error) {
        $this->post(route('messages.store'), $data)->assertSessionHasErrors($error);
        expect(Message::count())->toBe(0);
    })->with([
        'required'   => [['content' => ''], 'content'],
        'too long'   => [['content' => str_repeat('a', 10001)], 'content'],
    ]);

    it('accepts content at the max length boundary', function () {
        $this->post(route('messages.store'), ['content' => str_repeat('a', 10000)])
            ->assertSessionDoesntHaveErrors();
    });
});

describe('show', function () {
    it('returns the decrypted content, then deletes the message', function () {
        [, $rawCode] = $this->createMessage('My secret');

        $this->post(route('messages.show'), ['code' => $rawCode])
            ->assertSessionHas('content', 'My secret');

        expect(Message::count())->toBe(0);
    });

    it('is case-insensitive for the code', function () {
        [, $rawCode] = $this->createMessage('Case test');

        $this->post(route('messages.show'), ['code' => strtolower($rawCode)])
            ->assertSessionHas('content', 'Case test');
    });

    it('rejects a second read after the message is consumed', function () {
        [, $rawCode] = $this->createMessage('One time only');

        $this->post(route('messages.show'), ['code' => $rawCode]);
        $this->post(route('messages.show'), ['code' => $rawCode])->assertSessionHasErrors('code');
    });

    it('rejects and does not delete an expired message', function () {
        [, $rawCode] = $this->createMessage('Expired', expiresAt: now()->subSecond());

        $this->post(route('messages.show'), ['code' => $rawCode])->assertSessionHasErrors('code');

        expect(Message::count())->toBe(1);
    });

    it('rejects invalid codes without exposing content', function (string $code) {
        $this->createMessage('Should not see this');

        $this->post(route('messages.show'), ['code' => $code])
            ->assertSessionHasErrors('code')
            ->assertSessionMissing('content');
    })->with([
        'wrong code' => ['WRONGCOD'],
        'empty code' => [''],
    ]);
});

describe('Message model', function () {
    it('encrypts content at rest', function () {
        [$message] = $this->createMessage('Sensitive data');

        expect(DB::table('messages')->value('content'))->not->toBe('Sensitive data')
            ->and($message->content)->toBe('Sensitive data');
    });

    it('prunes only expired messages', function () {
        $this->createMessage('Keep me', expiresAt: now()->addHour());
        $this->createMessage('Delete me', expiresAt: now()->subSecond());

        $this->artisan('model:prune', ['--model' => [Message::class]]);

        expect(Message::count())->toBe(1)
            ->and(Message::first()->content)->toBe('Keep me');
    });
});
