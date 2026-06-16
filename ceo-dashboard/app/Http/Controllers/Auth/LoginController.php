<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $target = redirect()->intended(route('dashboard'))->getTargetUrl();

            return $request->wantsJson()
                ? response()->json(['ok' => true, 'redirect' => $target])
                : redirect()->to($target);
        }

        // For AJAX this returns a 422 with the message (no page reload);
        // for a plain POST it redirects back with the error.
        throw ValidationException::withMessages([
            'email' => 'Invalid email or password.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
