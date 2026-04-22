<x-guest-layout title="Create New Password">
    <div class="guest-card-head">
        <span class="eyebrow">Set New Password</span>
        <h2>Choose a fresh password</h2>
        <p>
            Update your credentials to continue managing datasets, reports, NDVI insights, and crop-cycle records securely.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="auth-stack">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="field-group">
            <label for="email" class="field-label">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                required
                autofocus
                autocomplete="username"
                class="field-input"
                placeholder="Enter your email"
            >
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field-row">
            <div class="field-group">
                <label for="password" class="field-label">New Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="field-input"
                    placeholder="Create a new password"
                >
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field-group">
                <label for="password_confirmation" class="field-label">Confirm Password</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="field-input"
                    placeholder="Repeat the password"
                >
                @error('password_confirmation')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="auth-button">Reset Password</button>
    </form>
</x-guest-layout>
