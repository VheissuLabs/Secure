<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Home');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'content' => ['required', 'string', 'max:10000'],
        ]);

        $rawCode = Str::upper(Str::random(8));

        Message::create([
            'code' => $this->hashCode($rawCode),
            'content' => $request->string('content'),
            'expires_at' => now()->addHours(24),
        ]);

        return back()->with('code', $rawCode);
    }

    public function show(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $message = Message::query()
            ->where('code', $this->hashCode(Str::upper(trim($request->string('code')))))
            ->where('expires_at', '>', now())
            ->first();

        if (! $message) {
            return back()->withErrors(['code' => 'That message key is no longer valid.']);
        }

        $content = $message->content;

        $message->delete();

        return back()->with('content', $content);
    }

    private function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, config('app.key'));
    }
}
