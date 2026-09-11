<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;


class KelolaUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('management.user.index', compact('users', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'user', 'owner'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'User ditambahkan: ' . $user->name . ' (' . $user->email . ')',
        ]);

        return redirect()->route('user-management.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role === 'super_admin') {
            abort(403, 'Super admin tidak bisa dihapus.');
        }

        if (Auth::id() === $user->id) {
            abort(403, 'Anda tidak bisa menghapus akun sendiri.');
        }

        $userName = $user->name;
        $userEmail = $user->email;

        $user->delete();

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'User dihapus: ' . $userName . ' (' . $userEmail . ')',
        ]);

        return redirect()->route('user-management.index')->with('success', 'User berhasil dihapus.');
    }
}
