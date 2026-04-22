<x-guest-layout title="Sign In">
    <div class="guest-card-head">
        <span class="eyebrow">Welcome Back</span>
        <h2>Sign in to the crop intelligence portal</h2>
        <p>
            Access dashboards, dataset processing, crop-cycle extraction, reports, and seasonal NDVI analytics.
        </p>
    </div>

    <x-auth-session-status class="status-banner" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-stack">
        @csrf

        <div class="field-group">
            <label for="email" class="field-label">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="field-input"
                placeholder="researcher@example.com"
            >
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field-group">
            <label for="password" class="field-label">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="field-input"
                placeholder="Enter your password"
            >
            @error('password')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-meta">
            <label for="remember_me" class="checkbox-wrap">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Remember me on this device</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-link">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="auth-button">Log In</button>
    </form>

    <div class="auth-footer">
        New to the platform?
        <a href="{{ route('register') }}" class="text-link">Create an account</a>
    </div>
</x-guest-layout>
