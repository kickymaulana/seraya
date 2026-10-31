<?php

namespace App\Http\Controllers;

use App\Services\Sso\SsoService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SsoController extends Controller
{
    public function __construct(private readonly SsoService $sso) {}

    public function login(Request $request): RedirectResponse|Response
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login');
    }

    public function ssoLogin(Request $request): RedirectResponse|Response
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        try {
            return redirect()->away($this->sso->authorizationUrl());
        } catch (\RuntimeException $exception) {
            return Inertia::render('Auth/Login', ['error' => $exception->getMessage()]);
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('sso.login')->withErrors([
                'sso' => $request->string('error_description')->toString() ?: 'Otorisasi sekali_login dibatalkan.',
            ]);
        }

        $validated = $request->validate(['code' => ['required', 'string']]);

        try {
            $token = $this->sso->exchangeCode($validated['code']);
            $profile = $this->sso->userFromToken($token['access_token'] ?? '');
            $user = $this->sso->syncUser($profile);

            Auth::login($user, remember: false);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        } catch (\Throwable $exception) {
            Log::warning('SERAYA SSO authentication failed', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'status' => $exception instanceof RequestException ? $exception->response?->status() : null,
            ]);

            return redirect()->route('sso.login')->withErrors([
                'sso' => $exception->getMessage() ?: 'Login sekali_login gagal.',
            ]);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
