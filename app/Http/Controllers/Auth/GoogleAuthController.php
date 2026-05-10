<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole('student');
        }

        Auth::login($user, remember: true);

        if ($user->hasAnyRole(['super_admin', 'office_admin', 'staff'])) {
            return redirect('/admin');
        }

        // New users (onboarding_completed_at is null) go to onboarding.
        // Returning users go straight to the portal.
        if ($user->onboarding_completed_at === null) {
            return redirect()->route('portal.onboarding');
        }

        return redirect()->route('portal.tickets.index');
    }
}
