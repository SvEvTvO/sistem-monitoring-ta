<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // [OPTIMASI & BUG FIX]: Tangkap penolakan dari Database jika User memiliki relasi
        try {
            $user->delete(); // Coba hapus akunnya DULU

            // Jika lolos (berhasil dihapus), baru lakukan proses Logout
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/');

        } catch (\Illuminate\Database\QueryException $e) {
            // Jika database menolak (karena user punya Project, Divisi, atau Laporan)
            // Kembalikan ke profil dan berikan pesan error yang manusiawi
            return Redirect::back()->with('error', 'Akun tidak dapat dihapus karena masih terikat dengan data Project, Divisi, atau Laporan aktif.');
        }
    }
}
