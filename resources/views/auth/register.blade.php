<x-guest-layout title="Create Account">
    <div class="guest-card-head">
        <span class="eyebrow">Get Started</span>
        <h2>Create your monitoring workspace</h2>
        <p>
            Register to manage satellite datasets, review crop-cycle outputs, and collaborate across admin, researcher, or farmer workflows.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="auth-stack">
        @csrf

        <div class="field-group">
            <label for="name" class="field-label">Full Name</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="field-input"
                placeholder="Enter your full name"
            >
            @error('name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field-group">
            <label for="email" class="field-label">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                class="field-input"
                placeholder="you@example.com"
            >
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field-row">
            <div class="field-group">
                <label for="password" class="field-label">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="field-input"
                    placeholder="Create a strong password"
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
                    placeholder="Repeat your password"
                >
                @error('password_confirmation')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="field-help">
            Account roles and profile details can be managed after sign-up from the application settings and admin workflows.
        </div>

        <button type="submit" class="auth-button">Create Account</button>
    </form>

    <div class="auth-footer">
        Already registered?
        <a href="{{ route('login') }}" class="text-link">Sign in here</a>
    </div>
</x-guest-layout>
