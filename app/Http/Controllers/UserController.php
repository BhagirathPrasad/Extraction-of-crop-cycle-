<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{

    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('role'))   $query->where('role', $request->role);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                                      ->orWhere('email', 'like', "%{$search}%"));
        }
        if ($request->filled('active')) $query->where('is_active', (bool)$request->active);

        $users = $query->latest()->paginate(20)->withQueryString();

        $roleCounts = User::get(['role'])->groupBy('role')->map->count();

        return view('users.index', compact('users', 'roleCounts'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users',
            'password'     => 'required|min:8|confirmed',
            'role'         => 'required|in:admin,researcher,farmer',
            'organization' => 'nullable|string|max:255',
            'region'       => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
        ]);

        $user = User::create([
            ...$validated,
            'password'      => Hash::make($validated['password']),
            'is_active'     => true,
        ]);

        ActivityLog::log('created', "User '{$user->name}' created by admin.", User::class, $user->id);

        return redirect()->route('users.index')->with('success', "User {$user->name} created.");
    }

    public function show(User $user): View
    {
        $user->loadCount(['datasets', 'cropCycles', 'reports']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'         => 'required|in:admin,researcher,farmer',
            'organization' => 'nullable|string|max:255',
            'region'       => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'is_active'    => 'boolean',
            'password'     => 'nullable|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        ActivityLog::log('updated', "User '{$user->name}' updated by admin.", User::class, $user->id);

        return redirect()->route('users.index')->with('success', "User {$user->name} updated.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $name = $user->name;
        $user->delete();
        ActivityLog::log('deleted', "User '{$name}' deleted by admin.", User::class, $user->id);

        return redirect()->route('users.index')->with('success', "User {$name} deleted.");
    }

    /** Toggle active/inactive status */
    public function toggleStatus(User $user): RedirectResponse
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        ActivityLog::log($status, "User '{$user->name}' {$status}.", User::class, $user->id);

        return back()->with('success', "User {$user->name} has been {$status}.");
    }
}
