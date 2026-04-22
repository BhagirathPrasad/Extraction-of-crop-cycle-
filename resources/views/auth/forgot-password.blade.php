<x-guest-layout title="Reset Password">
    <div class="guest-card-head">
        <span class="eyebrow">Password Recovery</span>
        <h2>Reset access to your account</h2>
        <p>
            Enter your email address and we will send a secure password reset link so you can regain access to the platform.
        </p>
    </div>

    <x-auth-session-status class="status-banner" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="auth-stack">
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
                class="field-input"
                placeholder="Enter your account email"
            >
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="auth-button">Email Password Reset Link</button>
    </form>

    <div class="auth-footer">
        Remembered it?
        <a href="{{ route('login') }}" class="text-link">Return to sign in</a>
    </div>
</x-guest-layout>
