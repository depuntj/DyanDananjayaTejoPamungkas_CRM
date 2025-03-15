<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:admin']);
    }

    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }

        // Role filtering
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(10);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', 'confirmed', Password::defaults()],
                'role' => 'required|in:admin,manager,sales',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            Log::info('User created', [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully');
        } catch (\Exception $e) {
            Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to create user'])->withInput();
        }
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,manager,sales',
        ]);

        $user->update($validated);

        Log::info('User updated', [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'updated_by' => auth()->id()
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        Log::info('User password updated', [
            'id' => $user->id,
            'updated_by' => auth()->id()
        ]);

        return back()->with('success', 'Password updated successfully');
    }

    public function destroy(User $user)
    {
        // Prevent deletion of own account
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account');
        }

        // Check if user has associated data
        $hasLeads = $user->leads()->exists();
        $hasProjects = $user->projects()->exists();

        if ($hasLeads || $hasProjects) {
            return back()->with('error', 'Cannot delete user with associated leads or projects');
        }

        $user->delete();

        Log::info('User deleted', [
            'id' => $user->id,
            'name' => $user->name,
            'deleted_by' => auth()->id()
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }
}
