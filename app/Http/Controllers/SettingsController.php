<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{

    public function profile(): View
    {
        return view('settings.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'region'       => 'nullable|string|max:255',
            'avatar'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function security(): View
    {
        return view('settings.security', ['user' => auth()->user()]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        auth()->user()->update(['password' => Hash::make($request->password)]);
        ActivityLog::log('password_changed', 'User changed their password.');

        return back()->with('success', 'Password changed successfully.');
    }

    /** Toggle Dark/Light theme */
    public function toggleTheme(Request $request): RedirectResponse
    {
        $theme = auth()->user()->theme === 'dark' ? 'light' : 'dark';
        auth()->user()->update(['theme' => $theme]);
        return back();
    }

    /** Switch language */
    public function switchLocale(Request $request): RedirectResponse
    {
        $locale = $request->validate(['locale' => 'required|in:en,hi,fr'])['locale'];
        auth()->user()->update(['locale' => $locale]);
        session(['locale' => $locale]);
        return back()->with('success', 'Language updated.');
    }
}
